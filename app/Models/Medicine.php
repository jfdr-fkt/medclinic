<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Medicine extends Model {
    protected $fillable = [
        'name','generic_name','brand_names','barcode','qr_code','location_id','type',
        'description','dosage','dosage_form','image_path','form_other_note',
        'indications','dosage_instructions','side_effects','warnings','storage_instructions',
        'gallery_paths','units_per_blister','blisters_per_pack','unit_label',
        'archived_at','archived_by','archive_reason','archive_location_type',
    ];

    protected $casts = [
        'archived_at' => 'datetime',
        'gallery_paths' => 'array',
        'units_per_blister' => 'integer',
        'blisters_per_pack' => 'integer',
    ];

    public function unitsPerBlister(): int { return max(1, (int) ($this->units_per_blister ?: 10)); }
    public function blistersPerPack(): int { return max(1, (int) ($this->blisters_per_pack ?: 10)); }
    public function unitsPerPack(): int { return $this->unitsPerBlister() * $this->blistersPerPack(); }
    public function unitLabel(): string { return $this->unit_label ?: 'tablet'; }
    public function unitLabelPlural(): string
    {
        $u = $this->unitLabel();
        return str_ends_with($u, 's') ? $u : $u.'s';
    }

    public function unitsFromInput(int $quantity, string $unit): int
    {
        return match ($unit) {
            'pack' => $quantity * $this->unitsPerPack(),
            'blister' => $quantity * $this->unitsPerBlister(),
            default => $quantity,
        };
    }

    public function breakdown(int $units): array
    {
        $perBlister = $this->unitsPerBlister();
        $perPack = $this->unitsPerPack();
        $packs = intdiv($units, $perPack);
        $rem = $units - $packs * $perPack;
        $blisters = intdiv($rem, $perBlister);
        $loose = $rem - $blisters * $perBlister;
        return ['packs' => $packs, 'blisters' => $blisters, 'loose' => $loose];
    }

    public function breakdownLabel(int $units): string
    {
        $b = $this->breakdown($units);
        $parts = [];
        if ($b['packs'] > 0) $parts[] = $b['packs'].' pack'.($b['packs']===1?'':'s');
        if ($b['blisters'] > 0) $parts[] = $b['blisters'].' blister'.($b['blisters']===1?'':'s');
        if ($b['loose'] > 0 || empty($parts)) $parts[] = $b['loose'].' '.($b['loose']===1?$this->unitLabel():$this->unitLabelPlural());
        return implode(', ', $parts);
    }

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
