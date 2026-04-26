<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class MedicineLocation extends Model {
    protected $fillable = ['cabinet','shelf','level','section','notes'];
    public function medicines() { return $this->hasMany(Medicine::class); }
    public function getFullLocationAttribute() {
        return "Cabinet {$this->cabinet}, Shelf {$this->shelf}, Level {$this->level}".($this->section?" ({$this->section})":"");
    }
}