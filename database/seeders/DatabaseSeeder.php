<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Patient;
use App\Models\MedicineLocation;
use App\Models\Medicine;
use App\Models\Inventory;
use App\Models\Shift;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // Create Staff
        $admin = User::create(['name'=>'Dr. Sarah Chen','email'=>'admin@clinic.com','password'=>Hash::make('password'),'role'=>'admin','phone'=>'0917-123-4567','specialization'=>'Internal Medicine','last_seen_at'=>now(),'is_active'=>true]);
        $doctor1 = User::create(['name'=>'Dr. James Wilson','email'=>'doctor@clinic.com','password'=>Hash::make('password'),'role'=>'doctor','phone'=>'0918-234-5678','specialization'=>'Cardiology','last_seen_at'=>now()->subMinutes(2),'is_active'=>true]);
        $doctor2 = User::create(['name'=>'Dr. Maria Santos','email'=>'doctor2@clinic.com','password'=>Hash::make('password'),'role'=>'doctor','phone'=>'0919-345-6789','specialization'=>'Pediatrics','last_seen_at'=>now()->subMinutes(10),'is_active'=>true]);
        $nurse1 = User::create(['name'=>'Nurse Joy Reyes','email'=>'nurse@clinic.com','password'=>Hash::make('password'),'role'=>'nurse','phone'=>'0920-456-7890','last_seen_at'=>now()->subMinutes(1),'is_active'=>true]);
        $nurse2 = User::create(['name'=>'Nurse Mark Lim','email'=>'nurse2@clinic.com','password'=>Hash::make('password'),'role'=>'nurse','phone'=>'0921-567-8901','last_seen_at'=>now()->subMinutes(8),'is_active'=>true]);
        $assistant = User::create(['name'=>'Anna Cruz','email'=>'assistant@clinic.com','password'=>Hash::make('password'),'role'=>'assistant','phone'=>'0922-678-9012','last_seen_at'=>now()->subMinutes(15),'is_active'=>true]);

        // Create Patients
        $patients = [
            ['patient_id'=>'P-2024-001','name'=>'Juan Dela Cruz','date_of_birth'=>'1985-03-15','phone'=>'0933-111-2222','address'=>'123 Main St, Manila','medical_history'=>'Hypertension, Diabetes Type 2','assigned_nurse_id'=>$nurse1->id,'assigned_doctor_id'=>$doctor1->id,'last_visit'=>now()->subHours(2)],
            ['patient_id'=>'P-2024-002','name'=>'Maria Clara','date_of_birth'=>'1990-07-22','phone'=>'0933-222-3333','address'=>'456 Oak Ave, Quezon City','medical_history'=>'Asthma','assigned_nurse_id'=>$nurse1->id,'assigned_doctor_id'=>$doctor2->id,'last_visit'=>now()->subHours(4)],
            ['patient_id'=>'P-2024-003','name'=>'Pedro Penduko','date_of_birth'=>'1978-11-30','phone'=>'0933-333-4444','address'=>'789 Pine Rd, Makati','medical_history'=>'Arthritis, High Cholesterol','assigned_nurse_id'=>$nurse2->id,'assigned_doctor_id'=>$doctor1->id,'last_visit'=>now()->subHours(1)],
            ['patient_id'=>'P-2024-004','name'=>'Ana Lisa','date_of_birth'=>'2000-01-10','phone'=>'0933-444-5555','address'=>'321 Elm St, Pasig','medical_history'=>'None','assigned_nurse_id'=>$nurse2->id,'assigned_doctor_id'=>$doctor2->id,'last_visit'=>now()->subMinutes(30)],
            ['patient_id'=>'P-2024-005','name'=>'Rizal Mercado','date_of_birth'=>'1965-05-20','phone'=>'0933-555-6666','address'=>'555 Rizal Ave, Caloocan','medical_history'=>'Heart Disease, Hypertension','assigned_nurse_id'=>$nurse1->id,'assigned_doctor_id'=>$doctor1->id,'last_visit'=>now()->subDays(2)],
            ['patient_id'=>'P-2024-006','name'=>'Gabriela Silang','date_of_birth'=>'1988-09-14','phone'=>'0933-666-7777','address'=>'888 Hero St, Mandaluyong','medical_history'=>'Migraine','assigned_nurse_id'=>$nurse2->id,'assigned_doctor_id'=>$doctor2->id,'last_visit'=>now()->subDays(5)],
            ['patient_id'=>'P-2024-007','name'=>'Andres Bonifacio','date_of_birth'=>'1995-12-01','phone'=>'0933-777-8888','address'=>'111 Katipunan Rd, Marikina','medical_history'=>'None','assigned_nurse_id'=>$nurse1->id,'assigned_doctor_id'=>$doctor1->id,'last_visit'=>now()->subDays(1)],
            ['patient_id'=>'P-2024-008','name'=>'Melchora Aquino','date_of_birth'=>'1945-08-18','phone'=>'0933-888-9999','address'=>'222 Tandang Sora, QC','medical_history'=>'Diabetes, Hypertension, Arthritis','assigned_nurse_id'=>$nurse2->id,'assigned_doctor_id'=>$doctor2->id,'last_visit'=>now()->subMinutes(45)],
        ];
        foreach($patients as $p) Patient::create($p);

        // Create Medicine Locations
        $locations = [
            ['cabinet'=>'A','shelf'=>'1','level'=>'Top','section'=>'Left','notes'=>'Emergency meds'],
            ['cabinet'=>'A','shelf'=>'1','level'=>'Middle','section'=>'Center','notes'=>'Daily prescriptions'],
            ['cabinet'=>'A','shelf'=>'2','level'=>'Top','section'=>'Right','notes'=>'Pediatric meds'],
            ['cabinet'=>'B','shelf'=>'1','level'=>'Top','section'=>'Left','notes'=>'Antibiotics'],
            ['cabinet'=>'B','shelf'=>'1','level'=>'Bottom','section'=>'Center','notes'=>'Pain relief'],
            ['cabinet'=>'B','shelf'=>'2','level'=>'Middle','section'=>'Right','notes'=>'Vitamins & OTC'],
            ['cabinet'=>'C','shelf'=>'1','level'=>'Top','section'=>'Left','notes'=>'Refrigerated'],
            ['cabinet'=>'C','shelf'=>'1','level'=>'Middle','section'=>'Center','notes'=>'Injectables'],
        ];
        foreach($locations as $l) MedicineLocation::create($l);

        // Create Medicines with Inventory
        $medicines = [
            ['name'=>'Paracetamol 500mg','generic_name'=>'Acetaminophen','barcode'=>'1234567890123','qr_code'=>'PARA500','location_id'=>6,'type'=>'normal','description'=>'Pain reliever and fever reducer','dosage'=>'1-2 tablets every 4-6 hours','quantity'=>150,'min_stock_level'=>20,'expiration_date'=>'2026-12-31','batch_number'=>'BT-2024-001'],
            ['name'=>'Amoxicillin 250mg','generic_name'=>'Amoxicillin','barcode'=>'1234567890124','qr_code'=>'AMOX250','location_id'=>4,'type'=>'prescription','description'=>'Antibiotic for bacterial infections','dosage'=>'1 capsule 3x daily for 7 days','quantity'=>8,'min_stock_level'=>15,'expiration_date'=>'2025-08-15','batch_number'=>'BT-2024-002'],
            ['name'=>'Metformin 500mg','generic_name'=>'Metformin HCl','barcode'=>'1234567890125','qr_code'=>'METF500','location_id'=>2,'type'=>'prescription','description'=>'Diabetes medication','dosage'=>'1 tablet 2x daily with meals','quantity'=>45,'min_stock_level'=>30,'expiration_date'=>'2026-03-20','batch_number'=>'BT-2024-003'],
            ['name'=>'Salbutamol Inhaler','generic_name'=>'Albuterol','barcode'=>'1234567890126','qr_code'=>'SALB100','location_id'=>3,'type'=>'prescription','description'=>'Asthma rescue inhaler','dosage'=>'2 puffs as needed','quantity'=>12,'min_stock_level'=>10,'expiration_date'=>'2025-11-30','batch_number'=>'BT-2024-004'],
            ['name'=>'Amlodipine 5mg','generic_name'=>'Amlodipine Besylate','barcode'=>'1234567890127','qr_code'=>'AMLO5','location_id'=>2,'type'=>'prescription','description'=>'Blood pressure medication','dosage'=>'1 tablet daily','quantity'=>3,'min_stock_level'=>20,'expiration_date'=>'2026-01-15','batch_number'=>'BT-2024-005'],
            ['name'=>'Ibuprofen 400mg','generic_name'=>'Ibuprofen','barcode'=>'1234567890128','qr_code'=>'IBU400','location_id'=>5,'type'=>'normal','description'=>'Anti-inflammatory pain reliever','dosage'=>'1 tablet every 6-8 hours','quantity'=>200,'min_stock_level'=>25,'expiration_date'=>'2027-02-28','batch_number'=>'BT-2024-006'],
            ['name'=>'Vitamin C 500mg','generic_name'=>'Ascorbic Acid','barcode'=>'1234567890129','qr_code'=>'VITC500','location_id'=>6,'type'=>'normal','description'=>'Immune system support','dosage'=>'1 tablet daily','quantity'=>500,'min_stock_level'=>50,'expiration_date'=>'2026-09-10','batch_number'=>'BT-2024-007'],
            ['name'=>'Insulin Glargine','generic_name'=>'Insulin Glargine','barcode'=>'1234567890130','qr_code'=>'INSULIN','location_id'=>7,'type'=>'prescription','description'=>'Long-acting insulin','dosage'=>'As prescribed by doctor','quantity'=>5,'min_stock_level'=>8,'expiration_date'=>'2025-06-15','batch_number'=>'BT-2024-008'],
            ['name'=>'Cetirizine 10mg','generic_name'=>'Cetirizine HCl','barcode'=>'1234567890131','qr_code'=>'CET10','location_id'=>6,'type'=>'normal','description'=>'Antihistamine for allergies','dosage'=>'1 tablet daily','quantity'=>80,'min_stock_level'=>15,'expiration_date'=>'2026-11-20','batch_number'=>'BT-2024-009'],
            ['name'=>'Losartan 50mg','generic_name'=>'Losartan Potassium','barcode'=>'1234567890132','qr_code'=>'LOS50','location_id'=>2,'type'=>'prescription','description'=>'Blood pressure medication','dosage'=>'1 tablet daily','quantity'=>18,'min_stock_level'=>20,'expiration_date'=>'2025-12-25','batch_number'=>'BT-2024-010'],
        ];

        foreach($medicines as $m) {
            $med = Medicine::create([
                'name'=>$m['name'],'generic_name'=>$m['generic_name'],'barcode'=>$m['barcode'],'qr_code'=>$m['qr_code'],
                'location_id'=>$m['location_id'],'type'=>$m['type'],'description'=>$m['description'],'dosage'=>$m['dosage']
            ]);
            Inventory::create([
                'medicine_id'=>$med->id,'quantity'=>$m['quantity'],'min_stock_level'=>$m['min_stock_level'],
                'expiration_date'=>$m['expiration_date'],'batch_number'=>$m['batch_number']
            ]);
        }

        // Create Shifts
        $shifts = [
            ['user_id'=>$doctor1->id,'shift_type'=>'morning','shift_date'=>today(),'start_time'=>'08:00','end_time'=>'12:00','is_active'=>true],
            ['user_id'=>$doctor2->id,'shift_type'=>'afternoon','shift_date'=>today(),'start_time'=>'13:00','end_time'=>'17:00','is_active'=>false],
            ['user_id'=>$nurse1->id,'shift_type'=>'morning','shift_date'=>today(),'start_time'=>'07:00','end_time'=>'15:00','is_active'=>true],
            ['user_id'=>$nurse2->id,'shift_type'=>'afternoon','shift_date'=>today(),'start_time'=>'15:00','end_time'=>'23:00','is_active'=>false],
        ];
        foreach($shifts as $s) Shift::create($s);
        $this->call(DemoDataSeeder::class);
    }
}
