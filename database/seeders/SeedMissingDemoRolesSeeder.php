<?php
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SeedMissingDemoRolesSeeder extends Seeder
{
    public function run(): void
    {
        // Idempotent: only inserts demo accounts that don't already exist (firstOrCreate by email).
        // Safe to re-run after a fresh migration to top up second-of-each-role accounts on installs
        // that were seeded before the demo team was doubled.
        $missing = [
            // Existing first-of-role accounts (older installs may have missed these)
            ['name' => 'Dr. Elena Ramos',  'email' => 'clinichead@clinic.com',  'role' => 'clinic_head', 'specialization' => 'General Medicine',     'phone' => '0917-900-0001'],
            ['name' => 'Carlo Reyes',      'email' => 'pharmacist@clinic.com',  'role' => 'pharmacist',  'specialization' => 'Clinical Pharmacist',   'phone' => '0917-900-0002'],
            ['name' => 'Bianca Torres',    'email' => 'secretary@clinic.com',   'role' => 'secretary',   'specialization' => 'Patient Coordination',  'phone' => '0917-900-0003'],

            // Second-of-each-role accounts
            ['name' => 'David Park',       'email' => 'admin2@clinic.com',      'role' => 'admin',       'specialization' => 'Systems Administrator', 'phone' => '0917-123-4568'],
            ['name' => 'Dr. Robert Garcia','email' => 'clinichead2@clinic.com', 'role' => 'clinic_head', 'specialization' => 'Internal Medicine',     'phone' => '0917-900-0011'],
            ['name' => 'Sofia Tan',        'email' => 'pharmacist2@clinic.com', 'role' => 'pharmacist',  'specialization' => 'Inventory Pharmacist',  'phone' => '0917-900-0022'],
            ['name' => 'Mike Aquino',      'email' => 'secretary2@clinic.com',  'role' => 'secretary',   'specialization' => 'Front Desk',            'phone' => '0917-900-0033'],
            ['name' => 'Leo Garcia',       'email' => 'assistant2@clinic.com',  'role' => 'assistant',   'specialization' => null,                    'phone' => '0922-678-9013'],
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
