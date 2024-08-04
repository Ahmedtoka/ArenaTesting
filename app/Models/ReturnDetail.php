<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturnDetail extends Model {

    use HasFactory;
    protected $guarded = [];
    public $timestamps = false;
    public function return()
    {
        return $this->belongsTo(ReturnedOrder::class,'return_id');
    }

}
