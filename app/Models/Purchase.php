<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Purchase extends Model
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
        'mtc_no',
        'mtc_document',
        'credited_days',
        'new_type',
        'created_by',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function details()
    {
        return $this->hasMany(PurchaseDetail::class);
    }

    public function billDetails()
    {
        return $this->hasMany(PurchaseBillDetail::class);
    }

    public function purchaseReturns()
    {
        return $this->hasMany(PurchaseReturn::class, 'purchase_id');
    }

    public function getSalesGroupByItem($filterParams)
    {
        $fromDate = $filterParams['from_date'] ?? null;
        $toDate = $filterParams['to_date'] ?? null;
        $item = $filterParams['item'] ?? null;
        $party = $filterParams['party'] ?? null;
        $newType = $filterParams['new_type'] ?? null;

        $subBillDetails = DB::table('purchase_bill_details')
            ->select('purchase_id', DB::raw('SUM(amount) as bill_amount'))
            ->groupBy('purchase_id');

        $query = PurchaseDetail::select(
            'purchases.id',
            'purchases.vch_No',
            'purchases.date',
            'purchases.name_1',
            'purchases.created_at',
            'purchases.type',
            'purchase_details.item_name',
            'purchase_details.item_id',
            DB::raw("DATE_FORMAT(purchases.date, '%d-%m-%Y') as date"),
            DB::raw('SUM(purchase_details.amount) AS amount'),
            DB::raw('SUM(purchase_details.price) AS price'),
            DB::raw('SUM(purchase_details.qty) AS qty')
        )
            ->leftJoin('purchases', 'purchases.id', '=', 'purchase_details.purchase_id')
            ->leftJoinSub($subBillDetails, 'bd', function ($join) {
                $join->on('purchases.id', '=', 'bd.purchase_id');
            });

        if (!empty($item) && $item !== "0") {
            $query->whereIn('purchase_details.item_id', $item);
        }
        if (!empty($party) && $party !== "0") {
            $query->whereIn('purchases.party_id', $party);
        }
        if (!empty($newType) && $newType !== null) {
            $query->whereIn('purchases.new_type', $newType);
        }
        if (!empty($fromDate)) {
            $query->whereDate('purchases.date', '>=', $fromDate);
        }
        if (!empty($toDate)) {
            $query->whereDate('purchases.date', '<=', $toDate);
        }

        $query->groupBy(
            'purchase_details.item_id',
            'purchases.id',
            'purchases.vch_No',
            'purchases.date',
            'purchases.name_1',
            'purchases.created_at',
            'purchases.type',
            'purchase_details.item_name'
        )->orderByDesc('purchases.id');

        return $query->get()->toArray();
    }

    public function getSalesGroupByBill($filterParams)
    {
        $fromDate = $filterParams['from_date'] ?? null;
        $toDate = $filterParams['to_date'] ?? null;
        $item = $filterParams['item'] ?? null;
        $party = $filterParams['party'] ?? null;
        $newType = $filterParams['new_type'] ?? null;

        // Generate a query to get the sales group by bill
        $query = Purchase::select(
            'purchases.id',
            'purchases.vch_No',
            'purchases.name_1',
            'purchases.created_at',
            'purchases.type',
            DB::raw("DATE_FORMAT(purchases.date, '%d-%m-%Y') as date"),
            DB::raw("
                COALESCE((SELECT SUM(purchase_details.amount) FROM purchase_details WHERE purchase_details.purchase_id = purchases.id), 0) +
                COALESCE((SELECT SUM(purchase_bill_details.amount) FROM purchase_bill_details WHERE purchase_bill_details.purchase_id = purchases.id), 0)
                AS amount
            "),
            DB::raw("
                COALESCE((SELECT SUM(purchase_bill_details.amount) FROM purchase_bill_details WHERE purchase_bill_details.purchase_id = purchases.id AND purchase_bill_details.bs_name IN ('IGST', 'CGST', 'SGST')), 0)
                AS gstTotal
            "),
            DB::raw("
                COALESCE((SELECT SUM(purchase_details.qty) FROM purchase_details WHERE purchase_details.purchase_id = purchases.id), 0) AS qty
            ")
        )
            ->leftJoin('purchase_details', 'purchase_details.purchase_id', '=', 'purchases.id')
            ->leftJoin('purchase_bill_details', 'purchase_bill_details.purchase_id', '=', 'purchases.id');

        // Add conditions based on the filter params
        if (!empty($item) && $item !== "0") {
            $query->whereIn('purchase_details.item_id', $item);
        }
        if (!empty($party) && $party !== "0") {
            $query->whereIn('purchases.party_id', $party);
        }
        if (!empty($newType) && $newType !== null) {
            $query->whereIn('purchases.new_type', $newType);
        }
        if (!empty($fromDate)) {
            $query->whereDate('purchases.date', '>=', $fromDate);
        }
        if (!empty($toDate)) {
            $query->whereDate('purchases.date', '<=', $toDate);
        }

        // Group by necessary fields and order by order id descending
        $query->groupBy(
            'purchases.id',
        )
            ->orderByDesc('purchases.id');

        // Execute the query and return the result
        return $query->get()->toArray();
    }

    public function getSalesGroupByParty($filterParams)
    {
        $fromDate = $filterParams['from_date'] ?? null;
        $toDate = $filterParams['to_date'] ?? null;
        $item = $filterParams['item'] ?? null;
        $party = $filterParams['party'] ?? null;
        $newType = $filterParams['new_type'] ?? null;

        // Generate a query to get the sales group by bill
        $query = Purchase::select(
            'partys.id as party_id',
            'partys.name as party_name',
            DB::raw("COALESCE(SUM(sd.total_amount), 0) + COALESCE(SUM(sb.total_amount), 0) AS amount"),
            DB::raw("COALESCE(SUM(sd.total_qty), 0) AS qty")
        )
            ->join('partys', 'partys.id', '=', 'purchases.party_id')
            ->leftJoin(DB::raw("
            (SELECT purchase_id, SUM(amount) AS total_amount, SUM(qty) AS total_qty
             FROM purchase_details
             GROUP BY purchase_id) sd
        "), 'sd.purchase_id', '=', 'purchases.id')
            ->leftJoin(DB::raw("
            (SELECT purchase_id, SUM(amount) AS total_amount
             FROM purchase_bill_details
             GROUP BY purchase_id) sb
        "), 'sb.purchase_id', '=', 'purchases.id');

        // ✅ Apply Filters
        if (!empty($item) && $item !== "0") {
            $query->whereExists(function ($subQuery) use ($item) {
                $subQuery->select(DB::raw(1))
                    ->from('purchase_details')
                    ->whereRaw('purchase_details.purchase_id = purchases.id')
                    ->whereIn('purchase_details.item_id', $item);
            });
        }
        if (!empty($party) && $party !== "0") {
            $query->whereIn('purchases.party_id', $party);
        }
        if (!empty($newType) && $newType !== null) {
            $query->whereIn('purchases.new_type', $newType);
        }
        if (!empty($fromDate)) {
            $query->whereDate('purchases.date', '>=', $fromDate);
        }
        if (!empty($toDate)) {
            $query->whereDate('purchases.date', '<=', $toDate);
        }

        // Group by necessary fields and order by order id descending
        $query->groupBy('purchases.party_id')
            ->orderByDesc('purchases.id');

        // Execute the query and return the result
        return $query->get()->toArray();
    }

    public function getSalesGroupByVoucher($filterParams)
    {
        $fromDate = $filterParams['from_date'] ?? null;
        $toDate = $filterParams['to_date'] ?? null;
        $item = $filterParams['item'] ?? null;
        $party = $filterParams['party'] ?? null;
        $newType = $filterParams['new_type'] ?? null;

        // Generate a query to get the sales group by bill
        $query = PurchaseDetail::select(
            'purchases.id',
            'purchases.vch_No',
            'purchases.date',
            'purchase_details.item_name',
            'purchases.created_at',
            'purchase_details.qty',
            'purchase_details.price',
            'purchase_details.amount',
            'purchase_details.item_tax_category',
            'purchase_details.unit_name',
            DB::raw("DATE_FORMAT(purchases.date, '%d-%m-%Y') as date"),
            // DB::raw('SUM(purchase_details.amount) + COALESCE(SUM(purchase_bill_details.amount), 0) AS amount')
            // DB::raw('SUM(purchase_details.price) AS price')
            // DB::raw('SUM(purchase_details.qty) AS qty')
            // DB::raw("
            //     COALESCE((SELECT SUM(purchase_details.qty) FROM purchase_details WHERE purchase_details.purchase_id = purchases.id), 0) AS qty
            // ")
        )->leftJoin('purchases', 'purchases.id', '=', 'purchase_details.purchase_id')
            ->leftJoin('purchase_bill_details', 'purchases.id', '=', 'purchase_bill_details.purchase_id');

        // Add conditions based on the filter params
        if (!empty($item) && $item !== "0") {
            $query->whereIn('purchase_details.item_id', $item);
        }
        if (!empty($party) && $party !== "0") {
            $query->whereIn('purchases.party_id', $party);
        }
        if (!empty($newType) && $newType !== null) {
            $query->whereIn('purchases.new_type', $newType);
        }
        if (!empty($fromDate)) {
            $query->whereDate('purchases.date', '>=', $fromDate);
        }
        if (!empty($toDate)) {
            $query->whereDate('purchases.date', '<=', $toDate);
        }

        // Group by necessary fields and order by order id descending
        $query->groupBy(
            'purchase_details.id',
        )
            ->orderByDesc('purchase_details.id');

        // Execute the query and return the result
        return $query->get()->toArray();
    }

    public function getPurchaseTdsGroupByBill($filterParams)
    {
        $fromDate = $filterParams['from_date'] ?? null;
        $toDate = $filterParams['to_date'] ?? null;
        // $item = $filterParams['item'] ?? null;
        $party = $filterParams['party'] ?? null;

        // Generate a query to get the sales group by bill
        $query = Purchase::select(
            'purchases.id',
            'purchases.vch_No',
            'purchases.name_1',
            'purchases.created_at',
            'purchases.type',
            'purchases.tds_amount',
            'tds_categories.name as tds_category_name',
            DB::raw("DATE_FORMAT(purchases.date, '%d-%m-%Y') as date"),
            DB::raw("
                COALESCE((SELECT SUM(purchase_details.amount) FROM purchase_details WHERE purchase_details.purchase_id = purchases.id), 0) +
                COALESCE((SELECT SUM(purchase_bill_details.amount) FROM purchase_bill_details WHERE purchase_bill_details.purchase_id = purchases.id), 0)
                AS amount
            "),
            DB::raw("
                COALESCE((SELECT SUM(purchase_details.qty) FROM purchase_details WHERE purchase_details.purchase_id = purchases.id), 0) AS qty
            ")
        )
            ->leftJoin('purchase_details', 'purchase_details.purchase_id', '=', 'purchases.id')
            ->leftJoin('tds_categories', 'tds_categories.id', '=', 'purchases.tds_category_id')
            ->leftJoin('purchase_bill_details', 'purchase_bill_details.purchase_id', '=', 'purchases.id');

        // Add conditions based on the filter params
        if (!empty($item) && $item !== "0") {
            $query->whereIn('purchase_details.item_id', $item);
        }
        if (!empty($party) && $party !== "0") {
            $query->whereIn('purchases.party_id', $party);
        }
        if (!empty($fromDate)) {
            $query->whereDate('purchases.date', '>=', $fromDate);
        }
        if (!empty($toDate)) {
            $query->whereDate('purchases.date', '<=', $toDate);
        }

        // Group by necessary fields and order by order id descending
        $query->groupBy(
            'purchases.id',
        )->orderByDesc('purchases.id');

        // Execute the query and return the result
        return $query->get()->toArray();
    }
}
