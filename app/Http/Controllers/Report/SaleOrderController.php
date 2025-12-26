<?php

namespace App\Http\Controllers\Report;

use App\Models\Item;
use App\Models\Site;
use App\Models\Type;
use App\Models\Buyer;
use App\Models\Party;
use App\Models\SaleOrder;
use App\Traits\DataTable;
use App\Models\WorkCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;

class SaleOrderController extends Controller implements HasMiddleware
{
    use DataTable;
    public static function middleware(): array
    {
        return [
            new Middleware('permission:sale-order-view', only: ['index', "getList", "saleOrderModelData"]),
        ];
    }

    protected $saleOrder;
    public function __construct(SaleOrder $saleOrder)
    {
        $this->saleOrder = $saleOrder;
        DB::statement('SET SESSION sql_mode = REPLACE(REPLACE(REPLACE(@@sql_mode, "ONLY_FULL_GROUP_BY,", ""), ",ONLY_FULL_GROUP_BY", ""), "ONLY_FULL_GROUP_BY", "")');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = Item::get();
        $partys = Party::select('id', 'name')->get();
        $buyers = Buyer::all();
        $types = Type::all();
        $workCategories = WorkCategory::all();
        $site = Site::get(['id', 'name']);
        $buyer = DB::table('buyer_singles')->get(['id', 'name']);
        return view('report.sale-order.index', compact('items', 'partys', 'buyers', 'workCategories', 'site', 'buyer', 'types'));
    }

    public function getList(Request $request)
    {
        $report = $request['group'];
        $url = $request['url'];
        $table = [];
        switch ($report) {
            case 'item':
                $data = $this->saleOrder->getSalesGroupByItem($request);
                $url = $url;
                break;
            case 'bill':
                $data = $this->saleOrder->getSalesGroupByBill($request);
                $url = $url;
                break;
            case 'party':
                $data = $this->saleOrder->getSalesGroupByParty($request);
                $url = $url;
                break;
            case 'voucher':
                $data = $this->saleOrder->getSalesGroupByVoucher($request);
                $url = $url;
                break;
        }
        return view("report.sale-order.{$report}_ajax", compact('data', 'url'));
    }

    public function saleOrderModelData(Request $request)
    {
        $sale_orders = SaleOrder::with(['details', 'billDetails'])->find($request->id);
        return view('Report::sale-order.sale_order_model', compact('sale_orders'));
    }
}
