<?php

namespace App\Http\Controllers\Report;

use App\Models\Item;
use App\Models\Party;
use App\Traits\DataTable;
use App\Models\SaleReturn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Sale;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;

class SaleReturnController extends Controller implements HasMiddleware
{
    use DataTable;

    public static function middleware(): array
    {
        return [
            new Middleware('permission:sale-return-view', only: ['index', "getList", 'saleReturnModelData']),
            new Middleware('permission:sale-return-delete', only: ['saleReturnDelete']),
        ];
    }

    protected $saleReturn;

    public function __construct(SaleReturn $saleReturn)
    {
        $this->saleReturn = $saleReturn;
        DB::statement('SET SESSION sql_mode = REPLACE(REPLACE(REPLACE(@@sql_mode, "ONLY_FULL_GROUP_BY,", ""), ",ONLY_FULL_GROUP_BY", ""), "ONLY_FULL_GROUP_BY", "")');
    }

    public function index()
    {
        $items = Item::get();
        $partys = Party::select('id', 'name')->get();
        return view('report.sale-return.index', compact('items', 'partys'));
    }

    public function getList(Request $request)
    {
        $report = $request['group'];
        $url = $request['url'];
        $table = [];
        switch ($report) {
            case 'item':
                $data = $this->saleReturn->getSalesGroupByItem($request);
                $url = $url;
                break;
            case 'bill':
                $data = $this->saleReturn->getSalesGroupByBill($request);
                $url = $url;
                break;
            case 'party':
                $data = $this->saleReturn->getSalesGroupByParty($request);
                $url = $url;
                break;
            case 'voucher':
                $data = $this->saleReturn->getSalesGroupByVoucher($request);
                $url = $url;
                break;
        }
        return view("report.sale-return.{$report}_ajax", compact('data', 'url'));
    }

    public function saleReturnModelData(Request $request)
    {
        $action = $request->action;
        $sale_returns = SaleReturn::with(['details', 'billDetails'])->find($request->id);
        return view('Report::sale-return.sale_return_model', compact('sale_returns', 'action'));
    }

    public function saleReturnDelete(Request $request)
    {
        if ($request->id > 0) {
            // $sale_returns = SaleReturn::with('details', 'billDetails')->find($request->id);
            // $sale_returns->forceDelete();

            DB::transaction(function () use ($request) {
                DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                $saleReturn = SaleReturn::with('details', 'billDetails')->findOrFail($request->id);

                // Delete related details records
                $saleReturn->details()->forceDelete();

                // Delete related bill details records
                $saleReturn->billDetails()->forceDelete();

                // Delete the sale return record
                $saleReturn->forceDelete();
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            });
        }
        return $this->withSuccess("Data Deleted successfully");
    }

}
