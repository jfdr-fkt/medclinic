<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserNotification extends Model
{
    protected $table = 'user_notifications';
    protected $fillable = ['user_id', 'type', 'title', 'body', 'url', 'icon', 'color', 'read_at'];
    protected $casts = ['read_at' => 'datetime'];

    public function user() { return $this->belongsTo(User::class); }
    public function isRead(): bool { return $this->read_at !== null; }

    public static function notify(int $userId, string $type, string $title, ?string $body = null, ?string $url = null, ?string $icon = null, ?string $color = null): self
    {
        return self::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'url' => $url,
            'icon' => $icon ?? 'fa-bell',
            'color' => $color ?? 'brand',
        ]);
    }
}
