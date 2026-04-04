<?php

namespace Database\Seeders;

use App\Models\Barangay;
use App\Models\City;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class BusinessPermitsDemoSeeder extends Seeder
{
    public function run(): void
    {
        ini_set('memory_limit', (string) env('BUSINESS_PERMITS_MEMORY_LIMIT', '512M'));

        if (Barangay::query()->count() === 0) {
            $this->call(PSGCSeeder::class);
        }

        $targetCount = max(1, (int) env('BUSINESS_PERMITS_DEMO_COUNT', 10_000_000));
        $chunkSize = min(2000, max(100, (int) env('BUSINESS_PERMITS_DEMO_CHUNK', 1000)));
        $sqlInsertChunk = min(250, max(25, (int) env('BUSINESS_PERMITS_SQL_INSERT_CHUNK', 100)));
        $statuses = ['pending', 'processing', 'approved', 'rejected'];
        $civilStatuses = ['single', 'married', 'widowed', 'separated'];
        $documentTypes = ['valid_id', 'business_registration', 'proof_of_address'];
        $addressTypes = ['present', 'permanent'];
        $runToken = now()->format('YmdHis');
        $password = bcrypt('password');

        $barangays = Barangay::query()
            ->select(['code', 'city_code'])
            ->get()
            ->map(fn ($barangay) => [
                'code' => $barangay->code,
                'city_code' => $barangay->city_code,
            ])
            ->values()
            ->all();

        $cities = City::query()
            ->select(['code', 'province_code', 'region_code'])
            ->get()
            ->mapWithKeys(fn ($city) => [
                $city->code => [
                    'code' => $city->code,
                    'province_code' => $city->province_code,
                    'region_code' => $city->region_code,
                ],
            ])
            ->all();

        if (count($barangays) === 0) {
            return;
        }

        DB::connection()->disableQueryLog();

        $seeded = 0;
        $barangayLastIndex = count($barangays) - 1;
        $statusLastIndex = count($statuses) - 1;
        $civilStatusLastIndex = count($civilStatuses) - 1;
        $documentTypeLastIndex = count($documentTypes) - 1;
        $addressTypeLastIndex = count($addressTypes) - 1;

        while ($seeded < $targetCount) {
            $batchSize = min($chunkSize, $targetCount - $seeded);
            $successfulBatchSize = $this->insertBatch(
                $batchSize,
                $seeded,
                $runToken,
                $password,
                $barangays,
                $cities,
                $statuses,
                $civilStatuses,
                $documentTypes,
                $addressTypes,
                $sqlInsertChunk,
                $barangayLastIndex,
                $statusLastIndex,
                $civilStatusLastIndex,
                $documentTypeLastIndex,
                $addressTypeLastIndex
            );

            $seeded += $successfulBatchSize;

            if ($seeded % 100000 === 0 || $seeded === $targetCount) {
                $this->command?->info('Seeded '.number_format($seeded).' permits...');
            }
        }

        $this->command?->info(
            'Business permits demo seeding completed: '.number_format($targetCount).' records'
        );
    }

    private function insertBatch(
        int $batchSize,
        int $seeded,
        string $runToken,
        string $password,
        array $barangays,
        array $cities,
        array $statuses,
        array $civilStatuses,
        array $documentTypes,
        array $addressTypes,
        int $sqlInsertChunk,
        int $barangayLastIndex,
        int $statusLastIndex,
        int $civilStatusLastIndex,
        int $documentTypeLastIndex,
        int $addressTypeLastIndex
    ): int {
        $attemptBatchSize = $batchSize;

        while (true) {
            try {
                DB::reconnect();
                $batchNow = now();

                DB::transaction(function () use (
                    $attemptBatchSize,
                    $batchNow,
                    $runToken,
                    $seeded,
                    $password,
                    $barangays,
                    $cities,
                    $statuses,
                    $civilStatuses,
                    $documentTypes,
                    $addressTypes,
                    $sqlInsertChunk,
                    $barangayLastIndex,
                    $statusLastIndex,
                    $civilStatusLastIndex,
                    $documentTypeLastIndex,
                    $addressTypeLastIndex
                ) {
                    for ($offset = 0; $offset < $attemptBatchSize; $offset += $sqlInsertChunk) {
                        $segmentSize = min($sqlInsertChunk, $attemptBatchSize - $offset);
                        $userRows = [];
                        $metaRows = [];

                        for ($i = 0; $i < $segmentSize; $i++) {
                            $sequence = $seeded + $offset + $i + 1;
                            $gender = random_int(0, 1) === 0 ? 'male' : 'female';
                            $barangay = $barangays[random_int(0, $barangayLastIndex)];
                            $city = $cities[$barangay['city_code']] ?? null;

                            $userRows[] = [
                                'name' => 'Resident '.$sequence,
                                'email' => 'resident_'.$runToken.'_'.$sequence.'@example.test',
                                'password' => $password,
                                'role' => 'resident',
                                'created_at' => $batchNow,
                                'updated_at' => $batchNow,
                            ];

                            $metaRows[] = [
                                'sequence' => $sequence,
                                'gender' => $gender,
                                'barangay_code' => $barangay['code'] ?? null,
                                'city_code' => $city['code'] ?? null,
                                'province_code' => $city['province_code'] ?? null,
                                'region_code' => $city['region_code'] ?? null,
                            ];
                        }

                        DB::table('users')->insert($userRows);
                        $emails = array_column($userRows, 'email');
                        $userIdsByEmail = DB::table('users')
                            ->whereIn('email', $emails)
                            ->pluck('id', 'email')
                            ->all();

                        if (count($userIdsByEmail) !== $segmentSize) {
                            throw new RuntimeException('Inserted users could not be resolved by email.');
                        }

                        $userIds = [];
                        $permitRows = [];

                        for ($i = 0; $i < $segmentSize; $i++) {
                            $userId = (int) ($userIdsByEmail[$userRows[$i]['email']] ?? 0);
                            if ($userId === 0) {
                                throw new RuntimeException('Resolved user id is invalid.');
                            }
                            $userIds[] = $userId;
                            $permitRows[] = [
                                'user_id' => $userId,
                                'status' => $statuses[random_int(0, $statusLastIndex)],
                                'remarks' => random_int(1, 100) <= 30 ? 'Generated permit #'.$metaRows[$i]['sequence'] : null,
                                'application_date' => date('Y-m-d', strtotime('-'.random_int(0, 60).' days')),
                                'created_at' => $batchNow,
                                'updated_at' => $batchNow,
                            ];
                        }

                        DB::table('barangay_permits')->insert($permitRows);
                        $permitIdsByUser = DB::table('barangay_permits')
                            ->whereIn('user_id', $userIds)
                            ->pluck('id', 'user_id')
                            ->all();

                        if (count($permitIdsByUser) !== $segmentSize) {
                            throw new RuntimeException('Inserted permits could not be resolved by user id.');
                        }

                        $profileRows = [];
                        $addressRows = [];
                        $documentRows = [];

                        for ($i = 0; $i < $segmentSize; $i++) {
                            $userId = $userIds[$i];
                            $permitId = (int) ($permitIdsByUser[$userId] ?? 0);
                            if ($permitId === 0) {
                                throw new RuntimeException('Resolved permit id is invalid.');
                            }
                            $meta = $metaRows[$i];
                            $fullSequence = str_pad((string) $meta['sequence'], 8, '0', STR_PAD_LEFT);

                            $profileRows[] = [
                                'user_id' => $userId,
                                'first_name' => 'First'.$fullSequence,
                                'middle_name' => random_int(1, 100) <= 60 ? 'Middle'.$fullSequence : null,
                                'last_name' => 'Last'.$fullSequence,
                                'suffix' => random_int(1, 100) <= 10 ? ['Jr.', 'Sr.', 'III'][random_int(0, 2)] : null,
                                'date_of_birth' => date('Y-m-d', strtotime('-'.random_int(18 * 365, 70 * 365).' days')),
                                'place_of_birth' => 'Philippines',
                                'civil_status' => $civilStatuses[random_int(0, $civilStatusLastIndex)],
                                'gender' => $meta['gender'],
                                'citizenship' => 'Filipino',
                                'contact_number' => '09'.str_pad((string) random_int(0, 999999999), 9, '0', STR_PAD_LEFT),
                                'barangay_permit_id' => $permitId,
                                'created_at' => $batchNow,
                                'updated_at' => $batchNow,
                            ];

                            $addressRows[] = [
                                'user_id' => $userId,
                                'type' => $addressTypes[random_int(0, $addressTypeLastIndex)],
                                'house_no' => (string) random_int(1, 999),
                                'street' => 'Street '.random_int(1, 9999),
                                'purok' => (string) random_int(1, 9),
                                'barangay_id' => null,
                                'barangay_code' => $meta['barangay_code'],
                                'city_code' => $meta['city_code'],
                                'province_code' => $meta['province_code'],
                                'region_code' => $meta['region_code'],
                                'zip_code' => str_pad((string) random_int(1000, 9999), 4, '0', STR_PAD_LEFT),
                                'barangay_permit_id' => $permitId,
                                'created_at' => $batchNow,
                                'updated_at' => $batchNow,
                            ];

                            $documentRows[] = [
                                'user_id' => $userId,
                                'document_type' => $documentTypes[random_int(0, $documentTypeLastIndex)],
                                'file_path' => null,
                                'verified' => false,
                                'barangay_permit_id' => $permitId,
                                'created_at' => $batchNow,
                                'updated_at' => $batchNow,
                            ];
                        }

                        DB::table('applicant_profiles')->insert($profileRows);
                        DB::table('addresses')->insert($addressRows);
                        DB::table('supporting_documents')->insert($documentRows);
                    }
                });

                return $attemptBatchSize;
            } catch (\Throwable $e) {
                if (! $this->isConnectionLost($e) || $attemptBatchSize <= 100) {
                    throw $e;
                }

                DB::disconnect();
                $attemptBatchSize = max(100, intdiv($attemptBatchSize, 2));
                $this->command?->warn('Connection dropped. Retrying with batch size '.number_format($attemptBatchSize).'.');
            }
        }
    }

    private function isConnectionLost(\Throwable $e): bool
    {
        $message = strtolower($e->getMessage());

        return str_contains($message, 'server has gone away')
            || str_contains($message, 'lost connection')
            || str_contains($message, 'error while sending query')
            || str_contains($message, 'connection refused')
            || str_contains($message, 'sqlstate[hy000] [2002]');
    }
}
