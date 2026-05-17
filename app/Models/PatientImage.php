<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientImage extends Model
{
    protected $fillable = ['patient_id', 'uploaded_by', 'path', 'caption'];

    public function patient()    { return $this->belongsTo(Patient::class); }
    public function uploadedBy() { return $this->belongsTo(User::class, 'uploaded_by'); }

    public function url(): string
    {
        return asset('storage/' . ltrim($this->path, '/'));
    }
}
