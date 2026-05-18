<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class MedicineLocation extends Model {
    protected $fillable = ['storage_type','cabinet','shelf','level','section','notes'];

    public function medicines() { return $this->hasMany(Medicine::class, 'location_id'); }

    public function getFullLocationAttribute() {
        $type = $this->storage_type ?: 'Cabinet';
        $base = "{$type} {$this->cabinet}, Shelf {$this->shelf}, Level {$this->level}";
        return $this->section ? "{$base} ({$this->section})" : $base;
    }
}
