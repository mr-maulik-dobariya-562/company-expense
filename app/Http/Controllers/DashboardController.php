<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;

class DashboardController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:dashboard-create', only: ['create']),
            new Middleware('permission:dashboard-view', only: ['index', "getView"]),
            new Middleware('permission:dashboard-edit', only: ['edit', "update"]),
        ];
    }

    public function __construct()
    {
        DB::statement("SET SESSION sql_mode=(SELECT REPLACE(@@sql_mode, 'ONLY_FULL_GROUP_BY', ''));");
    }

    public function index()
    {
        return view('dashboard.dashboard');
    }
}
