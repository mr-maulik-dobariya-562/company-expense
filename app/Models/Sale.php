<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Sale extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'product_name',
        'sale_order_id',
        'ref_no',
        'date',
        'party_id',
        'name_1',
        'name_2',
        'type',
        'new_type',
        'status',
        'buyer_id',
        'site_id',
        'work_category_id',
        'material_id',
        'vch_No',
        'store_phone',
        'store_email',
        'store_cc_email',
        'purchase_phone',
        'purchase_email',
        'purchase_cc_email',
        'lead_delivery_date',
        'of1_info',
        'tds_category_id',
        'tds_amount',
        'amount',
        'mrn_date',
        'document',
        'mrn_no',
        'mrn_image',
        'credited_days',
        'document',
        'einvi_rv',
        'einvi_ack_no',
        'einvi_ack_date',
        'transport',
        'vehicle_no',
        'form31_no',
        'payment_checkbox',
        'mrn_reminder',
        'payment_reminder',
        'created_at',
        'created_by',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function details()
    {
        return $this->hasMany(SaleDetail::class, 'sale_id');
    }

    public function party()
    {
        return $this->belongsTo(Party::class);
    }

    public function billDetails()
    {
        return $this->hasMany(SaleBillDetails::class);
    }

    public function saleReturns()
    {
        return $this->hasMany(SaleReturn::class, 'sale_id');
    }

    public function buyer()
    {
        return $this->belongsTo(Buyer::class, 'buyer_id');
    }

    public function buyerSingles()
    {
        return $this->belongsTo(BuyerSingle::class, "buyer_id");
    }

    public function workCategory()
    {
        return $this->belongsTo(WorkCategory::class, 'work_category_id');
    }

    public function site()
    {
        return $this->belongsTo(Site::class, 'site_id');
    }

    public function saleOrder()
    {
        return $this->belongsTo(SaleOrder::class);
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
        $query = SaleDetail::select(
            'sales.id',
            'sale_details.item_name',
            'sale_details.item_id',
            DB::raw('SUM(sale_details.qty) AS qty'),
        )
            ->with(['sale.buyer'])
            ->leftJoin('sales', 'sales.id', '=', 'sale_details.sale_id');

        // Add conditions based on the filter params
        if (!empty($item) && $item !== "0") {
            $query->whereIn('sale_details.item_id', $item);
        }
        if (!empty($party) && $party !== "0") {
            $query->whereIn('sales.party_id', $party);
        }
        if (!empty($workCategory) && $workCategory !== "0") {
            $query->whereIn('sales.work_category_id', $workCategory);
        }

        if (!empty($site) && $site !== "0") {
            $query->whereIn('sales.site_id', $site);
        }

        // if (!empty($buyer) && $buyer !== "0") {
        //     $query->whereIn('sales.buyer_id', $buyer);
        // }
        if (!empty($buyer) && $buyer !== "0") {
            // $query->whereIn('sale_orders.buyer_id', $buyer);

            $query->whereHas('sale.buyer', function ($q) use ($buyer) {
                $q->whereIn('buyer_single_id', $buyer);
            });
        }
        if (!empty($newType) && $newType !== null) {
            $query->whereIn('sales.new_type', $newType);
        }
        if (!empty($fromDate)) {
            $query->whereDate('sales.date', '>=', $fromDate);
        }
        if (!empty($toDate)) {
            $query->whereDate('sales.date', '<=', $toDate);
        }

        // Group by necessary fields and order by order id descending
        $query->groupBy(
            'sale_details.item_id',

        )
            ->orderByDesc('sales.id');

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

        // Generate a query to get the sales grouped by party
        $query = Sale::select(
            'partys.id as party_id',
            'partys.name as party_name',
            DB::raw("COALESCE(SUM(sd.total_amount), 0) + COALESCE(SUM(sb.total_amount), 0) AS total_amount"),
            DB::raw("COALESCE(SUM(sd.total_qty), 0) AS total_qty")
        )->with(['buyer'])
            ->join('partys', 'partys.id', '=', 'sales.party_id')
            ->leftJoin(DB::raw("
            (SELECT sale_id, SUM(amount) AS total_amount, SUM(qty) AS total_qty
             FROM sale_details
             GROUP BY sale_id) sd
        "), 'sd.sale_id', '=', 'sales.id')
            ->leftJoin(DB::raw("
            (SELECT sale_id, SUM(amount) AS total_amount
             FROM sale_bill_details
             GROUP BY sale_id) sb
        "), 'sb.sale_id', '=', 'sales.id');

        // ✅ Apply Filters
        if (!empty($item) && $item !== "0") {
            $query->whereExists(function ($subQuery) use ($item) {
                $subQuery->select(DB::raw(1))
                    ->from('sale_details')
                    ->whereRaw('sale_details.sale_id = sales.id')
                    ->whereIn('sale_details.item_id', $item);
            });
        }

        if (!empty($fromDate)) {
            $query->whereDate('sales.date', '>=', $fromDate);
        }

        if (!empty($toDate)) {
            $query->whereDate('sales.date', '<=', $toDate);
        }

        if (!empty($party) && $party !== "0") {
            $query->whereIn('sales.party_id', $party);
        }

        if (!empty($workCategory) && $workCategory !== "0") {
            $query->whereIn('sales.work_category_id', $workCategory);
        }

        if (!empty($site) && $site !== "0") {
            $query->whereIn('sales.site_id', $site);
        }

        // if (!empty($buyer) && $buyer !== "0") {
        //     $query->whereIn('sales.buyer_id', $buyer);
        // }
        if (!empty($buyer) && $buyer !== "0") {
            // $query->whereIn('sale_orders.buyer_id', $buyer);

            $query->whereHas('buyer', function ($q) use ($buyer) {
                $q->whereIn('buyer_single_id', $buyer);
            });
        }
        if (!empty($newType) && $newType !== null) {
            $query->whereIn('sales.new_type', $newType);
        }

        // ✅ Group by party and order by total amount descending
        $query->groupBy('partys.id', 'partys.name')
            ->orderByDesc('total_amount');

        // ✅ Execute the query and return the result
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
        $query = SaleDetail::select(
            'sales.id',
            'sales.product_name',
            'sales.vch_No',
            'sales.date',
            'sale_details.item_name',
            'sales.created_at',
            'sale_details.item_tax_category',
            'sale_details.unit_name',
            DB::raw("DATE_FORMAT(sales.date, '%d-%m-%Y') as date"),
            DB::raw('SUM(sale_details.amount) AS amount'),
            DB::raw('SUM(sale_details.price) AS price'),
            DB::raw('SUM(sale_details.qty) AS qty'),
        )->with(['sale.buyer'])->leftJoin('sales', 'sales.id', '=', 'sale_details.sale_id');

        // Add conditions based on the filter params
        if (!empty($item) && $item !== "0") {
            $query->whereIn('sale_details.item_id', $item);
        }
        if (!empty($party) && $party !== "0") {
            $query->whereIn('sales.party_id', $party);
        }

        if (!empty($workCategory) && $workCategory !== "0") {
            $query->whereIn('sales.work_category_id', $workCategory);
        }

        if (!empty($site) && $site !== "0") {
            $query->whereIn('sales.site_id', $site);
        }

        // if (!empty($buyer) && $buyer !== "0") {
        //     $query->whereIn('sales.buyer_id', $buyer);
        // }

        if (!empty($buyer) && $buyer !== "0") {
            // $query->whereIn('sale_orders.buyer_id', $buyer);

            $query->whereHas('sale.buyer', function ($q) use ($buyer) {
                $q->whereIn('buyer_single_id', $buyer);
            });
        }

        if (!empty($newType) && $newType !== null) {
            $query->whereIn('sales.new_type', $newType);
        }

        if (!empty($fromDate)) {
            $query->whereDate('sales.date', '>=', $fromDate);
        }
        if (!empty($toDate)) {
            $query->whereDate('sales.date', '<=', $toDate);
        }

        // Group by necessary fields and order by order id descending
        $query->groupBy(
            'sale_details.id',
        )
            ->orderByDesc('sale_details.id');

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

        $query = Sale::select(
            'sales.id',
            'sales.vch_No',
            'sales.name_1',
            'sales.created_at',
            'sales.type',
            DB::raw("DATE_FORMAT(sales.date, '%d-%m-%Y') as date"),
            DB::raw("
                COALESCE((SELECT SUM(sale_details.amount) FROM sale_details WHERE sale_details.sale_id = sales.id), 0) +
                COALESCE((SELECT SUM(sale_bill_details.amount) FROM sale_bill_details WHERE sale_bill_details.sale_id = sales.id ), 0)
                AS amount
            "),
            DB::raw("
                COALESCE((SELECT SUM(sale_bill_details.amount) FROM sale_bill_details WHERE sale_bill_details.sale_id = sales.id AND sale_bill_details.bs_name IN ('IGST', 'CGST', 'SGST')), 0)
                AS gstTotal
            "),
            DB::raw("
                COALESCE((SELECT SUM(sale_details.qty) FROM sale_details WHERE sale_details.sale_id = sales.id), 0) AS qty
            ")
        )
            ->with(['buyer'])
            ->leftJoin('sale_details', 'sale_details.sale_id', '=', 'sales.id')
            ->leftJoin('sale_bill_details', 'sale_bill_details.sale_id', '=', 'sales.id');

        // Add conditions based on the filter params
        if (!empty($item) && $item !== "0") {
            $query->whereIn('sale_details.item_id', $item);
        }
        if (!empty($party) && $party !== "0") {
            $query->whereIn('sales.party_id', $party);
        }

        if (!empty($workCategory) && $workCategory !== "0") {
            $query->whereIn('sales.work_category_id', $workCategory);
        }

        if (!empty($site) && $site !== "0") {
            $query->whereIn('sales.site_id', $site);
        }

        // if (!empty($buyer) && $buyer !== "0") {
        //     $query->whereIn('sales.buyer_id', $buyer);
        // }

        if (!empty($buyer) && $buyer !== "0") {
            // $query->whereIn('sale_orders.buyer_id', $buyer);

            $query->whereHas('buyer', function ($q) use ($buyer) {
                $q->whereIn('buyer_single_id', $buyer);
            });
        }
        if (!empty($newType) && $newType !== null) {
            $query->whereIn('sales.new_type', $newType);
        }
        if (!empty($fromDate)) {
            $query->whereDate('sales.date', '>=', $fromDate);
        }
        if (!empty($toDate)) {
            $query->whereDate('sales.date', '<=', $toDate);
        }

        // Group by necessary fields and order by order id descending
        $query->groupBy('sales.id')
            ->orderByDesc('sales.id');

        // Execute the query and return the result
        return $query->get()->toArray();
    }

    public function getSalesTdsGroupByBill($filterParams)
    {
        $fromDate = $filterParams['from_date'] ?? null;
        $toDate = $filterParams['to_date'] ?? null;
        // $item = $filterParams['item'] ?? null;
        $party = $filterParams['party'] ?? null;

        $query = Sale::select(
            'sales.id',
            'sales.vch_No',
            'sales.name_1',
            'sales.created_at',
            'sales.type',
            'sales.tds_amount',
            'tds_categories.name as tds_category_name',
            DB::raw("DATE_FORMAT(sales.date, '%d-%m-%Y') as date"),
            DB::raw("
                COALESCE((SELECT SUM(sale_details.amount) FROM sale_details WHERE sale_details.sale_id = sales.id), 0) +
                COALESCE((SELECT SUM(sale_bill_details.amount) FROM sale_bill_details WHERE sale_bill_details.sale_id = sales.id), 0)
                AS amount
            "),
            DB::raw("
                COALESCE((SELECT SUM(sale_details.qty) FROM sale_details WHERE sale_details.sale_id = sales.id), 0) AS qty
            ")
        )
            ->leftJoin('sale_details', 'sale_details.sale_id', '=', 'sales.id')
            ->leftJoin('tds_categories', 'tds_categories.id', '=', 'sales.tds_category_id')
            ->leftJoin('sale_bill_details', 'sale_bill_details.sale_id', '=', 'sales.id');

        // Add conditions based on the filter params
        if (!empty($item) && $item !== "0") {
            $query->whereIn('sale_details.item_id', $item);
        }
        if (!empty($party) && $party !== "0") {
            $query->whereIn('sales.party_id', $party);
        }
        if (!empty($fromDate)) {
            $query->whereDate('sales.date', '>=', $fromDate);
        }
        if (!empty($toDate)) {
            $query->whereDate('sales.date', '<=', $toDate);
        }

        // Group by necessary fields and order by order id descending
        $query->groupBy('sales.id')
            ->orderByDesc('sales.id');

        // Execute the query and return the result
        return $query->get()->toArray();
    }

    public function getMasterSalesGroupByBill($filterParams)
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

        $query = Sale::select(
            'sales.id',
            'sales.vch_No',
            'sales.name_1',
            'sales.created_at',
            'sales.type',
            DB::raw("DATE_FORMAT(sales.date, '%d-%m-%Y') as date"),
            DB::raw("
                COALESCE((SELECT SUM(sale_details.amount) FROM sale_details WHERE sale_details.sale_id = sales.id), 0) +
                COALESCE((SELECT SUM(sale_bill_details.amount) FROM sale_bill_details WHERE sale_bill_details.sale_id = sales.id ), 0)
                AS amount
            "),
            DB::raw("
                COALESCE((SELECT SUM(sale_bill_details.amount) FROM sale_bill_details WHERE sale_bill_details.sale_id = sales.id AND sale_bill_details.bs_name IN ('IGST', 'CGST', 'SGST')), 0)
                AS gstTotal
            "),
            DB::raw("
                COALESCE((SELECT SUM(sale_details.qty) FROM sale_details WHERE sale_details.sale_id = sales.id), 0) AS qty
            "),
            DB::raw("
                COALESCE((SELECT SUM(sale_return_details.amount) FROM sale_return_details WHERE sale_return_details.sale_return_id IN (SELECT id FROM sale_returns WHERE sale_id = sales.id)), 0)
                AS returnAmount
            "),
            DB::raw("
                COALESCE((SELECT SUM(sale_return_bill_details.amount) FROM sale_return_bill_details WHERE sale_return_bill_details.sale_return_id IN (SELECT id FROM sale_returns WHERE sale_id = sales.id)), 0)
                AS returnGstTotal
            ")
        )
            ->with(['buyer'])
            ->leftJoin('sale_details', 'sale_details.sale_id', '=', 'sales.id')
            ->leftJoin('sale_bill_details', 'sale_bill_details.sale_id', '=', 'sales.id')
            ->leftJoin('sale_returns', 'sale_returns.sale_id', '=', 'sale_details.id')
            ->leftJoin('sale_return_details', 'sale_return_details.sale_return_id', '=', 'sale_returns.id')
            ->leftJoin('sale_return_bill_details', 'sale_return_bill_details.sale_return_id', '=', 'sale_returns.id');

        // Add conditions based on the filter params
        if (!empty($item) && $item !== "0") {
            $query->whereIn('sale_details.item_id', $item);
        }
        if (!empty($party) && $party !== "0") {
            $query->whereIn('sales.party_id', $party);
        }

        if (!empty($workCategory) && $workCategory !== "0") {
            $query->whereIn('sales.work_category_id', $workCategory);
        }

        if (!empty($site) && $site !== "0") {
            $query->whereIn('sales.site_id', $site);
        }

        // if (!empty($buyer) && $buyer !== "0") {
        //     $query->whereIn('sales.buyer_id', $buyer);
        // }

        if (!empty($buyer) && $buyer !== "0") {
            // $query->whereIn('sale_orders.buyer_id', $buyer);

            $query->whereHas('buyer', function ($q) use ($buyer) {
                $q->whereIn('buyer_single_id', $buyer);
            });
        }
        if (!empty($newType) && $newType !== null) {
            $query->whereIn('sales.new_type', $newType);
        }
        if (!empty($fromDate)) {
            $query->whereDate('sales.date', '>=', $fromDate);
        }
        if (!empty($toDate)) {
            $query->whereDate('sales.date', '<=', $toDate);
        }

        // Group by necessary fields and order by order id descending
        $query->groupBy('sales.id')
            ->orderByDesc('sales.id');

        // Execute the query and return the result
        return $query->get()->toArray();
    }
}
