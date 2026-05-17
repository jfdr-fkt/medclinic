<?php
namespace Database\Seeders;

use App\Models\Inventory;
use App\Models\Medicine;
use App\Models\MedicineLocation;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Seeder;

class ExpandClinicalDataSeeder extends Seeder
{
    /**
     * Idempotent top-up that:
     *   1. Backfills the new clinical columns on the 9 originally-seeded patients (if they still exist).
     *   2. Adds Filipino-named patients until the directory hits 20 total.
     *   3. Adds Philippines-flavored medicines (Biogesic, Bioflu, Solmux, etc.) until inventory hits 20 total.
     *
     * Safe to re-run.
     */
    public function run(): void
    {
        $this->backfillExistingPatients();
        $this->relocateToMindanao();
        $this->addPatientsUntil(20);
        $this->addPhilippinesMedicinesUntil(20);
    }

    /**
     * Reseat a handful of patients to Mindanao addresses for geographic variety
     * in the demo. Only rewrites the address when it still matches the original
     * Metro Manila seed value, so admin-edited addresses are preserved.
     */
    private function relocateToMindanao(): void
    {
        $moves = [
            'P-2024-002' => ['from' => '456 Oak Ave, Quezon City',    'to' => '456 Oak Ave, Brgy. Apopong, General Santos'],
            'P-2024-003' => ['from' => '789 Pine Rd, Makati',         'to' => '789 Pine Rd, Brgy. Talomo, Davao City'],
            'P-2024-005' => ['from' => '555 Rizal Ave, Caloocan',     'to' => '555 Rizal Ave, Brgy. Patag, Cagayan de Oro'],
        ];
        foreach ($moves as $pid => $m) {
            Patient::where('patient_id', $pid)
                ->where('address', $m['from'])
                ->update(['address' => $m['to']]);
        }
    }

    private function backfillExistingPatients(): void
    {
        // Keyed by patient_id from DemoDataSeeder. Only updates fields that are still NULL,
        // so an admin who edited a patient via the UI doesn't get clobbered.
        $backfill = [
            'P-2024-001' => ['sex'=>'male',  'blood_type'=>'O+',  'height_cm'=>168, 'weight_kg'=>78.4, 'allergies'=>'Penicillin',           'chronic_conditions'=>'Hypertension (Stage 1), Type 2 Diabetes', 'emergency_contact_name'=>'Carmen Dela Cruz', 'emergency_contact_phone'=>'0917-555-0101'],
            'P-2024-002' => ['sex'=>'female','blood_type'=>'A+',  'height_cm'=>160, 'weight_kg'=>54.0, 'allergies'=>'Sulfa drugs',          'chronic_conditions'=>'Mild asthma — uses Salbutamol PRN',        'emergency_contact_name'=>'Jose Clara',       'emergency_contact_phone'=>'0917-555-0102'],
            'P-2024-003' => ['sex'=>'male',  'blood_type'=>'B+',  'height_cm'=>172, 'weight_kg'=>85.2, 'allergies'=>'NKDA',                 'chronic_conditions'=>'Osteoarthritis (knees), Hypercholesterolemia', 'emergency_contact_name'=>'Linda Penduko',   'emergency_contact_phone'=>'0917-555-0103'],
            'P-2024-004' => ['sex'=>'female','blood_type'=>'O-',  'height_cm'=>165, 'weight_kg'=>52.1, 'allergies'=>'NKDA',                 'chronic_conditions'=>'None',                                       'emergency_contact_name'=>'Pedro Lisa',       'emergency_contact_phone'=>'0917-555-0104'],
            'P-2024-005' => ['sex'=>'male',  'blood_type'=>'A-',  'height_cm'=>170, 'weight_kg'=>72.5, 'allergies'=>'Shellfish',            'chronic_conditions'=>'Coronary artery disease (post-stent 2022), Hypertension', 'emergency_contact_name'=>'Teresa Mercado', 'emergency_contact_phone'=>'0917-555-0105'],
            'P-2024-006' => ['sex'=>'female','blood_type'=>'AB+', 'height_cm'=>158, 'weight_kg'=>49.0, 'allergies'=>'Aspirin (causes hives)','chronic_conditions'=>'Chronic migraine',                          'emergency_contact_name'=>'Tomas Silang',     'emergency_contact_phone'=>'0917-555-0106'],
            'P-2024-007' => ['sex'=>'male',  'blood_type'=>'O+',  'height_cm'=>175, 'weight_kg'=>70.0, 'allergies'=>'NKDA',                 'chronic_conditions'=>'None',                                       'emergency_contact_name'=>'Procesa Bonifacio','emergency_contact_phone'=>'0917-555-0107'],
            'P-2024-008' => ['sex'=>'female','blood_type'=>'B-',  'height_cm'=>152, 'weight_kg'=>58.0, 'allergies'=>'NKDA',                 'chronic_conditions'=>'Type 2 Diabetes (insulin-dependent), Hypertension, Osteoarthritis', 'emergency_contact_name'=>'Juan Aquino',   'emergency_contact_phone'=>'0917-555-0108'],
            'P-67-420'   => ['sex'=>'other', 'blood_type'=>'unknown','height_cm'=>167, 'weight_kg'=>67.0, 'allergies'=>'Allergic to taking life seriously', 'chronic_conditions'=>'Chronic meme syndrome',         'emergency_contact_name'=>'Mr. Meme',        'emergency_contact_phone'=>'0917-555-4200'],
        ];

        foreach ($backfill as $pid => $fields) {
            $patient = Patient::where('patient_id', $pid)->first();
            if (!$patient) continue;
            $updates = [];
            foreach ($fields as $col => $val) {
                if (is_null($patient->{$col})) $updates[$col] = $val;
            }
            if ($updates) $patient->update($updates);
        }
    }

    private function addPatientsUntil(int $target): void
    {
        $needed = max(0, $target - Patient::count());
        if ($needed === 0) return;

        $nurses  = User::where('role', 'nurse')->pluck('id')->all();
        $doctors = User::where('role', 'doctor')->pluck('id')->all();

        // Cycle through nurses/doctors so assignments are distributed.
        $pickNurse  = fn($i) => $nurses[$i % max(count($nurses), 1)]  ?? null;
        $pickDoctor = fn($i) => $doctors[$i % max(count($doctors), 1)] ?? null;

        // 11 new Filipino-named patients with realistic clinical profiles.
        $newPatients = [
            ['patient_id'=>'P-2025-001','name'=>'Joselito Reyes',    'date_of_birth'=>'1972-06-12','sex'=>'male',  'blood_type'=>'O+',  'height_cm'=>171,'weight_kg'=>82.3,'phone'=>'0917-200-1001','address'=>'14 Mabini St, San Juan, Metro Manila','medical_history'=>"2024: BP control adjustment. 2023: ECG normal. 2022: Diagnosed with hypertension.",'allergies'=>'NKDA','chronic_conditions'=>'Hypertension — on Losartan 50mg daily','emergency_contact_name'=>'Lorna Reyes','emergency_contact_phone'=>'0917-200-1100'],
            ['patient_id'=>'P-2025-002','name'=>'Carmen Villanueva', 'date_of_birth'=>'1958-11-03','sex'=>'female','blood_type'=>'A+',  'height_cm'=>154,'weight_kg'=>61.5,'phone'=>'0917-200-1002','address'=>'77 Rizal St, Brgy. Poblacion, Davao City','medical_history'=>"2025: Started Glipizide. 2024: HbA1c 8.1% — diabetes uncontrolled, dietitian referral. 2020: Cataract surgery (R eye).",'allergies'=>'Sulfa drugs (rash)','chronic_conditions'=>'Type 2 Diabetes, Cataracts (post-op R), early CKD','emergency_contact_name'=>'Antonio Villanueva','emergency_contact_phone'=>'0917-200-1102'],
            ['patient_id'=>'P-2025-003','name'=>'Ricardo Cruz',      'date_of_birth'=>'1989-02-28','sex'=>'male',  'blood_type'=>'B+',  'height_cm'=>178,'weight_kg'=>92.7,'phone'=>'0917-200-1003','address'=>'8 Velez St, Brgy. Carmen, Cagayan de Oro','medical_history'=>"2025: Annual checkup — labs pending. 2023: Knee MRI showed mild meniscus tear, conservative management.",'allergies'=>'NKDA','chronic_conditions'=>'Obesity (BMI 29.3), pre-diabetic','emergency_contact_name'=>'Marites Cruz','emergency_contact_phone'=>'0917-200-1103'],
            ['patient_id'=>'P-2025-004','name'=>'Luna Mendoza',      'date_of_birth'=>'2001-09-17','sex'=>'female','blood_type'=>'O+',  'height_cm'=>162,'weight_kg'=>50.4,'phone'=>'0917-200-1004','address'=>'3 Katipunan Ext, Loyola Heights, QC','medical_history'=>"2024: Started cetirizine for seasonal allergies. 2023: URTI, full recovery.",'allergies'=>'Dust mites, pollen','chronic_conditions'=>'Allergic rhinitis','emergency_contact_name'=>'Felisa Mendoza','emergency_contact_phone'=>'0917-200-1104'],
            ['patient_id'=>'P-2025-005','name'=>'Mateo Bautista',    'date_of_birth'=>'2017-04-22','sex'=>'male',  'blood_type'=>'A-',  'height_cm'=>120,'weight_kg'=>23.5,'phone'=>'0917-200-1005','address'=>'21 Sampaguita St, Brgy. Lagao, General Santos','medical_history'=>"2025-03: Routine pediatric checkup — growing well. Immunizations up to date through MMR booster.",'allergies'=>'NKDA','chronic_conditions'=>'Mild eczema (atopic)','emergency_contact_name'=>'Patricia Bautista (mother)','emergency_contact_phone'=>'0917-200-1105'],
            ['patient_id'=>'P-2025-006','name'=>'Sophia Garcia',     'date_of_birth'=>'1993-12-04','sex'=>'female','blood_type'=>'AB-', 'height_cm'=>167,'weight_kg'=>59.0,'phone'=>'0917-200-1006','address'=>'9 Acacia Lane, Brgy. Sta. Maria, Zamboanga City','medical_history'=>"2024: Switched to combined OCP for endometriosis pain management. 2022: Laparoscopic ovarian cyst removal.",'allergies'=>'NKDA','chronic_conditions'=>'Endometriosis','emergency_contact_name'=>'Miguel Garcia','emergency_contact_phone'=>'0917-200-1106'],
            ['patient_id'=>'P-2025-007','name'=>'Antonio Magbanua',  'date_of_birth'=>'1951-08-09','sex'=>'male',  'blood_type'=>'O-',  'height_cm'=>166,'weight_kg'=>68.0,'phone'=>'0917-200-1007','address'=>'45 Aurora Blvd, Cubao, QC','medical_history'=>"2024: Pneumonia, admitted 4 days. 2023: COPD diagnosis confirmed via spirometry. Ex-smoker (40 pack-years, quit 2020).",'allergies'=>'NKDA','chronic_conditions'=>'COPD (GOLD II), hypertension','emergency_contact_name'=>'Estrella Magbanua','emergency_contact_phone'=>'0917-200-1107'],
            ['patient_id'=>'P-2025-008','name'=>'Liwayway Castro',   'date_of_birth'=>'1985-05-30','sex'=>'female','blood_type'=>'B+',  'height_cm'=>159,'weight_kg'=>72.8,'phone'=>'0917-200-1008','address'=>'12 Quezon Ave, Brgy. Pala-o, Iligan City','medical_history'=>"2025: Started levothyroxine 50mcg. 2024: Hashimoto's thyroiditis diagnosed.",'allergies'=>'Iodine contrast (mild rash)','chronic_conditions'=>'Hypothyroidism (Hashimoto)','emergency_contact_name'=>'Rafael Castro','emergency_contact_phone'=>'0917-200-1108'],
            ['patient_id'=>'P-2025-009','name'=>'Renato del Mundo',  'date_of_birth'=>'1968-01-14','sex'=>'male',  'blood_type'=>'A+',  'height_cm'=>173,'weight_kg'=>88.0,'phone'=>'0917-200-1009','address'=>'30 Quirino Hwy, Novaliches, QC','medical_history'=>"2024: Stent placed (LAD). 2024-Jan: STEMI — successful PCI. On dual antiplatelet therapy.",'allergies'=>'NKDA','chronic_conditions'=>'Coronary artery disease (post-PCI), hyperlipidemia, hypertension','emergency_contact_name'=>'Cecilia del Mundo','emergency_contact_phone'=>'0917-200-1109'],
            ['patient_id'=>'P-2025-010','name'=>'Isabella Lim',      'date_of_birth'=>'1996-07-25','sex'=>'female','blood_type'=>'O+',  'height_cm'=>164,'weight_kg'=>55.2,'phone'=>'0917-200-1010','address'=>'5 Greenhills North, San Juan','medical_history'=>"2025: Pregnancy confirmed (G1P0, ~14 weeks). Prenatal vitamins started.",'allergies'=>'NKDA','chronic_conditions'=>'Currently pregnant — first trimester','emergency_contact_name'=>'Andrew Lim (husband)','emergency_contact_phone'=>'0917-200-1110'],
            ['patient_id'=>'P-2025-011','name'=>'Bayani Tolentino',  'date_of_birth'=>'1977-10-19','sex'=>'male',  'blood_type'=>'B-',  'height_cm'=>169,'weight_kg'=>76.0,'phone'=>'0917-200-1011','address'=>'18 Montilla Blvd, Brgy. Imadejas, Butuan City','medical_history'=>"2024: Gout flare, on allopurinol prophylaxis. 2023: Uric acid 9.2 mg/dL.",'allergies'=>'NKDA','chronic_conditions'=>'Gout, hyperuricemia','emergency_contact_name'=>'Rosario Tolentino','emergency_contact_phone'=>'0917-200-1111'],
            ['patient_id'=>'P-2025-012','name'=>'Marisol Aquino',   'date_of_birth'=>'1962-03-25','sex'=>'female','blood_type'=>'O+',  'height_cm'=>156,'weight_kg'=>64.0,'phone'=>'0917-200-1012','address'=>'7 Taft Ave, Pasay City','medical_history'=>"2025: Mammogram clear. 2023: Total knee replacement (L). 2018: Hysterectomy for fibroids.",'allergies'=>'Latex','chronic_conditions'=>'Osteoarthritis (post-TKR L), osteoporosis','emergency_contact_name'=>'Eduardo Aquino','emergency_contact_phone'=>'0917-200-1112'],
        ];

        $i = 0;
        foreach ($newPatients as $p) {
            if ($needed <= 0) break;
            // firstOrCreate makes this idempotent — re-runs are safe.
            $patient = Patient::firstOrCreate(
                ['patient_id' => $p['patient_id']],
                array_merge($p, [
                    'assigned_nurse_id'  => $pickNurse($i),
                    'assigned_doctor_id' => $pickDoctor($i),
                    'last_visit'         => now()->subDays(rand(1, 60)),
                ])
            );
            if ($patient->wasRecentlyCreated) $needed--;
            $i++;
        }
    }

    private function addPhilippinesMedicinesUntil(int $target): void
    {
        $needed = max(0, $target - Medicine::count());
        if ($needed === 0) return;

        // Pick locations by storage purpose so meds end up somewhere sensible.
        $pickLoc = function(string $notesContains) {
            return MedicineLocation::where('notes', 'LIKE', "%{$notesContains}%")->first()
                ?? MedicineLocation::inRandomOrder()->first();
        };

        $painLoc   = $pickLoc('Pain relief');
        $coldsLoc  = $pickLoc('Vitamins & OTC');
        $abxLoc    = $pickLoc('Antibiotics');
        $dailyLoc  = $pickLoc('Daily prescriptions');
        $vitLoc    = $pickLoc('Vitamins & OTC');

        // PH-flavored brand names common in any local botika. Mix of OTC + Rx.
        $newMeds = [
            ['name'=>'Biogesic 500mg',       'generic_name'=>'Paracetamol',                   'barcode'=>'4800016641012','qr_code'=>'BIO500',  'loc'=>$painLoc,  'type'=>'normal',       'dosage'=>'1 tab q4-6h PRN for fever/pain', 'qty'=>240,'min'=>30,'exp'=>'2027-06-30','batch'=>'BIO-2025-01'],
            ['name'=>'Dolfenal 500mg',       'generic_name'=>'Mefenamic Acid',                'barcode'=>'4800016641029','qr_code'=>'DOLF500', 'loc'=>$painLoc,  'type'=>'normal',       'dosage'=>'1 tab q6h PRN, take with food',  'qty'=>120,'min'=>20,'exp'=>'2026-11-15','batch'=>'DOLF-2025-01'],
            ['name'=>'Alaxan FR',            'generic_name'=>'Ibuprofen + Paracetamol',       'barcode'=>'4800016641036','qr_code'=>'ALAXFR',  'loc'=>$painLoc,  'type'=>'normal',       'dosage'=>'1 cap q8h PRN for muscle pain',  'qty'=>90, 'min'=>15,'exp'=>'2026-09-20','batch'=>'ALAX-2025-01'],
            ['name'=>'Neozep Forte',         'generic_name'=>'Phenylephrine + CPM + Paracetamol','barcode'=>'4800016641043','qr_code'=>'NEOZ',  'loc'=>$coldsLoc,'type'=>'normal',       'dosage'=>'1 tab q4-6h for cold symptoms',  'qty'=>80, 'min'=>15,'exp'=>'2026-08-10','batch'=>'NEO-2025-01'],
            ['name'=>'Bioflu',               'generic_name'=>'Phenylephrine + CPM + Paracetamol','barcode'=>'4800016641050','qr_code'=>'BIOFLU','loc'=>$coldsLoc,'type'=>'normal',       'dosage'=>'1 tab q6h for flu symptoms',     'qty'=>60, 'min'=>20,'exp'=>'2026-07-25','batch'=>'BFLU-2025-01'],
            ['name'=>'Solmux 500mg',         'generic_name'=>'Carbocisteine',                 'barcode'=>'4800016641067','qr_code'=>'SOLMUX', 'loc'=>$coldsLoc,'type'=>'normal',       'dosage'=>'1 cap TID for productive cough', 'qty'=>72, 'min'=>15,'exp'=>'2026-12-05','batch'=>'SOL-2025-01'],
            ['name'=>'Diatabs 2mg',          'generic_name'=>'Loperamide',                    'barcode'=>'4800016641074','qr_code'=>'DIA2',   'loc'=>$coldsLoc,'type'=>'normal',       'dosage'=>'1-2 caps after loose stool, max 8/day','qty'=>50,'min'=>10,'exp'=>'2027-03-15','batch'=>'DIA-2025-01'],
            ['name'=>'Buscopan 10mg',        'generic_name'=>'Hyoscine N-butylbromide',       'barcode'=>'4800016641081','qr_code'=>'BUSC10', 'loc'=>$coldsLoc,'type'=>'normal',       'dosage'=>'1-2 tabs TID for abdominal cramps','qty'=>40,'min'=>10,'exp'=>'2026-10-30','batch'=>'BUSC-2025-01'],
            ['name'=>'Enervon',              'generic_name'=>'Multivitamins + Vitamin C',     'barcode'=>'4800016641098','qr_code'=>'ENERV',  'loc'=>$vitLoc,  'type'=>'normal',       'dosage'=>'1 tab daily after meal',         'qty'=>180,'min'=>30,'exp'=>'2027-01-20','batch'=>'ENRV-2025-01'],
            ['name'=>'Kremil-S',             'generic_name'=>'Al hydroxide + Mg hydroxide + Simethicone','barcode'=>'4800016641104','qr_code'=>'KREMIL','loc'=>$coldsLoc,'type'=>'normal','dosage'=>'1-2 tabs PRN for acidity/gas', 'qty'=>96, 'min'=>20,'exp'=>'2026-12-25','batch'=>'KREM-2025-01'],
            ['name'=>'Co-Amoxiclav 625mg',   'generic_name'=>'Amoxicillin + Clavulanic Acid', 'barcode'=>'4800016641111','qr_code'=>'COAMOX', 'loc'=>$abxLoc,  'type'=>'prescription', 'dosage'=>'1 tab BID x7d for bacterial infection','qty'=>28,'min'=>15,'exp'=>'2026-06-15','batch'=>'COAM-2025-01'],
            ['name'=>'Cefalexin 500mg',      'generic_name'=>'Cephalexin',                    'barcode'=>'4800016641128','qr_code'=>'CEFAL',  'loc'=>$abxLoc,  'type'=>'prescription', 'dosage'=>'1 cap QID x7d for skin/soft tissue','qty'=>32,'min'=>15,'exp'=>'2026-05-30','batch'=>'CEF-2025-01'],
            ['name'=>'Loratadine 10mg',      'generic_name'=>'Loratadine',                    'barcode'=>'4800016641135','qr_code'=>'LORAT',  'loc'=>$coldsLoc,'type'=>'normal',       'dosage'=>'1 tab daily for allergies',      'qty'=>100,'min'=>20,'exp'=>'2027-02-10','batch'=>'LOR-2025-01'],
            ['name'=>'Omeprazole 20mg',      'generic_name'=>'Omeprazole',                    'barcode'=>'4800016641142','qr_code'=>'OMEP20', 'loc'=>$dailyLoc,'type'=>'prescription', 'dosage'=>'1 cap daily before breakfast',   'qty'=>56, 'min'=>20,'exp'=>'2026-11-12','batch'=>'OMEP-2025-01'],
            ['name'=>'Simvastatin 20mg',     'generic_name'=>'Simvastatin',                   'barcode'=>'4800016641159','qr_code'=>'SIMV20', 'loc'=>$dailyLoc,'type'=>'prescription', 'dosage'=>'1 tab nightly for cholesterol',  'qty'=>30, 'min'=>20,'exp'=>'2026-09-30','batch'=>'SIMV-2025-01'],
            ['name'=>'Conzace',              'generic_name'=>'Vitamins A, C, E + Zinc',       'barcode'=>'4800016641166','qr_code'=>'CONZ',   'loc'=>$vitLoc,  'type'=>'normal',       'dosage'=>'1 cap daily after meal',         'qty'=>150,'min'=>30,'exp'=>'2027-04-18','batch'=>'CONZ-2025-01'],
        ];

        foreach ($newMeds as $m) {
            if ($needed <= 0) break;
            $med = Medicine::firstOrCreate(
                ['name' => $m['name']],
                [
                    'generic_name' => $m['generic_name'],
                    'barcode'      => $m['barcode'],
                    'qr_code'      => $m['qr_code'],
                    'location_id'  => $m['loc']?->id,
                    'type'         => $m['type'],
                    'dosage'       => $m['dosage'],
                ]
            );
            if ($med->wasRecentlyCreated) {
                Inventory::create([
                    'medicine_id'     => $med->id,
                    'quantity'        => $m['qty'],
                    'min_stock_level' => $m['min'],
                    'expiration_date' => $m['exp'],
                    'batch_number'    => $m['batch'],
                ]);
                $needed--;
            }
        }
    }
}
