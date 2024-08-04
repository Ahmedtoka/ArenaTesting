<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prepare extends Model {

    use HasFactory;
    protected $guarded = [];
    public $timestamps = false;

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
    public function sale()
    {
        return $this->belongsTo(Sale::class,'order_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class,'assign_to');
    }
    public function products()
    {
        return $this->hasMany(PrepareProductList::class);
    }

}
