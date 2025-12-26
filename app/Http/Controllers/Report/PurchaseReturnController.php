<?php

namespace App\Http\Controllers\Report;

use App\Models\Item;
use App\Models\Party;
use App\Traits\DataTable;
use Illuminate\Http\Request;
use App\Models\PurchaseReturn;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Purchase;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;

class PurchaseReturnController extends Controller implements HasMiddleware
{
    use DataTable;

    public static function middleware(): array
    {
        return [
            new Middleware('permission:purchase-return-view', only: ['index', "getList", "purchaseReturnModelData"]),
            new Middleware('permission:purchase-return-delete', only: ['purchaseReturnDelete']),
        ];
    }

    protected $purchaseReturn;

    public function __construct(PurchaseReturn $purchaseReturn)
    {
        $this->purchaseReturn = $purchaseReturn;
        DB::statement('SET SESSION sql_mode = REPLACE(REPLACE(REPLACE(@@sql_mode, "ONLY_FULL_GROUP_BY,", ""), ",ONLY_FULL_GROUP_BY", ""), "ONLY_FULL_GROUP_BY", "")');
    }

    public function index()
    {
        $items = Item::get();
        $partys = Party::select('id', 'name')->get();
        return view('report.purchase-return.index', compact('items', 'partys'));
    }

    public function getList(Request $request)
    {
        $report = $request['group'];
        $url = $request['url'];
        $table = [];
        switch ($report) {
            case 'item':
                $data = $this->purchaseReturn->getSalesGroupByItem($request);
                $url = $url;
                break;
            case 'bill':
                $data = $this->purchaseReturn->getSalesGroupByBill($request);
                $url = $url;
                break;
            case 'party':
                $data = $this->purchaseReturn->getSalesGroupByParty($request);
                $url = $url;
                break;
            case 'voucher':
                $data = $this->purchaseReturn->getSalesGroupByVoucher($request);
                $url = $url;
                break;
        }
        return view("report.purchase-return.{$report}_ajax", compact('data', 'url'));
    }

    public function purchaseReturnModelData(Request $request)
    {
        $action = $request->action;
        $purchase_returns = PurchaseReturn::with(['details', 'billDetails'])->find($request->id);
        return view('Report::purchase-return.purchase_return_model', compact('purchase_returns', 'action'));
    }

    public function purchaseReturnDelete(Request $request)
    {
        if ($request->id > 0) {
            // $purchase_returns = PurchaseReturn::with('details', 'billDetails')->find($request->id);
            // $purchase_returns->forceDelete();
            DB::transaction(function () use ($request) {
                DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                $purchaseReturn = PurchaseReturn::with('details', 'billDetails')->findOrFail($request->id);

                // Delete related details records
                $purchaseReturn->details()->forceDelete();

                // Delete related bill details records
                $purchaseReturn->billDetails()->forceDelete();

                // Delete the purchase return record
                $purchaseReturn->forceDelete();
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            });
        }
        return $this->withSuccess("Data Deleted successfully");
    }
}
