<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class SaleReturn extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'product_name',
        'date',
        'party_id',
        'name_1',
        'name_2',
        'ref_no',
        'sale_id',
        'type',
        'status',
        'buyer_id',
        'work_category_id',
        'vch_No',
        'created_by',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function details()
    {
        return $this->hasMany(SaleReturnDetail::class);
    }

    public function billDetails()
    {
        return $this->hasMany(SaleReturnBillDetail::class, 'sale_return_id');
    }

    public function sale()
    {
        return $this->hasOne(Sale::class, 'id');
    }

    public function getSalesGroupByItem($filterParams)
    {
        $fromDate = $filterParams['from_date'] ?? null;
        $toDate = $filterParams['to_date'] ?? null;
        $item = $filterParams['item'] ?? null;
        $party = $filterParams['party'] ?? null;

        // Generate a query to get the sales group by bill
        $query = SaleReturnDetail::select(
            'sale_returns.id',
            'sale_return_details.item_name',
            DB::raw('SUM(sale_return_details.qty) AS qty'),
        )
            ->leftJoin('sale_returns', 'sale_returns.id', '=', 'sale_return_details.sale_return_id');

        // Add conditions based on the filter params
        if (!empty($item) && $item !== "0") {
            $query->whereIn('sale_return_details.item_id', $item);
        }
        if (!empty($party) && $party !== "0") {
            $query->whereIn('sale_returns.party_id', $party);
        }
        if (!empty($fromDate)) {
            $query->whereDate('sale_returns.date', '>=', $fromDate);
        }
        if (!empty($toDate)) {
            $query->whereDate('sale_returns.date', '<=', $toDate);
        }

        // Group by necessary fields and order by order id descending
        $query->groupBy(
            'sale_return_details.item_id',
        )
            ->orderByDesc('sale_returns.id');

        // Execute the query and return the result
        return $query->get()->toArray();
    }

    public function getSalesGroupByBill($filterParams)
    {
        $fromDate = $filterParams['from_date'] ?? null;
        $toDate = $filterParams['to_date'] ?? null;
        $item = $filterParams['item'] ?? null;
        $party = $filterParams['party'] ?? null;

        // Generate a query to get the sales group by bill
        $query = SaleReturn::select(
            'sale_returns.id',
            'sale_returns.vch_No',
            'sale_returns.name_1',
            'sale_returns.created_at',
            'sale_returns.type',
            'sale_returns.ref_no',
            DB::raw("DATE_FORMAT(sale_returns.date, '%d-%m-%Y') as date"),
            DB::raw("
                COALESCE((SELECT SUM(sale_return_details.amount) FROM sale_return_details WHERE sale_return_details.sale_return_id = sale_returns.id), 0) +
                COALESCE((SELECT SUM(sale_return_bill_details.amount) FROM sale_return_bill_details WHERE sale_return_bill_details.sale_return_id = sale_returns.id), 0)
                AS amount
            "),
            DB::raw("
                COALESCE((SELECT SUM(sale_return_details.qty) FROM sale_return_details WHERE sale_return_details.sale_return_id = sale_returns.id), 0) AS qty
            ")
        )
            ->leftJoin('sale_return_details', 'sale_return_details.sale_return_id', '=', 'sale_returns.id')
            ->leftJoin('sale_return_bill_details', 'sale_return_bill_details.sale_return_id', '=', 'sale_returns.id');

        // Add conditions based on the filter params
        if (!empty($item) && $item !== "0") {
            $query->whereIn('sale_return_details.item_id', $item);
        }
        if (!empty($party) && $party !== "0") {
            $query->whereIn('sale_returns.party_id', $party);
        }
        if (!empty($fromDate)) {
            $query->whereDate('sale_returns.date', '>=', $fromDate);
        }
        if (!empty($toDate)) {
            $query->whereDate('sale_returns.date', '<=', $toDate);
        }

        // Group by necessary fields and order by order id descending
        $query->groupBy(
            'sale_returns.id',
        )
            ->orderByDesc('sale_returns.id');

        // Execute the query and return the result
        return $query->get()->toArray();
    }

    public function getSalesGroupByParty($filterParams)
    {
        $fromDate = $filterParams['from_date'] ?? null;
        $toDate = $filterParams['to_date'] ?? null;
        $item = $filterParams['item'] ?? null;
        $party = $filterParams['party'] ?? null;

        $query = SaleReturn::select(
            'partys.id as party_id',
            'partys.name as party_name',
            DB::raw("COALESCE(SUM(sd.total_amount), 0) + COALESCE(SUM(sb.total_amount), 0) AS amount"),
            DB::raw("COALESCE(SUM(sd.total_qty), 0) AS qty")
        )
            ->join('partys', 'partys.id', '=', 'sale_returns.party_id')
            ->leftJoin(DB::raw("
            (SELECT sale_return_id, SUM(amount) AS total_amount, SUM(qty) AS total_qty
             FROM sale_return_details
             GROUP BY sale_return_id) sd
        "), 'sd.sale_return_id', '=', 'sale_returns.id')
            ->leftJoin(DB::raw("
            (SELECT sale_return_id, SUM(amount) AS total_amount
             FROM sale_return_bill_details
             GROUP BY sale_return_id) sb
        "), 'sb.sale_return_id', '=', 'sale_returns.id');

        // ✅ Apply Filters
        if (!empty($item) && $item !== "0") {
            $query->whereExists(function ($subQuery) use ($item) {
                $subQuery->select(DB::raw(1))
                    ->from('sale_return_details')
                    ->whereRaw('sale_return_details.sale_return_id = sale_returns.id')
                    ->whereIn('sale_return_details.item_id', $item);
            });
        }

        if (!empty($party) && $party !== "0") {
            $query->whereIn('sale_returns.party_id', $party);
        }
        if (!empty($fromDate)) {
            $query->whereDate('sale_returns.date', '>=', $fromDate);
        }
        if (!empty($toDate)) {
            $query->whereDate('sale_returns.date', '<=', $toDate);
        }

        // Group by necessary fields and order by order id descending
        $query->groupBy('sale_returns.party_id', 'sale_returns.name_1')
            ->orderByDesc('sale_returns.id');

        // Execute the query and return the result
        return $query->get()->toArray();
    }

    public function getSalesGroupByVoucher($filterParams)
    {
        $fromDate = $filterParams['from_date'] ?? null;
        $toDate = $filterParams['to_date'] ?? null;
        $item = $filterParams['item'] ?? null;
        $party = $filterParams['party'] ?? null;

        // Generate a query to get the sales group by bill
        $query = SaleReturnDetail::select(
            'sale_returns.id',
            'sale_returns.product_name',
            'sale_returns.vch_No',
            'sale_returns.date',
            'sale_return_details.item_name',
            'sale_returns.created_at',
            'sale_return_details.unit_name',
            'sale_return_details.item_tax_category',
            DB::raw("DATE_FORMAT(sale_returns.date, '%d-%m-%Y') as date"),
            DB::raw('SUM(sale_return_details.amount) AS amount'),
            DB::raw('SUM(sale_return_details.price) AS price'),
            DB::raw('SUM(sale_return_details.qty) AS qty'),
        )->leftJoin('sale_returns', 'sale_returns.id', '=', 'sale_return_details.sale_return_id');

        // Add conditions based on the filter params
        if (!empty($item) && $item !== "0") {
            $query->whereIn('sale_return_details.item_id', $item);
        }
        if (!empty($party) && $party !== "0") {
            $query->whereIn('sale_returns.party_id', $party);
        }
        if (!empty($fromDate)) {
            $query->whereDate('sale_returns.date', '>=', $fromDate);
        }
        if (!empty($toDate)) {
            $query->whereDate('sale_returns.date', '<=', $toDate);
        }

        // Group by necessary fields and order by order id descending
        $query->groupBy(
            'sale_return_details.id',
        )
            ->orderByDesc('sale_return_details.id');

        // Execute the query and return the result
        return $query->get()->toArray();
    }
}
