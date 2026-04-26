<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Patient extends Model {
    protected $fillable = ['patient_id','name','date_of_birth','phone','address','medical_history','assigned_nurse_id','assigned_doctor_id','last_visit'];
    protected $casts = ['date_of_birth'=>'date','last_visit'=>'datetime'];
    public function nurse() { return $this->belongsTo(User::class,'assigned_nurse_id'); }
    public function doctor() { return $this->belongsTo(User::class,'assigned_doctor_id'); }
    public function pinnedBy() { return $this->belongsToMany(User::class,'pinned_patients'); }
    public function scopeSearch($query,$term) {
        return $query->where(function($q)use($term){
            $q->where('name','LIKE',"%{$term}%")->orWhere('patient_id','LIKE',"%{$term}%")
              ->orWhereHas('nurse',fn($sq)=>$sq->where('name','LIKE',"%{$term}%"))
              ->orWhereHas('doctor',fn($sq)=>$sq->where('name','LIKE',"%{$term}%"));
        });
    }
}