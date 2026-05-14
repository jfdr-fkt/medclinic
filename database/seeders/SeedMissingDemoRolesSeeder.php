<?php
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SeedMissingDemoRolesSeeder extends Seeder
{
    public function run(): void
    {
        $missing = [
            ['name' => 'Dr. Elena Ramos', 'email' => 'clinichead@clinic.com', 'role' => 'clinic_head', 'specialization' => 'General Medicine',     'phone' => '0917-900-0001'],
            ['name' => 'Carlo Reyes',     'email' => 'pharmacist@clinic.com', 'role' => 'pharmacist',  'specialization' => 'Clinical Pharmacist',   'phone' => '0917-900-0002'],
            ['name' => 'Bianca Torres',   'email' => 'secretary@clinic.com',  'role' => 'secretary',   'specialization' => 'Patient Coordination',  'phone' => '0917-900-0003'],
        ];

        foreach ($missing as $data) {
            User::firstOrCreate(
                ['email' => $data['email']],
                array_merge($data, [
                    'password'     => Hash::make('password'),
                    'is_active'    => true,
                    'last_seen_at' => now(),
                ])
            );
        }
    }
}
