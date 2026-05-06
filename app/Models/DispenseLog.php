<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DispenseLog extends Model
{
    protected $fillable = ['medicine_id', 'patient_id', 'dispensed_by', 'quantity', 'notes'];

    public function medicine()    { return $this->belongsTo(Medicine::class); }
    public function patient()     { return $this->belongsTo(Patient::class); }
    public function dispensedBy() { return $this->belongsTo(User::class, 'dispensed_by'); }
}
