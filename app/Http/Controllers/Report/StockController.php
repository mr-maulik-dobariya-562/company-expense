<?php

namespace App\Http\Controllers\Report;

use App\Models\Item;
use App\Models\Stock;
use App\Traits\DataTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;

class StockController extends Controller implements HasMiddleware
{
    use DataTable;
    public static function middleware(): array
    {
        return [
            new Middleware('permission:stock-report-view', only: ['index', "getList"]),
        ];
    }

    protected $stock;

    public function __construct(Stock $stock)
    {
        $this->stock = $stock;
        DB::statement('SET SESSION sql_mode = REPLACE(REPLACE(REPLACE(@@sql_mode, "ONLY_FULL_GROUP_BY,", ""), ",ONLY_FULL_GROUP_BY", ""), "ONLY_FULL_GROUP_BY", "")');
    }

    public function index()
    {
        $items = Stock::select(
            'stocks.item_name',
            'stocks.id',
        )
            ->whereRaw('stocks.id = (SELECT id FROM stocks AS s2 WHERE s2.item_name = stocks.item_name ORDER BY s2.id DESC LIMIT 1)')
            ->groupBy('stocks.item_name')->get()->toArray();

        return view('report.stock.index', compact('items'));
    }

    public function getList(Request $request)
    {
        $report = $request['group'];
        $url = $request['url'];
        $table = [];

        switch ($report) {
            case 'item':
                $data = $this->stock->getSalesGroupByItem($request);
                $url = $url;
                break;
            case 'category':
                $data = $this->stock->getSalesGroupByCategory($request);
                $url = $url;
                break;
        }
        return view("report.stock.{$report}_ajax", compact('data', 'url'));
    }
}
