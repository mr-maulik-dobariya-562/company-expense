<?php

namespace App\Http\Controllers\Report;

use App\Models\Item;
use App\Models\Party;
use App\Models\SaleOrder;
use App\Traits\DataTable;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;

class PendingSaleOrderController extends Controller implements HasMiddleware
{
    use DataTable;
    public static function middleware(): array
    {
        return [
            new Middleware('permission:pending-sale-order-view', only: ['index', "getList"]),
        ];
    }

    protected $saleOrder;
    public function __construct(SaleOrder $saleOrder)
    {
        $this->saleOrder = $saleOrder;
        DB::statement('SET SESSION sql_mode = REPLACE(REPLACE(REPLACE(@@sql_mode, "ONLY_FULL_GROUP_BY,", ""), ",ONLY_FULL_GROUP_BY", ""), "ONLY_FULL_GROUP_BY", "")');
    }

    public function index()
    {
        $items = Item::get();
        $partys = Party::select('id', 'name')->get();
        return view('report.pending-sale-order.index', compact('items', 'partys'));
    }

    public function getList(Request $request)
    {
        $report = $request['group'];
        $url = $request['url'];
        $table = [];
        switch ($report) {
            case 'voucher':
                $data = $this->saleOrder->getPendingSalesGroupByVoucher($request);
                $url = $url;
                break;
        }
        return view("report.pending-sale-order.{$report}_ajax", compact('data', 'url'));
    }
}
