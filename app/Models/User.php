<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $fillable = [
        'name', 'email', 'password', 'role', 'phone',
        'specialization', 'last_seen_at', 'is_active',
        'status', 'avatar', 'bio',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'last_seen_at' => 'datetime',
        'is_active'    => 'boolean',
    ];

    public function shifts()         { return $this->hasMany(Shift::class); }
    public function pinnedPatients() { return $this->belongsToMany(Patient::class, 'pinned_patients'); }

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
