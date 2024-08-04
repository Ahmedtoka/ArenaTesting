<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarehouseProduct extends Model
{
    use HasFactory;

    protected $guarded = [];

    public $timestamps = false;
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
}
