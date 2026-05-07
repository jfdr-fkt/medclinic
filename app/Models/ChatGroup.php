<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatGroup extends Model
{
    protected $fillable = ['name', 'description', 'created_by'];

    public function creator()  { return $this->belongsTo(User::class, 'created_by'); }
    public function members()  { return $this->belongsToMany(User::class, 'chat_group_members', 'group_id', 'user_id')->withPivot('last_read_at')->withTimestamps(); }
    public function messages() { return $this->hasMany(Message::class, 'group_id'); }
    public function lastMessage() { return $this->hasOne(Message::class, 'group_id')->latestOfMany(); }
}
