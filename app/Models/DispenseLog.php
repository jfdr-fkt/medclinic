<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DispenseLog extends Model
{
    protected $fillable = ['medicine_id', 'patient_id', 'dispensed_by', 'quantity', 'unit', 'quantity_in_units', 'is_return', 'notes'];
    protected $casts = ['is_return' => 'boolean'];

    public function medicine() { return $this->belongsTo(Medicine::class); }
    public function patient() { return $this->belongsTo(Patient::class); }
    public function dispensedBy() { return $this->belongsTo(User::class, 'dispensed_by'); }

    public function inputLabel(): string
    {
        $u = $this->unit ?: 'tablet';
        $plural = str_ends_with($u, 's') ? $u : $u.'s';
        return $this->quantity.' '.($this->quantity === 1 ? $u : $plural);
    }
}
