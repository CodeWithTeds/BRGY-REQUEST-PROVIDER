<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\BarangayPermit;
use App\Models\ApplicantProfile;
use App\Models\Address;
use App\Models\SupportingDocument;
use App\Models\Barangay;
use App\Models\City;

class BusinessPermitsDemoSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure PSGC reference data exists
        if (Barangay::query()->count() === 0) {
            $this->call(PSGCSeeder::class);
        }

        $faker = \Faker\Factory::create('en_PH');
        $statuses = ['pending', 'processing', 'approved', 'rejected'];
        
        // Total records to seed
        $count = 150000;
        
        // Cache barangays and cities to avoid per-iteration queries
        $barangays = Barangay::query()->select(['code', 'city_code'])->get();
        $cities = City::query()->select(['code', 'province_code', 'region_code'])->get()->keyBy('code');

        // Create demo users and permits with related records
        for ($i = 0; $i < $count; $i++) {
            // Use lowercase to match enum definitions in migrations
            $gender = Arr::random(['male', 'female']);

            $user = User::factory()->create([
                'name' => $faker->name($gender),
                'email' => Str::uuid() . '@example.test',
                'password' => bcrypt('password'),
                'role' => 'resident',
            ]);
            
            $barangay = $barangays->random();
            $city = $barangay ? $cities->get($barangay->city_code) : null;

            $permit = BarangayPermit::create([
                'user_id' => $user->id,
                'status' => Arr::random($statuses),
                'remarks' => $faker->optional(0.3)->sentence(),
                'application_date' => $faker->dateTimeBetween('-60 days', 'now')->format('Y-m-d'),
            ]);

            ApplicantProfile::create([
                'user_id' => $user->id,
                'first_name' => $faker->firstName($gender),
                'middle_name' => $faker->optional(0.6)->firstName(),
                'last_name' => $faker->lastName(),
                'suffix' => $faker->optional(0.1)->randomElement(['Jr.', 'Sr.', 'III']),
                'date_of_birth' => $faker->date('Y-m-d', '-18 years'),
                'place_of_birth' => 'Philippines',
                // Match enum values defined in applicant_profiles migration
                'civil_status' => Arr::random(['single', 'married']),
                'gender' => $gender,
                'citizenship' => 'Filipino',
                'contact_number' => $faker->numerify('09#########'),
                'barangay_permit_id' => $permit->id,
            ]);

            Address::create([
                'user_id' => $user->id,
                // Match addresses.type enum: ['present', 'permanent']
                'type' => Arr::random(['present', 'permanent']),
                'house_no' => (string) $faker->numberBetween(1, 999),
                'street' => $faker->streetName(),
                'purok' => (string) $faker->numberBetween(1, 9),
                'barangay_code' => $barangay?->code,
                'city_code' => $city?->code,
                'province_code' => $city?->province_code,
                'region_code' => $city?->region_code,
                'zip_code' => (string) $faker->numberBetween(1000, 9999),
                'barangay_permit_id' => $permit->id,
            ]);

            SupportingDocument::create([
                'user_id' => $user->id,
                'document_type' => Arr::random(['valid_id', 'business_registration', 'proof_of_address']),
                'file_path' => null,
                'verified' => false,
                'barangay_permit_id' => $permit->id,
            ]);

            // Progress output every 5,000 records
            if (($i + 1) % 5000 === 0) {
                $this->command?->info('Seeded ' . ($i + 1) . ' permits...');
            }
        }
    }
}