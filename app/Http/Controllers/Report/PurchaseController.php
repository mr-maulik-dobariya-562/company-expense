<?php

namespace App\Http\Controllers\Report;

use App\Models\Item;
use App\Models\Type;
use App\Models\Party;
use App\Models\Purchase;
use App\Traits\DataTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;

class PurchaseController extends Controller implements HasMiddleware
{
    use DataTable;
    public static function middleware(): array
    {
        return [
            new Middleware('permission:purchase-view', only: ['index', "getList", "purchaseModelData"]),
        ];
    }

    protected $purchase;

    public function __construct(Purchase $purchase)
    {
        $this->purchase = $purchase;
        DB::statement('SET SESSION sql_mode = REPLACE(REPLACE(REPLACE(@@sql_mode, "ONLY_FULL_GROUP_BY,", ""), ",ONLY_FULL_GROUP_BY", ""), "ONLY_FULL_GROUP_BY", "")');
    }

    public function index()
    {
        $items = Item::get();
        $types = Type::get();
        $partys = Party::select('id', 'name')->get();
        return view('report.purchase.index', compact('items', 'partys', 'types'));
    }

    public function getList(Request $request)
    {
        $report = $request['group'];
        $url = $request['url'];
        $table = [];
        switch ($report) {
            case 'item':
                $data = $this->purchase->getSalesGroupByItem($request);
                $url = $url;
                break;
            case 'bill':
                $data = $this->purchase->getSalesGroupByBill($request);
                $url = $url;
                break;
            case 'party':
                $data = $this->purchase->getSalesGroupByParty($request);
                $url = $url;
                break;
            case 'voucher':
                $data = $this->purchase->getSalesGroupByVoucher($request);
                $url = $url;
                break;
        }
        return view("report.purchase.{$report}_ajax", compact('data', 'url'));
    }

    public function purchaseModelData(Request $request)
    {
        $purchases = Purchase::with(['details', 'billDetails', 'purchaseReturns'])->find($request->id);
        $action = $request->action;
        return view('Report::purchase.purchase_model', compact('purchases', 'action'));
    }
}
