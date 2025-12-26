<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Facades\Auth;

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
        if (Auth::user()->hasRole('Admin')) {
            $userData = User::whereDoesntHave('roles', function ($query) {
                $query->where('name', 'Admin');
            })->withSum([
                        'expenses' => function ($query) {
                            $query->where('type', 'DEBIT')
                                ->where('pay_status', '0');
                        }
                    ], 'amount')->get();
        } else {
            $userData = User::where('id', Auth::user()->id)->withSum([
                'expenses' => function ($query) {
                    $query->where('type', 'DEBIT')
                        ->where('pay_status', '0');
                }
            ], 'amount')->get();
        }
        return view('dashboard.dashboard', compact('userData'));
    }
}
