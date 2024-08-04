<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResyncedOrder extends Model {

    use HasFactory;
    protected $guarded = [];
    public $timestamps = false;
    public function editor()
    {
        return $this->belongsTo(User::class,'synced_by');
    }
    public function assignee()
    {
        return $this->belongsTo(User::class,'assign_to');
    }

}
