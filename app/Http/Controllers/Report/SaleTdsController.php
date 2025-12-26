<?php

namespace App\Http\Controllers\Report;

use App\Models\Party;
use App\Models\Sale;
use App\Traits\DataTable;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Http\Request;

class SaleTdsController extends Controller implements HasMiddleware
{
    use DataTable;
    public static function middleware(): array
    {
        return [
            new Middleware('permission:sale-tds-view', only: ['index', "getList"]),
        ];
    }
    protected $saleTds;
    public function __construct(Sale $saleTds)
    {
        $this->saleTds = $saleTds;
        DB::statement('SET SESSION sql_mode = REPLACE(REPLACE(REPLACE(@@sql_mode, "ONLY_FULL_GROUP_BY,", ""), ",ONLY_FULL_GROUP_BY", ""), "ONLY_FULL_GROUP_BY", "")');
    }
    public function index()
    {
        $partys = Party::select('id', 'name')->get();
        return view('report.sale-tds.index', compact('partys'));
    }

    public function getList(Request $request)
    {
        // $report = $request['group'];
        $url = $request['url'];
        $table = [];
        $data = $this->saleTds->getSalesTdsGroupByBill($request);
        $url = $url;

        return view("report.sale-tds.bill_ajax", compact('data', 'url'));
    }

    public function saleTdsModelData(Request $request)
    {
        $sales_tds = Sale::with(['details', 'billDetails'])->find($request->id);
        return view('Report::sale-tds.sale_tds_model', compact('sales_tds'));
    }
}
