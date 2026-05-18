<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CalendarEvent extends Model
{
    protected $fillable = ['user_id', 'created_by', 'event_date', 'title', 'description', 'color'];
    protected $casts = ['event_date' => 'date'];

    public function user() { return $this->belongsTo(User::class); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }

    public function isGlobal(): bool { return $this->user_id === null; }

    public function colorClasses(): string
    {
        return match($this->color) {
            'holiday' => 'bg-rose-100 text-rose-700 border-rose-300 dark:bg-rose-900/30 dark:text-rose-200 dark:border-rose-800/60',
            'important' => 'bg-red-100 text-red-700 border-red-300 dark:bg-red-900/30 dark:text-red-200 dark:border-red-800/60',
            'festive' => 'bg-fuchsia-100 text-fuchsia-700 border-fuchsia-300 dark:bg-fuchsia-900/30 dark:text-fuchsia-200 dark:border-fuchsia-800/60',
            'note' => 'bg-sky-100 text-sky-700 border-sky-300 dark:bg-sky-900/30 dark:text-sky-200 dark:border-sky-800/60',
            default => 'bg-emerald-100 text-emerald-700 border-emerald-300 dark:bg-emerald-900/30 dark:text-emerald-200 dark:border-emerald-800/60',
        };
    }

    public function dotColor(): string
    {
        return match($this->color) {
            'holiday' => 'bg-rose-500',
            'important' => 'bg-red-500',
            'festive' => 'bg-fuchsia-500',
            'note' => 'bg-sky-500',
            default => 'bg-emerald-500',
        };
    }
}
