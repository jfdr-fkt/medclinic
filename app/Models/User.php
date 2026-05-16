<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $fillable = [
        'name', 'email', 'password', 'role', 'phone',
        'specialization', 'last_seen_at', 'is_active',
        'status', 'avatar', 'bio', 'theme', 'font_size', 'colorblind_mode',
        'date_of_birth', 'hire_date', 'address',
        'emergency_contact_name', 'emergency_contact_phone',
        'emergency_contact_2_name', 'emergency_contact_2_phone',
        'license_number', 'must_change_password',
    ];

    protected function casts(): array
    {
        return [
            'last_seen_at'         => 'datetime',
            'date_of_birth'        => 'date',
            'hire_date'            => 'date',
            'is_active'            => 'boolean',
            'colorblind_mode'      => 'boolean',
            'must_change_password' => 'boolean',
        ];
    }

    public function avatarUrl(): string
    {
        if ($this->avatar && str_starts_with($this->avatar, 'avatars/')) {
            return asset('storage/' . $this->avatar);
        }
        return ''; // empty triggers initial-letters fallback in views
    }

    protected $hidden = ['password', 'remember_token'];

    public function shifts()         { return $this->hasMany(Shift::class); }
    public function pinnedPatients() { return $this->belongsToMany(Patient::class, 'pinned_patients'); }
    public function chatGroups()     { return $this->belongsToMany(ChatGroup::class, 'chat_group_members', 'user_id', 'group_id')->withPivot('last_read_at')->withTimestamps(); }

    public function can_($action): bool
    {
        $r = $this->role;
        return match($action) {
            // Patient records — HIPAA / PH Data Privacy Act minimum-necessary access.
            // Only roles with a direct clinical relationship can see the directory tab. Doctors
            // and nurses are further restricted in PatientController to ONLY see patients they
            // are personally assigned to. Admin + clinic_head see everything (audit-logged).
            // Pharmacist, secretary and assistant lose the Patients tab entirely.
            'patients.view'         => in_array($r, ['admin','clinic_head','doctor','nurse']),
            'patients.view_medical' => in_array($r, ['admin','clinic_head','doctor','nurse']),
            'patients.create'       => in_array($r, ['admin','clinic_head','doctor','nurse']),
            'patients.delete'       => in_array($r, ['admin','clinic_head','doctor']),
            'patients.pin_all'      => in_array($r, ['admin','clinic_head','doctor']),
            // Oversight roles see every patient; clinical roles see only their assigned ones.
            'patients.view_all'     => in_array($r, ['admin','clinic_head']),
            // Medicine management — pharmacist is the dedicated restocking role
            'medicines.create'    => in_array($r, ['admin','clinic_head','pharmacist']),
            'medicines.delete'    => in_array($r, ['admin','clinic_head']),
            'medicines.dispense'  => in_array($r, ['admin','clinic_head','doctor','pharmacist','nurse']),
            'medicines.locations' => in_array($r, ['admin','clinic_head']),
            // Staff management
            'staff.create'        => $r === 'admin',
            'staff.delete'        => $r === 'admin',
            'staff.shifts.manage' => in_array($r, ['admin','clinic_head']),
            // Audit log — admin sees who accessed which patient records.
            // Required by HIPAA / PH Data Privacy Act: "minimum necessary access" + audit trail.
            'audit.view'          => $r === 'admin',
            default => false,
        };
    }

    public function roleLabel(): string
    {
        return match($this->role) {
            'admin'       => 'Admin',
            'clinic_head' => 'Clinic Head',
            'doctor'      => 'Doctor',
            'pharmacist'  => 'Pharmacist',
            'nurse'       => 'Nurse',
            'secretary'   => 'Secretary',
            'assistant'   => 'Assistant',
            default       => ucfirst(str_replace('_', ' ', $this->role)),
        };
    }

    public function isOnline()
    {
        if ($this->status === 'offline') return false;
        return $this->last_seen_at && $this->last_seen_at->gt(now()->subMinutes(5));
    }

    public function statusLabel()
    {
        if (!$this->isOnline()) return 'Offline';
        return ucfirst($this->status ?? 'available');
    }

    public function statusColor()
    {
        if (!$this->isOnline()) return 'gray';
        return match($this->status) {
            'busy'  => 'red',
            'away'  => 'amber',
            default => 'emerald',
        };
    }

    public function currentShift()
    {
        return $this->shifts()->whereDate('shift_date', today())
            ->where('start_time', '<=', now()->format('H:i'))
            ->where('end_time',   '>=', now()->format('H:i'))->first();
    }
}
