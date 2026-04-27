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
        // Staff
        $admin = User::create(['name'=>'Dr. Sarah Chen','email'=>'admin@clinic.com','password'=>Hash::make('password'),'role'=>'admin','phone'=>'0917-123-4567','specialization'=>'Internal Medicine','last_seen_at'=>now(),'is_active'=>true]);
        $doc1 = User::create(['name'=>'Dr. James Wilson','email'=>'doctor@clinic.com','password'=>Hash::make('password'),'role'=>'doctor','phone'=>'0918-234-5678','specialization'=>'Cardiology','last_seen_at'=>now()->subMinutes(2),'is_active'=>true]);
        $doc2 = User::create(['name'=>'Dr. Maria Santos','email'=>'doctor2@clinic.com','password'=>Hash::make('password'),'role'=>'doctor','phone'=>'0919-345-6789','specialization'=>'Pediatrics','last_seen_at'=>now()->subMinutes(10),'is_active'=>true]);
        $nurse1 = User::create(['name'=>'Nurse Joy Reyes','email'=>'nurse@clinic.com','password'=>Hash::make('password'),'role'=>'nurse','phone'=>'0920-456-7890','last_seen_at'=>now()->subMinutes(1),'is_active'=>true]);
        $nurse2 = User::create(['name'=>'Nurse Mark Lim','email'=>'nurse2@clinic.com','password'=>Hash::make('password'),'role'=>'nurse','phone'=>'0921-567-8901','last_seen_at'=>now()->subMinutes(8),'is_active'=>true]);
        $asst = User::create(['name'=>'Anna Cruz','email'=>'assistant@clinic.com','password'=>Hash::make('password'),'role'=>'assistant','phone'=>'0922-678-9012','last_seen_at'=>now()->subMinutes(15),'is_active'=>true]);

        // Patients
        Patient::create(['patient_id'=>'P-2024-001','name'=>'Juan Dela Cruz','date_of_birth'=>'1985-03-15','phone'=>'0933-111-2222','address'=>'123 Main St, Manila','medical_history'=>'Hypertension, Diabetes Type 2','assigned_nurse_id'=>$nurse1->id,'assigned_doctor_id'=>$doc1->id,'last_visit'=>now()->subHours(2)]);
        Patient::create(['patient_id'=>'P-2024-002','name'=>'Maria Clara','date_of_birth'=>'1990-07-22','phone'=>'0933-222-3333','address'=>'456 Oak Ave, QC','medical_history'=>'Asthma','assigned_nurse_id'=>$nurse1->id,'assigned_doctor_id'=>$doc2->id,'last_visit'=>now()->subHours(4)]);
        Patient::create(['patient_id'=>'P-2024-003','name'=>'Pedro Penduko','date_of_birth'=>'1978-11-30','phone'=>'0933-333-4444','address'=>'789 Pine Rd, Makati','medical_history'=>'Arthritis','assigned_nurse_id'=>$nurse2->id,'assigned_doctor_id'=>$doc1->id,'last_visit'=>now()->subHours(1)]);
        Patient::create(['patient_id'=>'P-2024-004','name'=>'Ana Lisa','date_of_birth'=>'2000-01-10','phone'=>'0933-444-5555','address'=>'321 Elm St, Pasig','medical_history'=>'None','assigned_nurse_id'=>$nurse2->id,'assigned_doctor_id'=>$doc2->id,'last_visit'=>now()->subMinutes(30)]);
        Patient::create(['patient_id'=>'P-2024-005','name'=>'Rizal Mercado','date_of_birth'=>'1965-05-20','phone'=>'0933-555-6666','address'=>'555 Rizal Ave, Caloocan','medical_history'=>'Heart Disease','assigned_nurse_id'=>$nurse1->id,'assigned_doctor_id'=>$doc1->id,'last_visit'=>now()->subDays(2)]);

        // Locations
        $loc1 = MedicineLocation::create(['cabinet'=>'A','shelf'=>'1','level'=>'Top','section'=>'Left','notes'=>'Emergency']);
        $loc2 = MedicineLocation::create(['cabinet'=>'A','shelf'=>'1','level'=>'Middle','section'=>'Center','notes'=>'Daily RX']);
        $loc3 = MedicineLocation::create(['cabinet'=>'B','shelf'=>'1','level'=>'Top','section'=>'Left','notes'=>'Antibiotics']);
        $loc4 = MedicineLocation::create(['cabinet'=>'B','shelf'=>'2','level'=>'Middle','section'=>'Right','notes'=>'OTC']);

        // Medicines + Inventory
        $med1 = Medicine::create(['name'=>'Paracetamol 500mg','generic_name'=>'Acetaminophen','barcode'=>'1234567890123','qr_code'=>'PARA500','location_id'=>$loc4->id,'type'=>'normal','description'=>'Pain/fever','dosage'=>'1-2 tabs q4-6h']);
        Inventory::create(['medicine_id'=>$med1->id,'quantity'=>150,'min_stock_level'=>20,'expiration_date'=>'2026-12-31','batch_number'=>'BT-001']);

        $med2 = Medicine::create(['name'=>'Amoxicillin 250mg','generic_name'=>'Amoxicillin','barcode'=>'1234567890124','qr_code'=>'AMOX250','location_id'=>$loc3->id,'type'=>'prescription','description'=>'Antibiotic','dosage'=>'1 cap TID x7d']);
        Inventory::create(['medicine_id'=>$med2->id,'quantity'=>8,'min_stock_level'=>15,'expiration_date'=>'2025-08-15','batch_number'=>'BT-002']);

        $med3 = Medicine::create(['name'=>'Metformin 500mg','generic_name'=>'Metformin HCl','barcode'=>'1234567890125','qr_code'=>'METF500','location_id'=>$loc2->id,'type'=>'prescription','description'=>'Diabetes','dosage'=>'1 tab BID']);
        Inventory::create(['medicine_id'=>$med3->id,'quantity'=>45,'min_stock_level'=>30,'expiration_date'=>'2026-03-20','batch_number'=>'BT-003']);

        $med4 = Medicine::create(['name'=>'Amlodipine 5mg','generic_name'=>'Amlodipine','barcode'=>'1234567890126','qr_code'=>'AMLO5','location_id'=>$loc2->id,'type'=>'prescription','description'=>'BP med','dosage'=>'1 tab daily']);
        Inventory::create(['medicine_id'=>$med4->id,'quantity'=>3,'min_stock_level'=>20,'expiration_date'=>'2026-01-15','batch_number'=>'BT-004']);

        $med5 = Medicine::create(['name'=>'Vitamin C 500mg','generic_name'=>'Ascorbic Acid','barcode'=>'1234567890127','qr_code'=>'VITC500','location_id'=>$loc4->id,'type'=>'normal','description'=>'Immune support','dosage'=>'1 tab daily']);
        Inventory::create(['medicine_id'=>$med5->id,'quantity'=>500,'min_stock_level'=>50,'expiration_date'=>'2026-09-10','batch_number'=>'BT-005']);

        // Shifts
        Shift::create(['user_id'=>$doc1->id,'shift_type'=>'morning','shift_date'=>today(),'start_time'=>'08:00','end_time'=>'12:00','is_active'=>true]);
        Shift::create(['user_id'=>$doc2->id,'shift_type'=>'afternoon','shift_date'=>today(),'start_time'=>'13:00','end_time'=>'17:00','is_active'=>false]);
        Shift::create(['user_id'=>$nurse1->id,'shift_type'=>'morning','shift_date'=>today(),'start_time'=>'07:00','end_time'=>'15:00','is_active'=>true]);
        Shift::create(['user_id'=>$nurse2->id,'shift_type'=>'afternoon','shift_date'=>today(),'start_time'=>'15:00','end_time'=>'23:00','is_active'=>false]);
    }
}