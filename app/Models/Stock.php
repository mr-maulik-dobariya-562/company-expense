<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Stock extends Model
{
    use SoftDeletes, HasFactory;

    protected $table = 'stocks';

    protected $fillable = [
        'date',
        'item_name',
        'qty',
        'unit_name',
        'price',
        'amount',
        'created_by'
    ];

    public function getSalesGroupByItem($filterParams)
    {
        $item = $filterParams['item'] ?? null;
        $query = StockDetail::select(
            'stock_details.date',
            'stock_details.qty',
            'stock_details.price',
            'stock_details.item_name',
            'stock_details.amount',
            'stock_details.unit_name'
        )
            ->whereRaw('stock_details.id = (SELECT id FROM stock_details AS s2 WHERE s2.item_name = stock_details.item_name ORDER BY s2.id DESC LIMIT 1)')
            ->groupBy('stock_details.item_name');

        if (!empty($item) && $item !== "0") {
            $query->whereIn('stock_details.stock_id', $item);
        }

        return $query->get()->toArray();

    }

    public function getSalesGroupByCategory($filterParams)
    {
        $item = $filterParams['item'] ?? null;
        $query = Stock::select(
            'stocks.date',
            'stocks.item_name',
            'stocks.qty',
            'stocks.amount'
        )
            ->where('stocks.item_name', '!=', 'Totals')
            ->whereRaw('stocks.id = (SELECT id FROM stocks AS s2 WHERE s2.item_name = stocks.item_name ORDER BY s2.id DESC LIMIT 1)')
            ->groupBy('stocks.item_name');

        if (!empty($item) && $item !== "0") {
            $query->whereIn('stocks.id', $item);
        }

        return $query->get()->toArray();
    }

}
