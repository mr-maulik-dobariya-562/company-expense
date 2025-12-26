<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponseTrait;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controllers\HasMiddleware;

 class Controller  implements HasMiddleware
{
    use ApiResponseTrait ,ValidatesRequests;

    public static function middleware() {

        return [];
    }
    public function hasPermission($permission)
    {
        return auth()->user()->hasPermissionTo($permission);
    }
}
