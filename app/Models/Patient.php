<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Patient extends Model {
    protected $fillable = [
        'patient_id','name','date_of_birth','sex','blood_type','height_cm','weight_kg',
        'phone','address','medical_history','allergies','chronic_conditions',
        'emergency_contact_name','emergency_contact_phone',
        'emergency_contact_2_name','emergency_contact_2_phone',
        'assigned_nurse_id','assigned_doctor_id','last_visit',
    ];
    protected $casts = [
        'date_of_birth' => 'date',
        'last_visit'    => 'datetime',
        'weight_kg'     => 'decimal:2',
    ];

    public function bmi(): ?float
    {
        if (!$this->height_cm || !$this->weight_kg) return null;
        $m = $this->height_cm / 100;
        return round((float) $this->weight_kg / ($m * $m), 1);
    }

    public function bmiCategory(): ?string
    {
        $b = $this->bmi();
        if ($b === null) return null;
        if ($b < 18.5) return 'Underweight';
        if ($b < 25)   return 'Normal';
        if ($b < 30)   return 'Overweight';
        return 'Obese';
    }
    public function nurse() { return $this->belongsTo(User::class,'assigned_nurse_id'); }
    public function doctor() { return $this->belongsTo(User::class,'assigned_doctor_id'); }
    public function pinnedBy() { return $this->belongsToMany(User::class,'pinned_patients'); }
    public function visits() { return $this->hasMany(Visit::class)->orderByDesc('checked_in_at'); }
    public function images() { return $this->hasMany(PatientImage::class)->latest(); }
    public function isNewPatient(): bool { return $this->visits()->count() <= 1; }
    public function scopeSearch($query,$term) {
        return $query->where(function($q)use($term){
            $q->where('name','LIKE',"%{$term}%")->orWhere('patient_id','LIKE',"%{$term}%")
              ->orWhereHas('nurse',fn($sq)=>$sq->where('name','LIKE',"%{$term}%"))
              ->orWhereHas('doctor',fn($sq)=>$sq->where('name','LIKE',"%{$term}%"));
        });
    }
}