<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Shift extends Model {
    protected $fillable = ['user_id','shift_type','shift_date','start_time','end_time','is_active'];
    protected $casts = ['shift_date'=>'date','is_active'=>'boolean'];
    public function user() { return $this->belongsTo(User::class); }
}