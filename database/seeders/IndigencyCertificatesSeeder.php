<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\CertificateOfIndigency;
use App\Models\ApplicantProfile;
use App\Models\Address;
use App\Models\SupportingDocument;
use App\Models\Barangay;
use App\Models\City;

class IndigencyCertificatesSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure PSGC reference data exists
        if (Barangay::query()->count() === 0) {
            $this->call(PSGCSeeder::class);
        }

        $faker = \Faker\Factory::create('en_PH');
        
        // Total records to seed - 100 pending certificates
        $count = 100;
        
        // Cache barangays and cities to avoid per-iteration queries
        $barangays = Barangay::query()->select(['code', 'city_code'])->get();
        $cities = City::query()->select(['code', 'province_code', 'region_code'])->get()->keyBy('code');

        // Common purposes for indigency certificates
        $purposes = [
            'Medical assistance',
            'Educational assistance',
            'Financial assistance',
            'Legal assistance',
            'Social services',
            'Government benefits',
            'Scholarship application',
            'Hospital bills',
            'Medicine assistance',
            'Emergency assistance'
        ];

        // Create demo users and certificates with related records
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

            // Create indigency certificate - all pending as requested
            $certificate = CertificateOfIndigency::create([
                'user_id' => $user->id,
                'purpose' => Arr::random($purposes),
                'status' => 'pending',
                'remarks' => null, // No remarks for pending certificates
                'application_date' => $faker->dateTimeBetween('-30 days', 'now')->format('Y-m-d'),
            ]);

            // Create applicant profile
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
            ]);

            // Create address
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
            ]);

            // Create supporting document
            SupportingDocument::create([
                'user_id' => $user->id,
                'document_type' => Arr::random(['valid_id', 'proof_of_address', 'income_statement']),
                'file_path' => null,
                'verified' => false,
                'certificate_of_indigency_id' => $certificate->id,
            ]);

            // Progress output every 25 records
            if (($i + 1) % 25 === 0) {
                $this->command?->info('Seeded ' . ($i + 1) . ' indigency certificates...');
            }
        }

        $this->command?->info('Successfully seeded 100 pending indigency certificates!');
    }
}