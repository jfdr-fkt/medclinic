<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Medicine extends Model {
    protected $fillable = ['name','generic_name','brand_names','barcode','qr_code','location_id','type','description','dosage','dosage_form','image_path','form_other_note'];
    public function location() { return $this->belongsTo(MedicineLocation::class); }
    public function inventories() { return $this->hasMany(Inventory::class); }
    public function latestInventory() { return $this->hasOne(Inventory::class)->latestOfMany(); }
    public function totalStock() { return $this->inventories()->sum('quantity'); }

    public function imageUrl(): ?string
    {
        if (!$this->image_path) return null;
        return asset('storage/' . ltrim($this->image_path, '/'));
    }
}