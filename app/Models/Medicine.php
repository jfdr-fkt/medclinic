<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Medicine extends Model {
    protected $fillable = [
        'name','generic_name','brand_names','barcode','qr_code','location_id','type',
        'description','dosage','dosage_form','image_path','form_other_note',
        'indications','dosage_instructions','side_effects','warnings','storage_instructions',
        'gallery_paths',
        'archived_at','archived_by','archive_reason','archive_location_type',
    ];

    protected $casts = [
        'archived_at'   => 'datetime',
        'gallery_paths' => 'array',
    ];

    /**
     * Up to 5 extra image paths shown in the gallery on the show page.
     * Returns an array of absolute URLs.
     */
    public function galleryUrls(): array
    {
        if (!is_array($this->gallery_paths)) return [];
        return array_map(fn($p) => asset('storage/' . ltrim($p, '/')), array_slice($this->gallery_paths, 0, 5));
    }

    public function location() { return $this->belongsTo(MedicineLocation::class); }
    public function inventories() { return $this->hasMany(Inventory::class); }
    public function latestInventory() { return $this->hasOne(Inventory::class)->latestOfMany(); }
    public function archivedBy() { return $this->belongsTo(User::class, 'archived_by'); }
    public function totalStock() { return $this->inventories()->sum('quantity'); }

    public function isExpired(): bool
    {
        $exp = $this->latestInventory?->expiration_date;
        return $exp ? \Carbon\Carbon::parse($exp)->lt(now()->startOfDay()) : false;
    }
    public function isArchivedManually(): bool
    {
        return $this->archived_at !== null;
    }

    public function imageUrl(): ?string
    {
        if (!$this->image_path) return null;
        return asset('storage/' . ltrim($this->image_path, '/'));
    }
}
