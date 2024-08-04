<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryDetail extends Model {

    use HasFactory;
    protected $guarded = [];
    public $timestamps = false;

    public function inventory()
    {
        return $this->belongsTo(InventoryTransfer::class,'transfer_id');
    }

}
