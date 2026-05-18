<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Visit extends Model
{
    protected $fillable = [
        'patient_id', 'recorded_by', 'current_staff_id',
        'checked_in_at', 'checked_out_at', 'status', 'visit_type', 'reason', 'notes',
    ];

    public function typeLabel(): string
    {
        return $this->visit_type === 'appointment' ? 'Appointment' : 'Walk-in';
    }

    protected $casts = [
        'checked_in_at' => 'datetime',
        'checked_out_at' => 'datetime',
    ];

    public function patient()      { return $this->belongsTo(Patient::class); }
    public function recordedBy()   { return $this->belongsTo(User::class, 'recorded_by'); }
    public function currentStaff() { return $this->belongsTo(User::class, 'current_staff_id'); }

    public function isActive(): bool
    {
        return !in_array($this->status, ['completed', 'cancelled']);
    }

    public function statusLabel(): string
    {
        return match($this->status) {
            'waiting' => 'Waiting',
            'with_nurse' => 'With Nurse',
            'with_doctor' => 'With Doctor',
            'pharmacy' => 'Pharmacy',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            default => ucfirst($this->status),
        };
    }

    public function statusColor(): string
    {
        return match($this->status) {
            'waiting' => 'amber',
            'with_nurse' => 'cyan',
            'with_doctor' => 'blue',
            'pharmacy' => 'purple',
            'completed' => 'emerald',
            'cancelled' => 'gray',
            default => 'slate',
        };
    }
}
