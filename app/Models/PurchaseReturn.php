<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class PurchaseReturn extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'product_name',
        'date',
        'party_id',
        'name_1',
        'name_2',
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
        return $this->hasMany(PurchaseReturnDetail::class);
    }

    public function billDetails()
    {
        return $this->hasMany(PurchaseReturnBillDetail::class, 'purchase_return_id');
    }

    public function getSalesGroupByItem($filterParams)
    {
        $fromDate = $filterParams['from_date'] ?? null;
        $toDate = $filterParams['to_date'] ?? null;
        $item = $filterParams['item'] ?? null;
        $party = $filterParams['party'] ?? null;

        // Generate a query to get the sales group by bill
        $query = PurchaseReturnDetail::select(
            'purchase_returns.id',
            'purchase_return_details.item_name',
            DB::raw('SUM(purchase_return_details.qty) AS qty'),
        )
            ->leftJoin('purchase_returns', 'purchase_returns.id', '=', 'purchase_return_details.purchase_return_id');

        // Add conditions based on the filter params
        if (!empty($item) && $item !== "0") {
            $query->whereIn('purchase_return_details.item_id', $item);
        }
        if (!empty($party) && $party !== "0") {
            $query->whereIn('purchase_returns.party_id', $party);
        }
        if (!empty($fromDate)) {
            $query->whereDate('purchase_returns.date', '>=', $fromDate);
        }
        if (!empty($toDate)) {
            $query->whereDate('purchase_returns.date', '<=', $toDate);
        }

        // Group by necessary fields and order by order id descending
        $query->groupBy(
            'purchase_return_details.item_id',
        )
            ->orderByDesc('purchase_returns.id');

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
        $query = PurchaseReturn::select(
            'purchase_returns.id',
            'purchase_returns.vch_No',
            'purchase_returns.purchase_id',
            'purchase_returns.name_1',
            'purchase_returns.created_at',
            'purchase_returns.type',
            'purchase_returns.ref_no',
            DB::raw("DATE_FORMAT(purchase_returns.date, '%d-%m-%Y') as date"),
            DB::raw("
                COALESCE((SELECT SUM(purchase_return_details.amount) FROM purchase_return_details WHERE purchase_return_details.purchase_return_id = purchase_returns.id), 0) +
                COALESCE((SELECT SUM(purchase_return_bill_details.amount) FROM purchase_return_bill_details WHERE purchase_return_bill_details.purchase_return_id = purchase_returns.id), 0)
                AS amount
            "),
            DB::raw("
                COALESCE((SELECT SUM(purchase_return_details.qty) FROM purchase_return_details WHERE purchase_return_details.purchase_return_id = purchase_returns.id), 0) AS qty
            ")
        )
            ->leftJoin('purchase_return_details', 'purchase_return_details.purchase_return_id', '=', 'purchase_returns.id')
            ->leftJoin('purchase_return_bill_details', 'purchase_return_bill_details.purchase_return_id', '=', 'purchase_returns.id');

        // Add conditions based on the filter params
        if (!empty($item) && $item !== "0") {
            $query->whereIn('purchase_return_details.item_id', $item);
        }
        if (!empty($party) && $party !== "0") {
            $query->whereIn('purchase_returns.party_id', $party);
        }
        if (!empty($fromDate)) {
            $query->whereDate('purchase_returns.date', '>=', $fromDate);
        }
        if (!empty($toDate)) {
            $query->whereDate('purchase_returns.date', '<=', $toDate);
        }

        // Group by necessary fields and order by order id descending
        $query->groupBy(
            'purchase_returns.id',
        )
            ->orderByDesc('purchase_returns.id');

        // Execute the query and return the result
        return $query->get()->toArray();
    }

    public function getSalesGroupByParty($filterParams)
    {
        $fromDate = $filterParams['from_date'] ?? null;
        $toDate = $filterParams['to_date'] ?? null;
        $item = $filterParams['item'] ?? null;
        $party = $filterParams['party'] ?? null;

        $query = PurchaseReturn::select(
            'partys.id as party_id',
            'partys.name as party_name',
            DB::raw("COALESCE(SUM(sd.total_amount), 0) + COALESCE(SUM(sb.total_amount), 0) AS amount"),
            DB::raw("COALESCE(SUM(sd.total_qty), 0) AS qty")
        )
            ->join('partys', 'partys.id', '=', 'purchase_returns.party_id')
            ->leftJoin(DB::raw("
            (SELECT purchase_return_id, SUM(amount) AS total_amount, SUM(qty) AS total_qty
             FROM purchase_return_details
             GROUP BY purchase_return_id) sd
        "), 'sd.purchase_return_id', '=', 'purchase_returns.id')
            ->leftJoin(DB::raw("
            (SELECT purchase_return_id, SUM(amount) AS total_amount
             FROM purchase_return_bill_details
             GROUP BY purchase_return_id) sb
        "), 'sb.purchase_return_id', '=', 'purchase_returns.id');

        // ✅ Apply Filters
        if (!empty($item) && $item !== "0") {
            $query->whereExists(function ($subQuery) use ($item) {
                $subQuery->select(DB::raw(1))
                    ->from('purchase_return_details')
                    ->whereRaw('purchase_return_details.purchase_return_id = purchase_returns.id')
                    ->whereIn('purchase_return_details.item_id', $item);
            });
        }

        if (!empty($party) && $party !== "0") {
            $query->whereIn('purchase_returns.party_id', $party);
        }
        if (!empty($fromDate)) {
            $query->whereDate('purchase_returns.date', '>=', $fromDate);
        }
        if (!empty($toDate)) {
            $query->whereDate('purchase_returns.date', '<=', $toDate);
        }

        // Group by necessary fields and order by order id descending
        $query->groupBy('purchase_returns.party_id', 'purchase_returns.name_1')
            ->orderByDesc('purchase_returns.id');

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
        $query = PurchaseReturnDetail::select(
            'purchase_returns.id',
            'purchase_returns.product_name',
            'purchase_returns.vch_No',
            'purchase_returns.date',
            'purchase_return_details.item_name',
            'purchase_returns.created_at',
            'purchase_return_details.unit_name',
            'purchase_return_details.item_tax_category',
            DB::raw("DATE_FORMAT(purchase_returns.date, '%d-%m-%Y') as date"),
            DB::raw('SUM(purchase_return_details.amount) AS amount'),
            DB::raw('SUM(purchase_return_details.price) AS price'),
            DB::raw('SUM(purchase_return_details.qty) AS qty'),
        )->leftJoin('purchase_returns', 'purchase_returns.id', '=', 'purchase_return_details.purchase_return_id');

        // Add conditions based on the filter params
        if (!empty($item) && $item !== "0") {
            $query->whereIn('purchase_return_details.item_id', $item);
        }
        if (!empty($party) && $party !== "0") {
            $query->whereIn('purchase_returns.party_id', $party);
        }
        if (!empty($fromDate)) {
            $query->whereDate('purchase_returns.date', '>=', $fromDate);
        }
        if (!empty($toDate)) {
            $query->whereDate('purchase_returns.date', '<=', $toDate);
        }

        // Group by necessary fields and order by order id descending
        $query->groupBy(
            'purchase_return_details.id',
        )
            ->orderByDesc('purchase_return_details.id');

        // Execute the query and return the result
        return $query->get()->toArray();
    }
}
