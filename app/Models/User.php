<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $fillable = [
        'name', 'email', 'password', 'role', 'phone',
        'specialization', 'last_seen_at', 'is_active',
        'status', 'avatar', 'bio', 'theme', 'font_size', 'colorblind_mode',
    ];

    protected function casts(): array
    {
        return [
            'last_seen_at'    => 'datetime',
            'is_active'       => 'boolean',
            'colorblind_mode' => 'boolean',
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
            'patients.create'  => in_array($r, ['admin','doctor','nurse']),
            'patients.delete'  => in_array($r, ['admin','doctor']),
            'patients.pin_all' => in_array($r, ['admin','doctor']),
            'medicines.create' => $r === 'admin',
            'medicines.delete' => $r === 'admin',
            'medicines.dispense'   => true,
            'medicines.locations'  => $r === 'admin',
            'staff.create'         => $r === 'admin',
            'staff.shifts.manage'  => $r === 'admin',
            default => false,
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
