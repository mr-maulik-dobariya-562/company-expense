<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class SaleOrder extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'product_name',
        'date',
        'party_id',
        'name_1',
        'name_2',
        'type',
        'new_type',
        'status',
        'buyer_id',
        'work_category_id',
        'site_id',
        'vch_No',
        'created_by',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function details()
    {
        return $this->hasMany(SaleOrderDetail::class);
    }

    public function billDetails()
    {
        return $this->hasMany(SaleOrderBillDetail::class, 'sale_order_id');
    }

    public function buyer()
    {
        return $this->belongsTo(Buyer::class);
    }

    public function workCategory()
    {
        return $this->belongsTo(WorkCategory::class);
    }

    public function site()
    {
        return $this->belongsTo(Site::class, 'site_id');
    }

    public function sale()
    {
        return $this->hasMany(Sale::class, 'sale_order_id');
    }

    public function buyerSingles()
    {
        return $this->belongsTo(BuyerSingle::class, "buyer_id");
    }

    public function getSalesGroupByItem($filterParams)
    {
        $fromDate = $filterParams['from_date'] ?? null;
        $toDate = $filterParams['to_date'] ?? null;
        $item = $filterParams['item'] ?? null;
        $party = $filterParams['party'] ?? null;
        $workCategory = $filterParams['work_category'] ?? null;
        $site = $filterParams['site'] ?? null;
        $buyer = $filterParams['buyer'] ?? null;
        $newType = $filterParams['new_type'] ?? null;
        $color = $filterParams['color'] ?? null;

        // Generate a query to get the sales group by bill
        $query = SaleOrderDetail::select(
            'sale_orders.id',
            'sale_order_details.item_name',
            DB::raw('SUM(sale_order_details.qty) AS qty'),
        )
            ->with(['saleOrders.buyer'])
            ->leftJoin('sale_orders', 'sale_orders.id', '=', 'sale_order_details.sale_order_id');

        // Add conditions based on the filter params
        if (!empty($item) && $item !== "0") {
            $query->whereIn('sale_order_details.item_id', $item);
        }
        if (!empty($fromDate)) {
            $query->whereDate('sale_orders.date', '>=', $fromDate);
        }
        if (!empty($toDate)) {
            $query->whereDate('sale_orders.date', '<=', $toDate);
        }
        if (!empty($party) && $party !== "0") {
            $query->whereIn('sale_orders.party_id', $party);
        }
        if (!empty($workCategory) && $workCategory !== "0") {
            $query->whereIn('sale_orders.work_category_id', $workCategory);
        }
        if (!empty($site) && $site !== "0") {
            $query->whereIn('sale_orders.site_id', $site);
        }
        if (!empty($buyer) && $buyer !== "0") {
            // $query->whereIn('sale_orders.buyer_id', $buyer);

            $query->whereHas('saleOrders.buyer', function ($q) use ($buyer) {
                $q->whereIn('buyer_single_id', $buyer);
            });
        }
        if (!empty($newType) && $newType !== null) {
            $query->whereIn('sale_orders.new_type', $newType);
        }

        // Group by necessary fields and order by order id descending
        $query->groupBy(
            'sale_order_details.item_id',

        )
            ->orderByDesc('sale_orders.id');

        // Execute the query and return the result
        return $query->get()->toArray();
    }

    public function getSalesGroupByParty($filterParams)
    {
        $fromDate = $filterParams['from_date'] ?? null;
        $toDate = $filterParams['to_date'] ?? null;
        $item = $filterParams['item'] ?? null;
        $party = $filterParams['party'] ?? null;
        $workCategory = $filterParams['work_category'] ?? null;
        $site = $filterParams['site'] ?? null;
        $buyer = $filterParams['buyer'] ?? null;
        $newType = $filterParams['new_type'] ?? null;
        $color = $filterParams['color'] ?? null;

        $query = SaleOrder::select(
            'partys.id as party_id',
            'partys.name as party_name',
            DB::raw("COALESCE(SUM(sd.total_amount), 0) + COALESCE(SUM(sb.total_amount), 0) AS amount"),
            DB::raw("COALESCE(SUM(sd.total_qty), 0) AS qty")
        )->with(['buyer'])
            ->join('partys', 'partys.id', '=', 'sale_orders.party_id')
            ->leftJoin(DB::raw("
            (SELECT sale_order_id, SUM(amount) AS total_amount, SUM(qty) AS total_qty
             FROM sale_order_details
             GROUP BY sale_order_id) sd
        "), 'sd.sale_order_id', '=', 'sale_orders.id')
            ->leftJoin(DB::raw("
            (SELECT sale_order_id, SUM(amount) AS total_amount
             FROM sale_order_bill_details
             GROUP BY sale_order_id) sb
        "), 'sb.sale_order_id', '=', 'sale_orders.id');

        // ✅ Apply Filters
        if (!empty($item) && $item !== "0") {
            $query->whereExists(function ($subQuery) use ($item) {
                $subQuery->select(DB::raw(1))
                    ->from('sale_order_details')
                    ->whereRaw('sale_order_details.sale_order_id = sale_orders.id')
                    ->whereIn('sale_order_details.item_id', $item);
            });
        }

        // Add conditions based on the filter params
        if (!empty($fromDate)) {
            $query->whereDate('sale_orders.date', '>=', $fromDate);
        }
        if (!empty($toDate)) {
            $query->whereDate('sale_orders.date', '<=', $toDate);
        }
        if (!empty($party) && $party !== "0") {
            $query->whereIn('sale_orders.party_id', $party);
        }

        if (!empty($workCategory) && $workCategory !== "0") {
            $query->whereIn('sale_orders.work_category_id', $workCategory);
        }
        if (!empty($site) && $site !== "0") {
            $query->whereIn('sale_orders.site_id', $site);
        }
        // if (!empty($buyer) && $buyer !== "0") {
        //     $query->whereIn('sale_orders.buyer_id', $buyer);
        // }
        if (!empty($buyer) && $buyer !== "0") {
            // $query->whereIn('sale_orders.buyer_id', $buyer);

            $query->whereHas('buyer', function ($q) use ($buyer) {
                $q->whereIn('buyer_single_id', $buyer);
            });
        }
        if (!empty($newType) && $newType !== null) {
            $query->whereIn('sale_orders.new_type', $newType);
        }

        // Group by necessary fields and order by order id descending
        $query->groupBy('sale_orders.party_id', 'sale_orders.name_1')
            ->orderByDesc('sale_orders.id');

        // Execute the query and return the result
        return $query->get()->toArray();
    }

    public function getSalesGroupByBill($filterParams)
    {
        $fromDate = $filterParams['from_date'] ?? null;
        $toDate = $filterParams['to_date'] ?? null;
        $item = $filterParams['item'] ?? null;
        $party = $filterParams['party'] ?? null;
        $workCategory = $filterParams['work_category'] ?? null;
        $site = $filterParams['site'] ?? null;
        $buyer = $filterParams['buyer'] ?? null;
        $newType = $filterParams['new_type'] ?? null;
        $color = $filterParams['color'] ?? null;

        $query = SaleOrder::select(
            'sale_orders.id',
            'sale_orders.vch_No',
            'sale_orders.name_1',
            'sale_orders.created_at',
            'sale_orders.type',
            DB::raw("DATE_FORMAT(sale_orders.date, '%d-%m-%Y') as date"),
            DB::raw("
                COALESCE((SELECT SUM(sale_order_details.amount) FROM sale_order_details WHERE sale_order_details.sale_order_id = sale_orders.id), 0) +
                COALESCE((SELECT SUM(sale_order_bill_details.amount) FROM sale_order_bill_details WHERE sale_order_bill_details.sale_order_id = sale_orders.id), 0)
                AS amount
            "),
            DB::raw("
                COALESCE((SELECT SUM(sale_order_details.qty) FROM sale_order_details WHERE sale_order_details.sale_order_id = sale_orders.id), 0) AS qty
            ")
        )->with(['buyer'])
            ->leftJoin('sale_order_details', 'sale_order_details.sale_order_id', '=', 'sale_orders.id')
            ->leftJoin('sale_order_bill_details', 'sale_order_bill_details.sale_order_id', '=', 'sale_orders.id');

        // Add conditions based on the filter params
        if (!empty($item) && $item !== "0") {
            $query->whereIn('sale_order_details.item_id', $item);
        }
        if (!empty($fromDate)) {
            $query->whereDate('sale_orders.date', '>=', $fromDate);
        }
        if (!empty($toDate)) {
            $query->whereDate('sale_orders.date', '<=', $toDate);
        }

        if (!empty($party) && $party !== "0") {
            $query->whereIn('sale_orders.party_id', $party);
        }

        if (!empty($workCategory) && $workCategory !== "0") {
            $query->whereIn('sale_orders.work_category_id', $workCategory);
        }
        if (!empty($site) && $site !== "0") {
            $query->whereIn('sale_orders.site_id', $site);
        }
        // if (!empty($buyer) && $buyer !== "0") {
        //     $query->whereIn('sale_orders.buyer_id', $buyer);
        // }
        if (!empty($buyer) && $buyer !== "0") {
            // $query->whereIn('sale_orders.buyer_id', $buyer);

            $query->whereHas('buyer', function ($q) use ($buyer) {
                $q->whereIn('buyer_single_id', $buyer);
            });
        }
        if (!empty($newType) && $newType !== null) {
            $query->whereIn('sale_orders.new_type', $newType);
        }

        // Group by necessary fields and order by order id descending
        $query->groupBy(
            'sale_orders.id',
        )
            ->orderByDesc('sale_orders.id');

        // Execute the query and return the result
        return $query->get()->toArray();
    }

    public function getSalesGroupByVoucher($filterParams)
    {
        $fromDate = $filterParams['from_date'] ?? null;
        $toDate = $filterParams['to_date'] ?? null;
        $item = $filterParams['item'] ?? null;
        $party = $filterParams['party'] ?? null;
        $workCategory = $filterParams['work_category'] ?? null;
        $site = $filterParams['site'] ?? null;
        $buyer = $filterParams['buyer'] ?? null;
        $newType = $filterParams['new_type'] ?? null;
        $color = $filterParams['color'] ?? null;

        // Generate a query to get the sales group by bill
        $query = SaleOrderDetail::select(
            'sale_orders.id',
            'sale_orders.product_name',
            'sale_orders.vch_No',
            'sale_orders.date',
            'sale_order_details.item_name',
            'sale_orders.created_at',
            'sale_order_details.unit_name',
            'sale_order_details.item_tax_category',
            DB::raw("DATE_FORMAT(sale_orders.date, '%d-%m-%Y') as date"),
            DB::raw('SUM(sale_order_details.amount) AS amount'),
            DB::raw('SUM(sale_order_details.price) AS price'),
            DB::raw('SUM(sale_order_details.qty) AS qty'),
        )->with(['saleOrders.buyer'])->leftJoin('sale_orders', 'sale_orders.id', '=', 'sale_order_details.sale_order_id');

        // Add conditions based on the filter params
        if (!empty($item) && $item !== "0") {
            $query->whereIn('sale_order_details.item_id', $item);
        }
        if (!empty($fromDate)) {
            $query->whereDate('sale_orders.date', '>=', $fromDate);
        }
        if (!empty($toDate)) {
            $query->whereDate('sale_orders.date', '<=', $toDate);
        }
        if (!empty($party) && $party !== "0") {
            $query->whereIn('sale_orders.party_id', $party);
        }

        if (!empty($workCategory) && $workCategory !== "0") {
            $query->whereIn('sale_orders.work_category_id', $workCategory);
        }
        if (!empty($site) && $site !== "0") {
            $query->whereIn('sale_orders.site_id', $site);
        }
        // if (!empty($buyer) && $buyer !== "0") {
        //     $query->whereIn('sale_orders.buyer_id', $buyer);
        // }
        if (!empty($buyer) && $buyer !== "0") {
            // $query->whereIn('sale_orders.buyer_id', $buyer);

            $query->whereHas('saleOrders.buyer', function ($q) use ($buyer) {
                $q->whereIn('buyer_single_id', $buyer);
            });
        }
        if (!empty($newType) && $newType !== null) {
            $query->whereIn('sale_orders.new_type', $newType);
        }

        // Group by necessary fields and order by order id descending
        $query->groupBy(
            'sale_order_details.id',
        )
            ->orderByDesc('sale_order_details.id');

        // Execute the query and return the result
        return $query->get()->toArray();
    }

    public function getPendingSalesGroupByVoucher($filterParams)
    {
        $fromDate = $filterParams['from_date'] ?? null;
        $toDate = $filterParams['to_date'] ?? null;
        $item = $filterParams['item'] ?? null;
        $party = $filterParams['party'] ?? null;

        // Generate a query to get the sales group by bill
        $query = SaleOrderDetail::select(
            'sale_orders.id',
            'sale_orders.product_name',
            'sale_orders.vch_No',
            'sale_orders.date',
            'sale_order_details.item_name',
            'sale_orders.created_at',
            'sale_order_details.unit_name',
            'sale_order_details.item_tax_category',
            'partys.name as party_name',
            DB::raw("DATE_FORMAT(sale_orders.date, '%d-%m-%Y') as date"),
            DB::raw('SUM(sale_order_details.amount) AS amount'),
            DB::raw('SUM(sale_order_details.price) AS price'),
            DB::raw('SUM(sale_order_details.qty) AS qty'),
        )
            ->leftJoin('sale_orders', 'sale_orders.id', '=', 'sale_order_details.sale_order_id')
            ->join('partys', 'partys.id', '=', 'sale_orders.party_id');

        $query->where('sale_orders.status', 'PENDING');
        // Add conditions based on the filter params
        if (!empty($item) && $item !== "0") {
            $query->whereIn('sale_order_details.item_id', $item);
        }
        if (!empty($fromDate)) {
            $query->whereDate('sale_orders.date', '>=', $fromDate);
        }
        if (!empty($toDate)) {
            $query->whereDate('sale_orders.date', '<=', $toDate);
        }
        if (!empty($party) && $party !== "0") {
            $query->whereIn('sale_orders.party_id', $party);
        }

        // Group by necessary fields and order by order id descending
        $query->groupBy(
            'sale_order_details.id',
        )->orderByDesc('sale_order_details.id');

        $data = $query->get()->toArray();

        foreach ($data as $key => $row) {
            $saleqty = Sale::where('sale_order_id', $row['id'])
                ->leftjoin('sale_details', 'sale_details.sale_id', '=', 'sales.id')
                ->select('sales.id', DB::raw('SUM(sale_details.qty) as details_sum_qty'))
                ->where('sale_details.item_name', $row['item_name'])
                ->where('sales.sale_order_id', $row['id'])
                ->get()
                ->toArray();

            $saleId = array_column($saleqty, 'id'); // Extract IDs

            // Check if sale ID exists before using it
            $saleReturnqty = SaleReturn::where('sale_id', $saleId)
                ->leftjoin('sale_return_details', 'sale_return_details.sale_return_id', '=', 'sale_returns.id')
                ->select('sale_returns.id', DB::raw('SUM(sale_return_details.qty) as details_sum_qty'))
                ->where('sale_return_details.item_name', $row['item_name'])
                ->get()
                ->toArray();

            // $data[$key]['pendingQty'] = $row['qty'] ?? 0;
            $data[$key]['saleQty'] = $saleqty[0]['details_sum_qty'] ?? 0;
            $data[$key]['returnQty'] = $saleReturnqty[0]['details_sum_qty'] ?? 0;
            $data[$key]['pendingQty'] = (($row['qty'] ?? 0) - $data[$key]['saleQty']) + $data[$key]['returnQty'];

        }
        return $data;
    }
}
