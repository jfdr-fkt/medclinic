<?php
namespace Database\Seeders;

use App\Models\Shift;
use App\Models\User;
use Illuminate\Database\Seeder;

class BackfillMonthOfShiftsSeeder extends Seeder
{
    public function run(): void
    {
        $shiftPatterns = [
            'morning' => ['07:00', '15:00'],
            'afternoon' => ['15:00', '23:00'],
            'night' => ['23:00', '07:00'],
            'on_call' => ['09:00', '17:00'],
        ];

        $scheduleRules = [
            'admin' => ['pool'=>['morning'],                      'weekdays'=>[1,2,3,4,5],     'offRate'=>0.05],
            'clinic_head' => ['pool'=>['morning','on_call'],            'weekdays'=>[1,2,3,4,5],     'offRate'=>0.10],
            'doctor' => ['pool'=>['morning','afternoon','on_call'],'weekdays'=>[1,2,3,4,5,6,7], 'offRate'=>0.20],
            'pharmacist' => ['pool'=>['morning','afternoon'],          'weekdays'=>[1,2,3,4,5,6],   'offRate'=>0.10],
            'nurse' => ['pool'=>['morning','afternoon','night'],  'weekdays'=>[1,2,3,4,5,6,7], 'offRate'=>0.20],
            'secretary' => ['pool'=>['morning'],                      'weekdays'=>[1,2,3,4,5],     'offRate'=>0.05],
            'assistant' => ['pool'=>['morning','afternoon'],          'weekdays'=>[1,2,3,4,5,6],   'offRate'=>0.15],
        ];

        // Wipe any existing shifts in the 30-day window so the backfill is deterministic
        // and we don't accumulate duplicates if this runs more than once.
        Shift::whereBetween('shift_date', [today(), today()->addDays(29)])->delete();

        foreach (User::where('is_active', true)->get() as $staffMember) {
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
                    'user_id' => $staffMember->id,
                    'shift_type' => $shiftKey,
                    'shift_date' => $date,
                    'start_time' => $start,
                    'end_time' => $end,
                    'is_active' => $d === 0,
                ]);
            }
        }
    }
}
