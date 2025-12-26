<?php

namespace App\Http\Controllers\Report;

use App\Models\Item;
use App\Models\Sale;
use App\Models\Site;
use App\Models\Type;
use App\Models\Party;
use App\Traits\DataTable;
use App\Models\WorkCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;

class MasterSaleController extends Controller implements HasMiddleware
{
    use DataTable;
    public static function middleware(): array
    {
        return [
            new Middleware('permission:master-sale-view', only: ['index', "getList"]),
        ];
    }

    protected $sale;
    public function __construct(Sale $sale)
    {
        $this->sale = $sale;
        DB::statement('SET SESSION sql_mode = REPLACE(REPLACE(REPLACE(@@sql_mode, "ONLY_FULL_GROUP_BY,", ""), ",ONLY_FULL_GROUP_BY", ""), "ONLY_FULL_GROUP_BY", "")');
    }

    public function index()
    {
        $items = Item::get();
        $types = Type::get();
        $partys = Party::select('id', 'name')->get();
        $workCategories = WorkCategory::all();
        $site = Site::get(['id', 'name']);
        $buyer = DB::table('buyer_singles')->get(['id', 'name']);
        return view('report.master-sale.index', compact('items', 'partys', 'workCategories', 'site', 'buyer', 'types'));
    }

    public function getList(Request $request)
    {
        // $report = $request['group'];
        $url = $request['url'];
        $table = [];
        $data = $this->sale->getMasterSalesGroupByBill($request);
        return view("report.master-sale.bill_ajax", compact('data', 'url'));
    }
}
