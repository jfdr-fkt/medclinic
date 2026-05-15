<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = ['user_id', 'action', 'entity_type', 'entity_id', 'details'];

    public function user()    { return $this->belongsTo(User::class); }
    public function patient() { return $this->belongsTo(Patient::class, 'entity_id'); }
}
