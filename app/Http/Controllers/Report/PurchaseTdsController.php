<?php

namespace App\Http\Controllers\Report;

use App\Models\Party;
use App\Models\Purchase;
use App\Traits\DataTable;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Http\Request;

class PurchaseTdsController extends Controller implements HasMiddleware
{
    use DataTable;
    public static function middleware(): array
    {
        return [
            new Middleware('permission:purchase-tds-view', only: ['index', "getList"]),
        ];
    }
    protected $purchaseTds;
    public function __construct(Purchase $purchaseTds)
    {
        $this->purchaseTds = $purchaseTds;
        DB::statement('SET SESSION sql_mode = REPLACE(REPLACE(REPLACE(@@sql_mode, "ONLY_FULL_GROUP_BY,", ""), ",ONLY_FULL_GROUP_BY", ""), "ONLY_FULL_GROUP_BY", "")');
    }
    public function index()
    {
        $partys = Party::select('id', 'name')->get();
        return view('report.purchase-tds.index', compact('partys'));
    }

    public function getList(Request $request)
    {
        // $report = $request['group'];
        $url = $request['url'];
        $table = [];
        $data = $this->purchaseTds->getPurchaseTdsGroupByBill($request);
        $url = $url;

        return view("report.purchase-tds.bill_ajax", compact('data', 'url'));
    }

    public function purchaseTdsModelData(Request $request)
    {
        $purchase_tds = Purchase::with(['details', 'billDetails'])->find($request->id);
        return view('Report::purchase-tds.purchase_tds_model', compact('purchase_tds'));
    }
}
