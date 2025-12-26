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

class SaleController extends Controller implements HasMiddleware
{
    use DataTable;
    public static function middleware(): array
    {
        return [
            new Middleware('permission:sale-view', only: ['index', "getList", "saleModelData"]),
        ];
    }

    protected $sale;
    public function __construct(Sale $sale)
    {
        $this->sale = $sale;
        DB::statement('SET SESSION sql_mode = REPLACE(REPLACE(REPLACE(@@sql_mode, "ONLY_FULL_GROUP_BY,", ""), ",ONLY_FULL_GROUP_BY", ""), "ONLY_FULL_GROUP_BY", "")');
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = Item::get();
        $types = Type::get();
        $partys = Party::select('id', 'name')->get();
        $workCategories = WorkCategory::all();
        $site = Site::get(['id', 'name']);
        $buyer = DB::table('buyer_singles')->get(['id', 'name']);
        return view('report.sale.index', compact('items', 'partys', 'workCategories', 'site', 'buyer', 'types'));
    }

    public function getList(Request $request)
    {
        $report = $request['group'];
        $url = $request['url'];
        $table = [];
        switch ($report) {
            case 'item':
                $data = $this->sale->getSalesGroupByItem($request);
                $url = $url;
                break;
            case 'bill':
                $data = $this->sale->getSalesGroupByBill($request);
                $url = $url;
                break;
            case 'party':
                $data = $this->sale->getSalesGroupByParty($request);
                $url = $url;
                break;
            case 'voucher':
                $data = $this->sale->getSalesGroupByVoucher($request);
                $url = $url;
                break;
        }
        return view("report.sale.{$report}_ajax", compact('data', 'url'));
    }

    public function saleModelData(Request $request)
    {
        $sales = Sale::with(['details', 'billDetails', 'saleReturns'])->find($request->id);
        $action = $request->action;
        // echo '<pre>';
        // print_r($sales->saleReturn->toArray());
        // echo '</pre>';
        // exit;

        return view('Report::sale.sale_model', compact('sales', 'action'));
    }
}
