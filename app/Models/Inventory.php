<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Inventory extends Model {
    protected $fillable = ['medicine_id','quantity','min_stock_level','expiration_date','batch_number'];
    protected $casts = ['expiration_date'=>'date'];
    public function medicine() { return $this->belongsTo(Medicine::class); }
}