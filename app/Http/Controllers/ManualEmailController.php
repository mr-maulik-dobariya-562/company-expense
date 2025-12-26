<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\WorkCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ManualEmailController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:manual-email-create', only: ['create']),
            new Middleware('permission:manual-email-view', only: ['index', "getList"]),
            new Middleware('permission:manual-email-edit', only: ['edit', "update"]),
            new Middleware('permission:manual-email-delete', only: ['destroy']),
        ];
    }

    public function index()
    {
        $workCategories = WorkCategory::all();
        $site = Site::get(['id', 'name']);
        $buyer = DB::table('buyer_singles')->get(['id', 'name']);
        return view('manual_email.index', compact('workCategories', 'site', 'buyer'));
    }
}
