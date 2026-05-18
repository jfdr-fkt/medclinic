<?php
namespace Database\Seeders;

use App\Models\Patient;
use App\Models\User;
use App\Models\Visit;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class SeedTodayVisitsSeeder extends Seeder
{
    public function run(): void
    {
        $secretary = User::where('email', 'secretary@clinic.com')->first()
            ?? User::where('role', 'secretary')->first()
            ?? User::where('role', 'admin')->first();

        $nurse  = User::where('email', 'nurse@clinic.com')->first()  ?? User::where('role', 'nurse')->first();
        $doctor = User::where('email', 'doctor@clinic.com')->first() ?? User::where('role', 'doctor')->first();
        $pharm  = User::where('email', 'pharmacist@clinic.com')->first() ?? User::where('role', 'pharmacist')->first();

        $patients = Patient::orderBy('id')->take(6)->get();
        if ($patients->count() < 4) return;

        // Wipe today's existing visits so seeding is idempotent for the demo.
        Visit::whereDate('checked_in_at', today())->delete();

        $rows = [
            // Just walked in
            [
                'patient_id' => $patients[0]->id,
                'checked_in_at' => Carbon::today()->setTime(8, 15),
                'status' => 'waiting',
                'reason' => 'Follow-up consultation',
                'current_staff_id' => null,
            ],
            [
                'patient_id' => $patients[1]->id,
                'checked_in_at' => Carbon::today()->setTime(8, 40),
                'status' => 'waiting',
                'reason' => 'New patient — chest pain',
                'current_staff_id' => null,
            ],
            // Being triaged
            [
                'patient_id' => $patients[2]->id,
                'checked_in_at' => Carbon::today()->setTime(7, 55),
                'status' => 'with_nurse',
                'reason' => 'Annual physical',
                'current_staff_id' => $nurse?->id,
            ],
            // In the room with doctor
            [
                'patient_id' => $patients[3]->id,
                'checked_in_at' => Carbon::today()->setTime(7, 30),
                'status' => 'with_doctor',
                'reason' => 'Rash on arm',
                'current_staff_id' => $doctor?->id,
            ],
            // Waiting on a script at the pharmacy window
            [
                'patient_id' => $patients[4]->id ?? $patients[0]->id,
                'checked_in_at' => Carbon::today()->setTime(7, 10),
                'status' => 'pharmacy',
                'reason' => 'Refill — hypertension meds',
                'current_staff_id' => $pharm?->id,
            ],
            // Already gone home
            [
                'patient_id' => $patients[5]->id ?? $patients[1]->id,
                'checked_in_at' => Carbon::today()->setTime(6, 50),
                'checked_out_at' => Carbon::today()->setTime(7, 25),
                'status' => 'completed',
                'reason' => 'Quick blood pressure check',
                'current_staff_id' => null,
            ],
        ];

        foreach ($rows as $r) {
            Visit::create(array_merge($r, ['recorded_by' => $secretary?->id]));
        }
    }
}
