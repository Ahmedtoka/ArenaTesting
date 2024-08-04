<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\View;
use App\Models\Store;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Models\Warehouse;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    public function __construct()
    {
        $user = Auth::user();
        $store = $user?$user->getShopifyStore:Store::first();
        $this->store = $store;
        View::share('store',$store);
        $locations = Warehouse::all();
        View::share('locations',$locations);
    }
}
