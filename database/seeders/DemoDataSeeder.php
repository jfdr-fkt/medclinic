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
        // Staff
        $admin  = User::create(['name'=>'Sarah Chen','email'=>'admin@clinic.com','password'=>Hash::make('password'),'role'=>'admin','phone'=>'0917-123-4567','specialization'=>'IT & Operations Admin','last_seen_at'=>now(),'is_active'=>true]);
        $head   = User::create(['name'=>'Dr. Elena Ramos','email'=>'clinichead@clinic.com','password'=>Hash::make('password'),'role'=>'clinic_head','phone'=>'0917-900-0001','specialization'=>'General Medicine','last_seen_at'=>now()->subMinutes(3),'is_active'=>true]);
        $pharm  = User::create(['name'=>'Carlo Reyes','email'=>'pharmacist@clinic.com','password'=>Hash::make('password'),'role'=>'pharmacist','phone'=>'0917-900-0002','specialization'=>'Clinical Pharmacist','last_seen_at'=>now()->subMinutes(5),'is_active'=>true]);
        $sec    = User::create(['name'=>'Bianca Torres','email'=>'secretary@clinic.com','password'=>Hash::make('password'),'role'=>'secretary','phone'=>'0917-900-0003','specialization'=>'Patient Coordination','last_seen_at'=>now()->subMinutes(12),'is_active'=>true]);
        $doc1   = User::create(['name'=>'Dr. James Wilson','email'=>'doctor@clinic.com','password'=>Hash::make('password'),'role'=>'doctor','phone'=>'0918-234-5678','specialization'=>'Cardiology','last_seen_at'=>now()->subMinutes(2),'is_active'=>true]);
        $doc2   = User::create(['name'=>'Dr. Maria Santos','email'=>'doctor2@clinic.com','password'=>Hash::make('password'),'role'=>'doctor','phone'=>'0919-345-6789','specialization'=>'Pediatrics','last_seen_at'=>now()->subMinutes(10),'is_active'=>true]);
        $nurse1 = User::create(['name'=>'Nurse Joy Reyes','email'=>'nurse@clinic.com','password'=>Hash::make('password'),'role'=>'nurse','phone'=>'0920-456-7890','last_seen_at'=>now()->subMinutes(1),'is_active'=>true]);
        $nurse2 = User::create(['name'=>'Nurse Mark Lim','email'=>'nurse2@clinic.com','password'=>Hash::make('password'),'role'=>'nurse','phone'=>'0921-567-8901','last_seen_at'=>now()->subMinutes(8),'is_active'=>true]);
        $asst   = User::create(['name'=>'Anna Cruz','email'=>'assistant@clinic.com','password'=>Hash::make('password'),'role'=>'assistant','phone'=>'0922-678-9012','last_seen_at'=>now()->subMinutes(15),'is_active'=>true]);

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
            MedicineLocation::create(['cabinet'=>'A','shelf'=>'1','level'=>'Top','section'=>'Left','notes'=>'Emergency meds']),
            MedicineLocation::create(['cabinet'=>'A','shelf'=>'1','level'=>'Middle','section'=>'Center','notes'=>'Daily prescriptions']),
            MedicineLocation::create(['cabinet'=>'A','shelf'=>'2','level'=>'Top','section'=>'Right','notes'=>'Pediatric meds']),
            MedicineLocation::create(['cabinet'=>'B','shelf'=>'1','level'=>'Top','section'=>'Left','notes'=>'Antibiotics']),
            MedicineLocation::create(['cabinet'=>'B','shelf'=>'1','level'=>'Bottom','section'=>'Center','notes'=>'Pain relief']),
            MedicineLocation::create(['cabinet'=>'B','shelf'=>'2','level'=>'Middle','section'=>'Right','notes'=>'Vitamins & OTC']),
            MedicineLocation::create(['cabinet'=>'C','shelf'=>'1','level'=>'Top','section'=>'Left','notes'=>'Refrigerated']),
            MedicineLocation::create(['cabinet'=>'C','shelf'=>'1','level'=>'Middle','section'=>'Center','notes'=>'Injectables']),
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
            'morning'   => ['07:00', '15:00'],   // Day shift
            'afternoon' => ['15:00', '23:00'],   // Evening shift
            'night'     => ['23:00', '07:00'],   // Night shift
            'on_call'   => ['09:00', '17:00'],   // On-call hours
        ];

        // Generate 7 days of randomized rotating shifts for clinical staff
        $clinicalStaff = [$doc1, $doc2, $nurse1, $nurse2, $asst];
        $shiftKeys = ['morning', 'afternoon', 'night', 'on_call'];

        foreach ($clinicalStaff as $i => $staffMember) {
            for ($d = 0; $d < 7; $d++) {
                // Skip ~1 day per week (rest day) randomly
                if (rand(1, 7) === 1) continue;

                // Rotate shifts so each staff cycles through patterns differently
                $shiftKey = $shiftKeys[($i + $d) % count($shiftKeys)];
                [$start, $end] = $shiftPatterns[$shiftKey];

                Shift::create([
                    'user_id'    => $staffMember->id,
                    'shift_type' => $shiftKey,
                    'shift_date' => today()->addDays($d),
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
        Message::create(['sender_id'=>$admin->id,'receiver_id'=>$nurse1->id,'body'=>'Hi Joy, just a reminder — please update the inventory after every dispense. Thanks!','is_read'=>true]);
        Message::create(['sender_id'=>$nurse1->id,'receiver_id'=>$admin->id,'body'=>'Yes po Dr. Chen! Will do right away.','is_read'=>true]);
    }
}
