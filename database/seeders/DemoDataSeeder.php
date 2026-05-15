<?php
namespace Database\Seeders;

use App\Models\Inventory;
use App\Models\Medicine;
use App\Models\MedicineLocation;
use App\Models\Message;
use App\Models\Patient;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // Staff — 2 of every role so the team feels realistic during the demo.
        $admin1  = User::create(['name'=>'Sarah Chen','email'=>'admin@clinic.com','password'=>Hash::make('password'),'role'=>'admin','phone'=>'0917-123-4567','specialization'=>'IT & Operations Admin','last_seen_at'=>now(),'is_active'=>true]);
        $admin2  = User::create(['name'=>'David Park','email'=>'admin2@clinic.com','password'=>Hash::make('password'),'role'=>'admin','phone'=>'0917-123-4568','specialization'=>'Systems Administrator','last_seen_at'=>now()->subMinutes(4),'is_active'=>true]);

        $head1   = User::create(['name'=>'Dr. Elena Ramos','email'=>'clinichead@clinic.com','password'=>Hash::make('password'),'role'=>'clinic_head','phone'=>'0917-900-0001','specialization'=>'General Medicine','last_seen_at'=>now()->subMinutes(3),'is_active'=>true]);
        $head2   = User::create(['name'=>'Dr. Robert Garcia','email'=>'clinichead2@clinic.com','password'=>Hash::make('password'),'role'=>'clinic_head','phone'=>'0917-900-0011','specialization'=>'Internal Medicine','last_seen_at'=>now()->subMinutes(6),'is_active'=>true]);

        $doc1    = User::create(['name'=>'Dr. James Wilson','email'=>'doctor@clinic.com','password'=>Hash::make('password'),'role'=>'doctor','phone'=>'0918-234-5678','specialization'=>'Cardiology','last_seen_at'=>now()->subMinutes(2),'is_active'=>true]);
        $doc2    = User::create(['name'=>'Dr. Maria Santos','email'=>'doctor2@clinic.com','password'=>Hash::make('password'),'role'=>'doctor','phone'=>'0919-345-6789','specialization'=>'Pediatrics','last_seen_at'=>now()->subMinutes(10),'is_active'=>true]);

        $pharm1  = User::create(['name'=>'Carlo Reyes','email'=>'pharmacist@clinic.com','password'=>Hash::make('password'),'role'=>'pharmacist','phone'=>'0917-900-0002','specialization'=>'Clinical Pharmacist','last_seen_at'=>now()->subMinutes(5),'is_active'=>true]);
        $pharm2  = User::create(['name'=>'Sofia Tan','email'=>'pharmacist2@clinic.com','password'=>Hash::make('password'),'role'=>'pharmacist','phone'=>'0917-900-0022','specialization'=>'Inventory Pharmacist','last_seen_at'=>now()->subMinutes(11),'is_active'=>true]);

        $nurse1  = User::create(['name'=>'Nurse Joy Reyes','email'=>'nurse@clinic.com','password'=>Hash::make('password'),'role'=>'nurse','phone'=>'0920-456-7890','last_seen_at'=>now()->subMinutes(1),'is_active'=>true]);
        $nurse2  = User::create(['name'=>'Nurse Mark Lim','email'=>'nurse2@clinic.com','password'=>Hash::make('password'),'role'=>'nurse','phone'=>'0921-567-8901','last_seen_at'=>now()->subMinutes(8),'is_active'=>true]);

        $sec1    = User::create(['name'=>'Bianca Torres','email'=>'secretary@clinic.com','password'=>Hash::make('password'),'role'=>'secretary','phone'=>'0917-900-0003','specialization'=>'Patient Coordination','last_seen_at'=>now()->subMinutes(12),'is_active'=>true]);
        $sec2    = User::create(['name'=>'Mike Aquino','email'=>'secretary2@clinic.com','password'=>Hash::make('password'),'role'=>'secretary','phone'=>'0917-900-0033','specialization'=>'Front Desk','last_seen_at'=>now()->subMinutes(14),'is_active'=>true]);

        $asst1   = User::create(['name'=>'Anna Cruz','email'=>'assistant@clinic.com','password'=>Hash::make('password'),'role'=>'assistant','phone'=>'0922-678-9012','last_seen_at'=>now()->subMinutes(15),'is_active'=>true]);
        $asst2   = User::create(['name'=>'Leo Garcia','email'=>'assistant2@clinic.com','password'=>Hash::make('password'),'role'=>'assistant','phone'=>'0922-678-9013','last_seen_at'=>now()->subMinutes(20),'is_active'=>true]);

        // Patients
        $pats = [
            ['patient_id'=>'P-2024-001','name'=>'Juan Dela Cruz','date_of_birth'=>'1985-03-15','phone'=>'0933-111-2222','address'=>'123 Main St, Manila','medical_history'=>'Hypertension, Diabetes Type 2','assigned_nurse_id'=>$nurse1->id,'assigned_doctor_id'=>$doc1->id,'last_visit'=>now()->subHours(2)],
            ['patient_id'=>'P-2024-002','name'=>'Maria Clara','date_of_birth'=>'1990-07-22','phone'=>'0933-222-3333','address'=>'456 Oak Ave, Quezon City','medical_history'=>'Asthma','assigned_nurse_id'=>$nurse1->id,'assigned_doctor_id'=>$doc2->id,'last_visit'=>now()->subHours(4)],
            ['patient_id'=>'P-2024-003','name'=>'Pedro Penduko','date_of_birth'=>'1978-11-30','phone'=>'0933-333-4444','address'=>'789 Pine Rd, Makati','medical_history'=>'Arthritis, High Cholesterol','assigned_nurse_id'=>$nurse2->id,'assigned_doctor_id'=>$doc1->id,'last_visit'=>now()->subHours(1)],
            ['patient_id'=>'P-2024-004','name'=>'Ana Lisa','date_of_birth'=>'2000-01-10','phone'=>'0933-444-5555','address'=>'321 Elm St, Pasig','medical_history'=>'None','assigned_nurse_id'=>$nurse2->id,'assigned_doctor_id'=>$doc2->id,'last_visit'=>now()->subMinutes(30)],
            ['patient_id'=>'P-2024-005','name'=>'Rizal Mercado','date_of_birth'=>'1965-05-20','phone'=>'0933-555-6666','address'=>'555 Rizal Ave, Caloocan','medical_history'=>'Heart Disease, Hypertension','assigned_nurse_id'=>$nurse1->id,'assigned_doctor_id'=>$doc1->id,'last_visit'=>now()->subDays(2)],
            ['patient_id'=>'P-2024-006','name'=>'Gabriela Silang','date_of_birth'=>'1988-09-14','phone'=>'0933-666-7777','address'=>'888 Hero St, Mandaluyong','medical_history'=>'Migraine','assigned_nurse_id'=>$nurse2->id,'assigned_doctor_id'=>$doc2->id,'last_visit'=>now()->subDays(5)],
            ['patient_id'=>'P-2024-007','name'=>'Andres Bonifacio','date_of_birth'=>'1995-12-01','phone'=>'0933-777-8888','address'=>'111 Katipunan Rd, Marikina','medical_history'=>'None','assigned_nurse_id'=>$nurse1->id,'assigned_doctor_id'=>$doc1->id,'last_visit'=>now()->subDays(1)],
            ['patient_id'=>'P-2024-008','name'=>'Melchora Aquino','date_of_birth'=>'1945-08-18','phone'=>'0933-888-9999','address'=>'222 Tandang Sora, Quezon City','medical_history'=>'Diabetes, Hypertension, Arthritis','assigned_nurse_id'=>$nurse2->id,'assigned_doctor_id'=>$doc2->id,'last_visit'=>now()->subMinutes(45)],
            ['patient_id'=>'P-67-420','name'=>'Sześć Siedem','date_of_birth'=>'1967-04-20','phone'=>'0911-420-6767','address'=>'67 Siedem Street, Warsaw, Polska','medical_history'=>'Chronic case of being a meme. Allergic to taking life seriously. Patient insists their lucky numbers are 67, 420, and 911.','assigned_nurse_id'=>$nurse1->id,'assigned_doctor_id'=>$doc1->id,'last_visit'=>now()->subMinutes(67)],
        ];
        foreach ($pats as $p) Patient::create($p);

        // Locations
        $locs = [
            MedicineLocation::create(['storage_type'=>'Cabinet','cabinet'=>'A','shelf'=>'1','level'=>'Top','section'=>'Left','notes'=>'Emergency meds']),
            MedicineLocation::create(['storage_type'=>'Cabinet','cabinet'=>'A','shelf'=>'1','level'=>'Middle','section'=>'Center','notes'=>'Daily prescriptions']),
            MedicineLocation::create(['storage_type'=>'Cabinet','cabinet'=>'A','shelf'=>'2','level'=>'Top','section'=>'Right','notes'=>'Pediatric meds']),
            MedicineLocation::create(['storage_type'=>'Cabinet','cabinet'=>'B','shelf'=>'1','level'=>'Top','section'=>'Left','notes'=>'Antibiotics']),
            MedicineLocation::create(['storage_type'=>'Cabinet','cabinet'=>'B','shelf'=>'1','level'=>'Bottom','section'=>'Center','notes'=>'Pain relief']),
            MedicineLocation::create(['storage_type'=>'Cabinet','cabinet'=>'B','shelf'=>'2','level'=>'Middle','section'=>'Right','notes'=>'Vitamins & OTC']),
            MedicineLocation::create(['storage_type'=>'Cabinet','cabinet'=>'C','shelf'=>'1','level'=>'Top','section'=>'Left','notes'=>'Refrigerated']),
            MedicineLocation::create(['storage_type'=>'Cabinet','cabinet'=>'C','shelf'=>'1','level'=>'Middle','section'=>'Center','notes'=>'Injectables']),
        ];

        // Medicines
        $meds = [
            ['name'=>'Paracetamol 500mg','generic_name'=>'Acetaminophen','barcode'=>'1234567890123','qr_code'=>'PARA500','loc'=>5,'type'=>'normal','dosage'=>'1-2 tabs q4-6h','qty'=>150,'min'=>20,'exp'=>'2026-12-31','batch'=>'BT-2024-001'],
            ['name'=>'Amoxicillin 250mg','generic_name'=>'Amoxicillin','barcode'=>'1234567890124','qr_code'=>'AMOX250','loc'=>3,'type'=>'prescription','dosage'=>'1 cap TID x7d','qty'=>8,'min'=>15,'exp'=>'2025-08-15','batch'=>'BT-2024-002'],
            ['name'=>'Metformin 500mg','generic_name'=>'Metformin HCl','barcode'=>'1234567890125','qr_code'=>'METF500','loc'=>1,'type'=>'prescription','dosage'=>'1 tab BID w/meals','qty'=>45,'min'=>30,'exp'=>'2026-03-20','batch'=>'BT-2024-003'],
            ['name'=>'Amlodipine 5mg','generic_name'=>'Amlodipine Besylate','barcode'=>'1234567890126','qr_code'=>'AMLO5','loc'=>1,'type'=>'prescription','dosage'=>'1 tab daily','qty'=>3,'min'=>20,'exp'=>'2026-01-15','batch'=>'BT-2024-004'],
            ['name'=>'Ibuprofen 400mg','generic_name'=>'Ibuprofen','barcode'=>'1234567890127','qr_code'=>'IBU400','loc'=>4,'type'=>'normal','dosage'=>'1 tab q6-8h w/food','qty'=>200,'min'=>25,'exp'=>'2027-02-28','batch'=>'BT-2024-005'],
            ['name'=>'Vitamin C 500mg','generic_name'=>'Ascorbic Acid','barcode'=>'1234567890128','qr_code'=>'VITC500','loc'=>5,'type'=>'normal','dosage'=>'1 tab daily','qty'=>500,'min'=>50,'exp'=>'2026-09-10','batch'=>'BT-2024-006'],
            ['name'=>'Salbutamol Inhaler 100mcg','generic_name'=>'Albuterol','barcode'=>'1234567890129','qr_code'=>'SALB100','loc'=>2,'type'=>'prescription','dosage'=>'2 puffs as needed','qty'=>12,'min'=>10,'exp'=>'2025-11-30','batch'=>'BT-2024-007'],
            ['name'=>'Cetirizine 10mg','generic_name'=>'Cetirizine HCl','barcode'=>'1234567890130','qr_code'=>'CET10','loc'=>5,'type'=>'normal','dosage'=>'1 tab daily','qty'=>80,'min'=>15,'exp'=>'2026-11-20','batch'=>'BT-2024-008'],
            ['name'=>'Losartan 50mg','generic_name'=>'Losartan Potassium','barcode'=>'1234567890131','qr_code'=>'LOS50','loc'=>1,'type'=>'prescription','dosage'=>'1 tab daily','qty'=>18,'min'=>20,'exp'=>'2025-12-25','batch'=>'BT-2024-009'],
            ['name'=>'Insulin Glargine 100u/mL','generic_name'=>'Insulin Glargine','barcode'=>'1234567890132','qr_code'=>'INSULIN','loc'=>6,'type'=>'prescription','dosage'=>'As prescribed','qty'=>5,'min'=>8,'exp'=>'2025-06-15','batch'=>'BT-2024-010'],
        ];

        foreach ($meds as $m) {
            $med = Medicine::create([
                'name'         => $m['name'],
                'generic_name' => $m['generic_name'],
                'barcode'      => $m['barcode'],
                'qr_code'      => $m['qr_code'],
                'location_id'  => $locs[$m['loc'] - 1]->id,
                'type'         => $m['type'],
                'dosage'       => $m['dosage'],
            ]);
            Inventory::create([
                'medicine_id'     => $med->id,
                'quantity'        => $m['qty'],
                'min_stock_level' => $m['min'],
                'expiration_date' => $m['exp'],
                'batch_number'    => $m['batch'],
            ]);
        }

        // Standard healthcare shift patterns
        $shiftPatterns = [
            'morning'   => ['07:00', '15:00'],
            'afternoon' => ['15:00', '23:00'],
            'night'     => ['23:00', '07:00'],
            'on_call'   => ['09:00', '17:00'],
        ];

        // Per-role realistic scheduling rules:
        //   pool     = which shifts this role is eligible for
        //   weekdays = days of week worked (1=Mon … 7=Sun)
        //   offRate  = chance per eligible day to be a rest day (0–1)
        $scheduleRules = [
            'admin'       => ['pool'=>['morning'],                      'weekdays'=>[1,2,3,4,5],     'offRate'=>0.05],
            'clinic_head' => ['pool'=>['morning','on_call'],            'weekdays'=>[1,2,3,4,5],     'offRate'=>0.10],
            'doctor'      => ['pool'=>['morning','afternoon','on_call'],'weekdays'=>[1,2,3,4,5,6,7], 'offRate'=>0.20],
            'pharmacist'  => ['pool'=>['morning','afternoon'],          'weekdays'=>[1,2,3,4,5,6],   'offRate'=>0.10],
            'nurse'       => ['pool'=>['morning','afternoon','night'],  'weekdays'=>[1,2,3,4,5,6,7], 'offRate'=>0.20],
            'secretary'   => ['pool'=>['morning'],                      'weekdays'=>[1,2,3,4,5],     'offRate'=>0.05],
            'assistant'   => ['pool'=>['morning','afternoon'],          'weekdays'=>[1,2,3,4,5,6],   'offRate'=>0.15],
        ];

        // Generate 30 days of realistic shifts for ALL staff.
        $allStaff = [$admin1, $admin2, $head1, $head2, $doc1, $doc2, $pharm1, $pharm2, $nurse1, $nurse2, $sec1, $sec2, $asst1, $asst2];

        foreach ($allStaff as $staffMember) {
            $rules = $scheduleRules[$staffMember->role] ?? $scheduleRules['assistant'];
            $pool  = $rules['pool'];
            $offset = $staffMember->id;

            for ($d = 0; $d < 30; $d++) {
                $date = today()->addDays($d);
                $dow  = (int) $date->isoWeekday();
                if (!in_array($dow, $rules['weekdays'])) continue;
                if (mt_rand(1, 100) / 100 <= $rules['offRate']) continue;

                $shiftKey = $pool[($offset + $d) % count($pool)];
                [$start, $end] = $shiftPatterns[$shiftKey];

                Shift::create([
                    'user_id'    => $staffMember->id,
                    'shift_type' => $shiftKey,
                    'shift_date' => $date,
                    'start_time' => $start,
                    'end_time'   => $end,
                    'is_active'  => $d === 0,
                ]);
            }
        }

        // Demo chat messages
        Message::create(['sender_id'=>$nurse1->id,'receiver_id'=>$doc1->id,'body'=>'Good morning Dr. Wilson! Patient Juan Dela Cruz is ready for his check-up.','is_read'=>true]);
        Message::create(['sender_id'=>$doc1->id,'receiver_id'=>$nurse1->id,'body'=>'Good morning Nurse Joy! Ill be there in 5 minutes. Please prepare his chart.','is_read'=>true]);
        Message::create(['sender_id'=>$nurse1->id,'receiver_id'=>$doc1->id,'body'=>'Noted! Also, Amoxicillin stock is getting low — only 8 units left.','is_read'=>false]);
        Message::create(['sender_id'=>$admin1->id,'receiver_id'=>$nurse1->id,'body'=>'Hi Joy, just a reminder — please update the inventory after every dispense. Thanks!','is_read'=>true]);
        Message::create(['sender_id'=>$nurse1->id,'receiver_id'=>$admin1->id,'body'=>'Yes po Dr. Chen! Will do right away.','is_read'=>true]);
    }
}
