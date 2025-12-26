<?php

namespace App\Http\Controllers;

use App\Models\Boq;
use App\Models\Item;
use App\Models\Sale;
use App\Models\Site;
use App\Models\Buyer;
use App\Models\Party;
use App\Models\Type;
use App\Models\Purchase;
use App\Models\SaleOrder;
use App\Traits\DataTable;
use App\Models\SaleReturn;
use App\Models\LogActivity;
use App\Models\PurchaseReturn;
use App\Models\TdsCategory;
use App\Models\WorkCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;

class DashboardController extends Controller implements HasMiddleware
{
    use DataTable;
    public static function middleware(): array
    {
        return [
            new Middleware('permission:dashboard-create', only: ['create']),
            new Middleware('permission:dashboard-view', only: ['index', "getView"]),
            new Middleware('permission:dashboard-edit', only: ['edit', "update"]),
            new Middleware('permission:sale-delete', only: ['saleDelete']),
            new Middleware('permission:purchase-delete', only: ['purchaseDelete']),
            new Middleware('permission:sale-order-delete', only: ['saleOrderDelete']),
            new Middleware('permission:sale-order-box', only: [
                'getSaleOrderData',
                'getSaleOrderReport',
                'saleOrderDataUpdate',
            ]),
            new Middleware('permission:sale-box', only: [
                'getSaleData',
                'getSaleReport',
                'saleDataUpdate',
                'saleMrnDataUpdate',
                'salePaymentDataUpdate',
            ]),
            new Middleware('permission:purchase-box', only: [
                'getPurchaseData',
                'getPurchaseReport',
                'purchaseDataUpdate',
                'purchasePaymentDataUpdate',
            ]),
            new Middleware('permission:production-sale-order-box', only: [
                'getProductionSaleOrderData',
                'getProductionSaleOrderReport',
                'productionSaleOrderDataUpdate',
            ]),
        ];
    }

    public function __construct()
    {
        DB::statement("SET SESSION sql_mode=(SELECT REPLACE(@@sql_mode, 'ONLY_FULL_GROUP_BY', ''));");
    }

    public function index()
    {
        $totalSale = Sale::count();
        $totalPurchase = Purchase::count();
        $totalSaleOrder = SaleOrder::count();
        return view('dashboard.dashboard', compact('totalSale', 'totalSaleOrder', 'totalPurchase'));
    }

    public function getSaleData()
    {
        $partys = Party::all();
        $items = Item::all();
        $workCategories = WorkCategory::all();
        $tdsCategories = TdsCategory::all();
        $types = Type::all();
        $site = Site::get(['id', 'name']);
        $buyer = DB::table('buyer_singles')->get(['id', 'name']);
        return view('dashboard.dashboard_sale', compact('partys', 'items', 'workCategories', 'site', 'buyer', 'tdsCategories', 'types'));
    }

    public function getSaleOrderData()
    {
        $buyers = Buyer::all();
        $partys = Party::all();
        $items = Item::all();
        $types = Type::all();
        $workCategories = WorkCategory::all();
        $site = Site::get(['id', 'name']);
        $buyer = DB::table('buyer_singles')->get(['id', 'name']);
        return view('dashboard.dashboard_sale_order', compact('buyers', 'workCategories', 'partys', 'items', 'site', 'buyer', 'types'));
    }

    public function getProductionSaleOrderData()
    {
        $partys = Party::all();
        $items = Item::all();
        return view('dashboard.dashboard_production_sale_order', compact('partys', 'items'));
    }

    public function getPurchaseData()
    {
        $partys = Party::all();
        $items = Item::all();
        $types = Type::all();
        $tdsCategories = TdsCategory::all();
        return view('dashboard.dashboard_purchase', compact('partys', 'items', 'tdsCategories', 'types'));
    }

    public function getProductionSaleOrderReport(Request $request)
    {
        /* Define Searchable */
        $searchableColumns = [
            'product_name',
            'date',
            'name_1',
            'name_2',
            'type',
            'status',
            'buyer_id',
            'work_category_id',
            'vch_No',
            'created_by',
        ];

        /* Add Model here with relation */
        $this->model(model: SaleOrder::class, with: ["createdBy", "details", 'buyer', 'workCategory', 'site', 'buyer.buyerSingles']);

        /* Add Filter here */
        $this->filter([
            "status" => $request->status ?? 'PENDING',
            "party_id" => $request->party_id,
            "work_category_id" => $request->work_category_id,
            "site_id" => $request->site_id,
            "buyer:buyer_single_id" => $request->buyer_id,
            "new_type" => $request->new_type,
            'details:item_id' => $request->item_id,
        ])->where(function ($query) use ($request) {
            if ($request->color == 'red') {
                $query->where('status', 'PENDING')
                    ->whereNull('buyer_id')
                    ->whereNull('work_category_id')
                    ->whereNull('site_id');
            }

            // Custom Filter for Green Color Condition
            if ($request->color == 'green') {
                $query->where('status', 'PENDING')
                    ->where(function ($q) {
                        $q->whereNotNull('buyer_id')
                            ->orWhereNotNull('work_category_id')
                            ->orWhereNotNull('site_id');
                    });
            }
        });
        $this->enableDateFilters('date');
        $this->orderBy("date", "DESC");
        $this->filter([
            'is_boq_status' => $request->is_boq_status
        ]);

        $deletePermission = $this->hasPermission("sale-order-delete");
        /* Add Formatting here */
        $this->formateArray(function ($row, $index) use ($searchableColumns, $deletePermission) {

            // $color = '';
            // if ($row->status == 'PENDING' && $row->buyer_id == null && $row->work_category_id == null && $row->site_id == null) {
            //     $color = 'background-color:#fb9191bf;';
            // } else if ($row->status == 'PENDING') {
            //     $color = 'background-color:#abedab;';
            // }

            $color = '';
            switch ($row->is_boq_status) {
                case "Pending":
                    $color = "background-color:#ffb6b6;";
                    break;
                case "Partially Completed":
                    $color = "background-color:#ffff9a;";
                    break;
                case "Completed":
                    $color = "background-color:#ccffcc;";
                    break;
            }

            $action = "";
            $action .= "<a class='itemModel text-white m-1'
                            data-id='{$row->id}'
                            data-bs-toggle='tooltip' data-bs-placement='top'
                            data-bs-original-title='Edit' href='javascript:void(0);'>
                            <i style='color:#007bff' class='far fa-eye' aria-hidden='true'></i>
                        </a>";

            return [
                "row_style" => $color,
                "id" => $row->id,
                "name" => $row->product_name,
                "date" => date('d-m-Y', strtotime($row->date)),
                "name_1" => $row->name_1,
                "name_2" => $row->name_2,
                "type" => $row->type,
                "status" => $row->status,
                "buyer_id" => $row->buyer?->buyerSingles?->name,
                "work_category_id" => $row->workCategory?->name,
                "site_id" => $row->site?->name,
                "vch_No" => $row->vch_No,
                "qty" => $row->details->sum('qty') + $row->billDetails->sum('qty'),
                "is_boq_status" => $row->is_boq_status,
                'remark' => $row->remark,
                "new_type" => $row->new_type,
                "amount" => $row->details->sum('amount') + $row->billDetails->sum('amount'),
                "document" => $row->document ? "<a download='sale_document_{$row->document}' href='" . asset('uploads/sale_document/' . $row->document) . "' target='_blank'><i class='fas fa-download' style='color:rgb(238, 44, 44)'></i></a>" : "",
                "action" => $action,
                "created_by" => $row->createdBy?->displayName(),
                "created_at" => $row->created_at ? $row->created_at->format('d/m/Y H:i:s') : '',
                "updated_at" => $row->updated_at ? $row->updated_at->format('d/m/Y H:i:s') : '',
            ];
        });
        return $this->getListAjax($searchableColumns);
    }

    public function productionSaleOrderModelData(Request $request)
    {
        $action = $request->action ?? null;
        $saleOrders = SaleOrder::with(['details', 'billDetails', 'buyer', 'sale', 'sale.saleReturns', 'workCategory'])->find($request->id);
        $saleIds = $saleOrders->sale->pluck('id')->toArray();
        $sale_returns = SaleReturn::whereIn('sale_id', $saleIds)->get()->toArray();
        $boqs = Boq::where('sale_order_id', $request->id)->get()->toArray();
        return view('dashboard.production_sale_order_model', compact('saleOrders', 'action', 'sale_returns', 'boqs'));
    }

    public function getSaleOrderReport(Request $request)
    {
        /* Define Searchable */
        $searchableColumns = [
            'product_name',
            'date',
            'name_1',
            'name_2',
            'type',
            'status',
            'buyer_id',
            'work_category_id',
            'vch_No',
            'created_by',
        ];

        /* Add Model here with relation */
        $this->model(model: SaleOrder::class, with: ["createdBy", "details", 'buyer', 'workCategory', 'site', 'buyer.buyerSingles']);

        /* Add Filter here */
        $this->filter([
            "status" => $request->status ?? 'PENDING',
            "party_id" => $request->party_id,
            "work_category_id" => $request->work_category_id,
            "site_id" => $request->site_id,
            "buyer:buyer_single_id" => $request->buyer_id,
            "new_type" => $request->new_type,
            'details:item_id' => $request->item_id,
        ])->where(function ($query) use ($request) {
            if ($request->color == 'red') {
                $query->where('status', 'PENDING')
                    ->whereNull('buyer_id')
                    ->whereNull('work_category_id')
                    ->whereNull('site_id');
            }

            // Custom Filter for Green Color Condition
            if ($request->color == 'green') {
                $query->where('status', 'PENDING')
                    ->where(function ($q) {
                        $q->whereNotNull('buyer_id')
                            ->orWhereNotNull('work_category_id')
                            ->orWhereNotNull('site_id');
                    });
            }
        });
        $this->enableDateFilters('date');
        $this->orderBy("date", "DESC");
        $deletePermission = $this->hasPermission("sale-order-delete");
        /* Add Formatting here */
        $this->formateArray(function ($row, $index) use ($searchableColumns, $deletePermission) {

            $color = '';
            if ($row->status == 'PENDING' && $row->buyer_id == null && $row->work_category_id == null && $row->site_id == null) {
                $color = 'background-color:#fb9191bf;';
            } else if ($row->status == 'PENDING') {
                $color = 'background-color:#abedab;';
            }

            $action = " <a class='edit-btn text-white m-1'
                            data-id='{$row->id}'
                            data-buyer='{$row->buyer_id}'
                            data-work_category='{$row->work_category_id}'
                            data-site='{$row->site_id}'
                            data-vch-no='{$row->vch_No}'
                            data-status='{$row->status}'
                            data-store_phone='{$row->store_phone}'
                            data-store_email='{$row->store_email}'
                            data-store_cc_email='{$row->store_cc_email}'
                            data-purchase_phone='{$row->purchase_phone}'
                            data-purchase_email='{$row->purchase_email}'
                            data-purchase_cc_email='{$row->purchase_cc_email}'
                            data-new_type='{$row->new_type}'
                            data-document='{$row->document}'
                            data-bs-toggle='tooltip' data-bs-placement='top' data-bs-original-title='Edit' href='javascript:void(0);'>
                            <i class='far fa-edit' aria-hidden='true' style='color: #007bff'></i>
                        </a>";
            $action .= "<a class='itemModel text-white m-1'
                            data-id='{$row->id}'
                            data-bs-toggle='tooltip' data-bs-placement='top'
                            data-bs-original-title='Edit' href='javascript:void(0);'>
                            <i style='color:#007bff' class='far fa-eye' aria-hidden='true'></i>
                        </a>";

            if ($row->status == 'PENDING' && $deletePermission) {

                $action .= "<a class='delete-btn text-white m-1 '
                            data-id='{$row->id}'
                            data-bs-toggle='tooltip' data-bs-placement='top'
                            data-bs-original-title='Delete' href='javascript:void(0);'>
                            <i style='color:#ff0000' class='fas fa-trash-alt' aria-hidden='true'></i>
                        </a>";
            }

            return [
                "row_style" => $color,
                "id" => $row->id,
                "name" => $row->product_name,
                "date" => date('d-m-Y', strtotime($row->date)),
                "name_1" => $row->name_1,
                "name_2" => $row->name_2,
                "type" => $row->type,
                "status" => $row->status,
                "buyer_id" => $row->buyer?->buyerSingles?->name,
                "work_category_id" => $row->workCategory?->name,
                "site_id" => $row->site?->name,
                "vch_No" => $row->vch_No,
                "new_type" => $row->new_type,
                "amount" => $row->details->sum('amount') + $row->billDetails->sum('amount'),
                "document" => $row->document ? "<a download='sale_document_{$row->document}' href='" . asset('uploads/sale_document/' . $row->document) . "' target='_blank'><i class='fas fa-download' style='color:rgb(238, 44, 44)'></i></a>" : "",
                "action" => $action,
                "created_by" => $row->createdBy?->displayName(),
                "created_at" => $row->created_at ? $row->created_at->format('d/m/Y H:i:s') : '',
                "updated_at" => $row->updated_at ? $row->updated_at->format('d/m/Y H:i:s') : '',
            ];
        });
        return $this->getListAjax($searchableColumns);
    }

    public function saleOrderDataUpdate(Request $request)
    {
        $validate = $request->validate([
            "buyer" => "sometimes|nullable",
            "work_category" => "sometimes|nullable",
            "site" => "sometimes|nullable",
            "status" => "sometimes|in:PENDING,COMPLETED",
            "new_type" => "sometimes|nullable",
            "id" => "required",
            "document" => "nullable|file|max:5120",
        ]);

        if (!$validate) {
            return $this->withError("Validation Error");
        }
        $store_email = implode(',', $request->store_email);
        $store_cc_email = implode(',', $request->store_cc_email);
        $purchase_email = implode(',', $request->purchase_email);
        $purchase_cc_email = implode(',', $request->purchase_cc_email);

        $sale = SaleOrder::where('id', $request->id);
        $sale_order = [
            "store_phone" => $request->store_phone,
            "store_email" => $store_email,
            "store_cc_email" => $store_cc_email,
            "purchase_phone" => $request->purchase_phone,
            "purchase_email" => $purchase_email,
            "purchase_cc_email" => $purchase_cc_email,
            "buyer_id" => $request->buyer,
            "work_category_id" => $request->work_category,
            "site_id" => $request->site,
            "status" => $request->status,
            "new_type" => $request->new_type
        ];

        $saleUpdate = Sale::where('sale_order_id', $request->id);

        $datasale = [
            "store_phone" => $request->store_phone,
            "store_email" => $store_email,
            "store_cc_email" => $store_cc_email,
            "purchase_phone" => $request->purchase_phone,
            "purchase_email" => $purchase_email,
            "purchase_cc_email" => $purchase_cc_email,
        ];
        $saleUpdate->update($datasale ?? []);

        if ($request->hasFile('document')) {
            // Delete old image if it exists
            if (!empty($sale->document) && file_exists(public_path('uploads/sale_document/' . $sale->document))) {
                unlink(public_path('uploads/sale_document/' . $sale->document)); // Delete existing image from storage
            }

            // Save new image
            $image = $request->file('document');
            $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads/sale_document/'), $imageName); // Move new image
            $sale_order['document'] = $imageName; // Store path in database
        }

        $sale->update($sale_order ?? []);

        Sale::where('sale_order_id', $request->id)->update([
            "buyer_id" => $request->buyer,
            "work_category_id" => $request->work_category,
            "site_id" => $request->site,
            "new_type" => $request->new_type
        ]);

        $log = [
            'subject' => 'Sale Order',
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'ip' => $request->ip(),
            'agent' => json_encode($sale_order),
            'vch_no' => $request->vch_No,
            'table_id' => $request->id,
            'user_id' => auth()->check() ? auth()->user()->id : 1
        ];
        LogActivity::create($log);

        if ($request->ajax()) {
            return $this->withSuccess("Data Updated successfully");
        }
        return $this->withSuccess("Data Updated successfully")->back();
    }

    public function saleOrderDelete(Request $request)
    {
        // SaleOrder::with('details', 'billDetails')->where('id', $request->id)->forceDelete();
        DB::transaction(function () use ($request) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            // Delete related details records
            DB::table('sale_order_details')->where('sale_order_id', $request->id)->delete();

            // Delete related bill details records
            DB::table('sale_order_bill_details')->where('sale_order_id', $request->id)->delete();

            // Delete the sale order record
            DB::table('sale_orders')->where('id', $request->id)->delete();

            DB::table('sales')->where('sale_order_id', $request->id)->update(['sale_order_id' => NULL]);
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        });

        return $this->withSuccess("Data Deleted successfully");
    }

    public function getSaleReport(Request $request)
    {
        /* Define Searchable */
        $searchableColumns = [
            'name_1',
            'vch_No',
            'saleOrder:vch_No',
        ];

        /* Add Model here with relation */
        $this->model(model: Sale::class, with: ["createdBy", "details", "buyer", "workCategory", "site"]);

        $filters = [
            'party_id' => $request->party_id,
            'details:item_id' => $request->item_id,
            'work_category_id' => $request->work_category_id,
            'site_id' => $request->site_id,
            'buyer:buyer_single_id' => $request->buyer_id,
            'new_type' => $request->new_type,
        ];

        if ($request->status == 'completed') {
            $filters['payment_checkbox'] = 1;
        }

        $modelClass = match ($request->status) {
            'info' => 'edit-btn',
            'mrn' => 'mrn-edit-btn',
            'payment' => 'payment-edit-btn',
            default => 'edit-btn',
        };
        $deletePermission = $this->hasPermission("sale-delete");

        $this->filter($filters)
            ->where(function ($query) use ($request) {
                if (!empty($request->color)) {
                    $color = $request->color;
                    $query->where(function ($subQuery) use ($color, $request) {
                        if ($request->status == 'info') {
                            if ($color == 'green') {
                                $subQuery->orwhere('payment_checkbox', 1);
                                $subQuery->orWhereNotNull('lead_delivery_date'); // ✅ Green: lead_delivery_date exists
                            }
                            if ($color == 'red') {
                                $subQuery->orWhere(function ($q) {
                                    $q->whereNull('lead_delivery_date')
                                        ->where('payment_checkbox', 0)
                                        ->whereNotNull('sale_order_id'); // ✅ Red: lead_delivery_date is NULL & sale_order_id exists
                                });
                            }
                            if ($color == 'purple') {
                                $subQuery->orWhere(function ($q) {
                                    $q->whereNull('sale_order_id')
                                        ->where('payment_checkbox', 0)
                                        ->whereNull('lead_delivery_date'); // ✅ Purple: sale_order_id is NULL & lead_delivery_date is NULL
                                });
                            }
                            if ($color == 'orange') {
                                $subQuery->Where("id", 0);
                            }
                            // if ($color == 'white') {
                            //     $subQuery->orWhere(function ($q) {
                            //         $q->whereNull('lead_delivery_date')
                            //             ->where('payment_checkbox', 0); // ✅ Purple: sale_order_id is NULL & lead_delivery_date is NULL
                            //     });
                            // }
                        } elseif ($request->status == 'mrn') {
                            if ($color == 'green') {
                                $subQuery->orWhereNotNull('mrn_date')
                                    ->orwhere('payment_checkbox', 1); // ✅ Green: mrn_date exists
                            }
                            if ($color == 'red') {
                                $subQuery->orWhere(function ($q) {
                                    $q->whereNotNull('lead_delivery_date')
                                        ->WhereNull('mrn_date')
                                        ->where('payment_checkbox', 0)
                                        ->whereRaw("lead_delivery_date < NOW()"); // ✅ Red: lead_delivery_date expired
                                });
                            }
                            if ($color == 'orange' || $color == 'purple') {
                                $subQuery->Where("id", 0); // ✅ Orange: Amount > 0
                            }
                            // if ($color == 'white') {
                            //     $subQuery->orWhere(function ($q) {
                            //         $q->whereNull('mrn_date')
                            //             ->where('payment_checkbox', 0); // ✅ Purple: sale_order_id is NULL & lead_delivery_date is NULL
                            //     });
                            // }
                        } elseif ($request->status == 'payment') {
                            if ($color == 'green') {
                                $subQuery->where('payment_checkbox', 1); // ✅ Green: payment completed
                            }
                            if ($color == 'red') {
                                $subQuery->orWhere(function ($q) {
                                    $q->whereNotNull('mrn_date')
                                        ->where('payment_checkbox', 0)
                                        ->whereRaw("DATE_ADD(mrn_date, INTERVAL credited_days DAY) < NOW()"); // ✅ Red: Due date expired
                                });
                            }
                            if ($color == 'orange') {
                                $subQuery->WhereRaw("amount > 0"); // ✅ Orange: Amount > 0
                                $subQuery->where('payment_checkbox', 0);
                            }
                            if ($color == 'purple') {
                                $subQuery->Where("id", 0);
                            }
                            // if ($color == 'white') {
                            //     $subQuery->orWhere(function ($q) {
                            //         $q->whereNotNull('mrn_date')
                            //             ->whereRaw("DATE_ADD(mrn_date, INTERVAL credited_days DAY) >= NOW()")
                            //             ->where('payment_checkbox', 0); // ✅ Purple: sale_order_id is NULL & lead_delivery_date is NULL
                            //     });
                            // }
                        }
                    });
                }

                if ($request->search_manual) {
                    $query->where(function ($q) use ($request) {
                        $q->where('name_1', 'LIKE', "%{$request->search_manual}%")
                            ->orWhereHas("saleOrder", fn($q) => $q->where('vch_No', 'LIKE', "%{$request->search_manual}%"))
                        ;
                    });
                }
                return $query;
            })
            ->enableDateFilters('date')
            ->orderBy("date", "DESC")
            ->formateArray(function ($row, $index) use ($request, $modelClass, $deletePermission) {
                $action = '';
                $color = '';
                $colorsdata = ['info' => 'white', 'payment' => 'white', 'mrn' => 'white'];

                if ($request->status == 'info') {
                    $action = " <a class='" . $modelClass . " m-1'
                               data-id='{$row->id}'
                               data-store_phone='{$row->store_phone}'
                               data-store_email='{$row->store_email}'
                               data-store_cc_email='{$row->store_cc_email}'
                               data-purchase_phone='{$row->purchase_phone}'
                               data-purchase_email='{$row->purchase_email}'
                               data-purchase_cc_email='{$row->purchase_cc_email}'
                               data-lead_delivery_date='{$row->lead_delivery_date}'
                               data-work_category='{$row->work_category_id}'
                               data-site='{$row->site_id}'
                               data-buyer='{$row->buyer_id}'
                               data-vch-no='{$row->vch_No}'
                               data-mrn_reminder='{$row->mrn_reminder}'
                               data-sale_order_id='{$row->sale_order_id}'
                               data-new_type='{$row->new_type}'
                               data-document='{$row->document}'
                               data-bs-toggle='tooltip' data-bs-placement='top' data-bs-original-title='Edit' href='javascript:void(0);'>
                               <i class='far fa-edit' aria-hidden='true'></i>
                            </a>";
                    if ($row->lead_delivery_date != null || $row->payment_checkbox == 1) {
                        $color = 'background-color:#abedab';
                    } else {

                        $color = 'background-color:#fb9191bf;';
                        if (empty($row->sale_order_id)) {
                            $color = 'background-color:#d3c4d9';
                        }

                    }
                } else if ($request->status == 'mrn') {
                    $action = " <a class='" . $modelClass . " m-1'
                                data-id='{$row->id}'
                                data-mrn_date='{$row->mrn_date}'
                                data-mrn_no='{$row->mrn_no}'
                                data-credited_days='{$row->credited_days}'
                                data-payment_reminder='{$row->payment_reminder}'
                                data-vch-no='{$row->vch_No}'
                                data-bs-toggle='tooltip' data-bs-placement='top' data-bs-original-title='Edit' href='javascript:void(0);'>
                                <i class='far fa-edit' aria-hidden='true'></i>
                            </a>";
                    if (!empty($row->mrn_date) || $row->payment_checkbox == 1) {
                        $color = 'background-color:#abedab';
                    } else if (!empty($row->lead_delivery_date) && $row->payment_checkbox == 0 && Carbon::parse($row->lead_delivery_date) < Carbon::now()) {
                        $color = 'background-color:#fb9191bf';
                    }
                } else if ($request->status == 'payment') {
                    $action = " <a class='" . $modelClass . " m-1'
                                data-id='{$row->id}'
                                data-amount='{$row->amount}'
                                data-tds_category_id='{$row->tds_category_id}'
                                data-tds_amount='{$row->tds_amount}'
                                data-payment_checkbox='{$row->payment_checkbox}'
                                data-vch-no='{$row->vch_No}'
                                data-bs-toggle='tooltip'
                                data-bs-placement='top'
                                data-bs-original-title='Edit'
                                href='javascript:void(0);'>
                                <i class='far fa-edit' aria-hidden='true'></i>
                            </a>";
                    $amount = array_sum(array_map('intval', explode(',', $row->amount)));
                    if (!empty($row->mrn_date) || $row->payment_checkbox == 1) {
                        if ($row->payment_checkbox == 1) {
                            $color = 'background-color:#abedab'; // green
                        } else if ($row->payment_checkbox == 0 && round($amount) < 1 && Carbon::parse($row->mrn_date)->addDays($row->credited_days) < Carbon::now()) {
                            $color = 'background-color:#fb9191bf'; // red
                        } else if ($amount > 0) {
                            $color = 'background-color:#ffcb72fa'; // orange
                        }
                    }
                }


                /* info */
                if ($row->lead_delivery_date != null || $row->payment_checkbox == 1) {
                    $colorsdata['info'] = "lightgreen";
                } else {
                    if (!empty($row->lead_delivery_date)) {
                        $colorsdata['info'] = "lightcoral";
                    }
                    if (empty($row->sale_order_id)) {
                        $colorsdata['info'] = "lightpurple";
                    }

                }

                /* MRN */
                if (!empty($row->mrn_date) || $row->payment_checkbox == 1) {
                    $colorsdata['mrn'] = "lightgreen";
                } else {
                    if (!empty($row->lead_delivery_date) && $row->payment_checkbox == 0 && Carbon::parse($row->lead_delivery_date) < Carbon::now()) {
                        $colorsdata['mrn'] = "lightcoral";
                    }
                }

                /* Payment */
                $amount = array_sum(array_map('intval', explode(',', $row->amount)));
                if (!empty($row->mrn_date) || $row->payment_checkbox == 1) {
                    if ($row->payment_checkbox == 1) {
                        $colorsdata['payment'] = "lightgreen";
                    } else if ($row->payment_checkbox == 0 && round($amount) < 1 && Carbon::parse($row->mrn_date)->addDays($row->credited_days) < Carbon::now()) {
                        $colorsdata['payment'] = "lightcoral";
                    } else if ($row->payment_checkbox == 0 && $amount > 0) {
                        $colorsdata['payment'] = "#ffcb72fa";
                    }
                }


                $symbol = "<div style='display: flex; gap: 8px; padding: 10px;'>
                            <div data-inner='1'
                                style='width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; background-color: {$colorsdata['info']}; transition: all 0.3s ease-in-out;
                                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); font-size: 16px; font-weight: bold; cursor: pointer;'
                                onmouseover='this.style.transform=\"scale(1.2)\"; this.style.boxShadow=\"0 6px 12px rgba(0, 0, 0, 0.3)\";'
                                onmouseout='this.style.transform=\"scale(1)\"; this.style.boxShadow=\"0 4px 8px rgba(0, 0, 0, 0.2)\";'>
                                1
                            </div>
                            <div data-inner='2'
                                style='width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; background-color: {$colorsdata['mrn']}; transition: all 0.3s ease-in-out;
                                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); font-size: 16px; font-weight: bold; cursor: pointer;'
                                onmouseover='this.style.transform=\"scale(1.2)\"; this.style.boxShadow=\"0 6px 12px rgba(0, 0, 0, 0.3)\";'
                                onmouseout='this.style.transform=\"scale(1)\"; this.style.boxShadow=\"0 4px 8px rgba(0, 0, 0, 0.2)\";'>
                                2
                            </div>
                            <div data-inner='3'
                                style='width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; background-color: {$colorsdata['payment']}; transition: all 0.3s ease-in-out;
                                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); font-size: 16px; font-weight: bold; cursor: pointer;'
                                onmouseover='this.style.transform=\"scale(1.2)\"; this.style.boxShadow=\"0 6px 12px rgba(0, 0, 0, 0.3)\";'
                                onmouseout='this.style.transform=\"scale(1)\"; this.style.boxShadow=\"0 4px 8px rgba(0, 0, 0, 0.2)\";'>
                                3
                            </div>
                        </div>";

                $action .= "<a class='itemModel text-white m-1'
                                data-id='{$row->id}'
                                data-bs-toggle='tooltip'
                                data-bs-placement='top'
                                data-bs-original-title='Edit'
                                href='javascript:void(0);'
                                >
                                <i style='color:#007bff' class='far fa-eye' aria-hidden='true'></i>
                            </a>";

                if ($request->status == 'info' && $deletePermission) {
                    $action .= "<a class='delete-btn text-white m-1 '
                                data-id='{$row->id}'
                                data-bs-toggle='tooltip'
                                data-bs-placement='top'
                                data-bs-original-title='Delete'
                                href='javascript:void(0);' >
                                <i style='color:#ff0000' class='fas fa-trash-alt' aria-hidden='true'></i>
                            </a>";
                }

                return [
                    "row_style" => $color,
                    "id" => $symbol,
                    "action" => $action,
                    "name" => $row->product_name,
                    "date" => $row->date ? Carbon::parse($row->date)->format('d-m-Y') : '',
                    "name_1" => $row->name_1,
                    "due_date" => Carbon::parse($row->mrn_date)->addDays($row->credited_days)->format('d-m-Y'),
                    "type" => $row->type,
                    "vehicle_no" => $row->vehicle_no,
                    "transport" => $row->transport,
                    "new_type" => $row->new_type,
                    "status" => $row->status,
                    "sale_order" => $row?->saleOrder?->vch_No,
                    "mrn_date" => $row->mrn_date,
                    "mrn_no" => $row->mrn_no,
                    "mrn_attachment" => $row->mrn_image ? "<a download='mrn_images_{$row->mrn_image}' href='" . asset('uploads/mrn_images/' . $row->mrn_image) . "' target='_blank'><i class='fas fa-download' style='color:rgb(238, 44, 44)'></i></a>" : '',
                    "document" => $row->document ? "<a download='sale_invoice_document_{$row->document}' href='" . asset('uploads/sale_invoice_document/' . $row->document) . "' target='_blank'><i class='fas fa-download' style='color:rgb(238, 44, 44)'></i></a>" : "",
                    "credited_days" => $row->credited_days,
                    "buyer" => $row->buyer?->buyerSingles?->name,
                    "workCategory" => $row->workCategory?->name,
                    "site" => $row->site?->name,
                    "vch_No" => $row->vch_No,
                    "amount" => $row->details->sum('amount') + $row->billDetails->sum('amount'),
                    "pendingAmount" => ($row->details->sum('amount') + $row->billDetails->sum('amount')) - $amount - $row->tds_amount,
                    "created_by" => $row->createdBy?->name,
                    "created_at" => $row->created_at ? $row->created_at->format('d/m/Y H:i:s') : '',
                    "updated_at" => $row->updated_at ? $row->updated_at->format('d/m/Y H:i:s') : '',
                ];
            });
        return $this->getListAjax($searchableColumns);
    }

    public function saleDataUpdate(Request $request)
    {
        $request->validate([
            "lead_delivery_date" => "sometimes|nullable|date",
            "id" => "required",
            "buyer" => "sometimes|nullable",
            "work_category" => "sometimes|nullable",
            "site" => "sometimes|nullable",
            "store_phone" => "sometimes|nullable",
            "store_email" => "sometimes|nullable",
            "store_cc_email" => "sometimes|nullable",
            "purchase_phone" => "sometimes|nullable",
            "purchase_email" => "sometimes|nullable",
            "purchase_cc_email" => "sometimes|nullable",
            "document" => "nullable|file|max:5120",
            "new_type" => "sometimes|nullable",
        ]);

        $store_email = implode(',', $request->store_email);
        $store_cc_email = implode(',', $request->store_cc_email);
        $purchase_email = implode(',', $request->purchase_email);
        $purchase_cc_email = implode(',', $request->purchase_cc_email);

        $sale = Sale::findOrFail($request->id);

        $data = [
            "store_phone" => $request->store_phone,
            "store_email" => $store_email,
            "store_cc_email" => $store_cc_email,
            "purchase_phone" => $request->purchase_phone,
            "purchase_email" => $purchase_email,
            "purchase_cc_email" => $purchase_cc_email,
            "lead_delivery_date" => $request->lead_delivery_date,
            "buyer_id" => $request->buyer,
            "work_category_id" => $request->work_category,
            "site_id" => $request->site,
            "mrn_reminder" => $request->mrn_reminder,
            "new_type" => $request->new_type,
        ];

        if ($request->hasFile('document')) {
            // Delete old image if it exists
            if (!empty($sale->document) && file_exists(public_path('uploads/sale_invoice_document/' . $sale->document))) {
                unlink(public_path('uploads/sale_invoice_document/' . $sale->document)); // Delete existing image from storage
            }

            // Save new image
            $image = $request->file('document');
            // $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $imageName = str_replace(['-', ' ', '/'], '_', $request->vch_No) . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads/sale_invoice_document/'), $imageName); // Move new image
            $data['document'] = $imageName; // Store path in database
        }

        $new_data = $data;
        unset(
            $new_data['buyer_id'],
            $new_data['work_category_id'],
            $new_data['site_id'],
            $new_data['new_type'],
            $new_data['store_phone'],
            $new_data['store_email'],
            $new_data['store_cc_email'],
            $new_data['purchase_phone'],
            $new_data['purchase_email'],
            $new_data['purchase_cc_email']
        );

        $updateData = $sale->sale_order_id > 0 ? $new_data : $data;
        $sale->update($updateData ?? []);


        $log = [
            'subject' => 'Sale',
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'ip' => $request->ip(),
            'agent' => json_encode($data),
            'table_id' => $request->id,
            'vch_no' => $request->vch_No,
            'user_id' => auth()->check() ? auth()->user()->id : 1
        ];
        LogActivity::create($log);

        if ($request->ajax()) {
            return $this->withSuccess("Info Updated successfully");
        }
        return $this->withSuccess("Info Updated successfully")->back();
    }

    public function saleMrnDataUpdate(Request $request)
    {
        $request->validate([
            "mrn_date" => "sometimes|nullable|date",
            "id" => "required",
            "mrn_image" => "nullable|file|max:5120"
        ]);

        $sale = Sale::findOrFail($request->id);

        $data = [
            "mrn_date" => $request->mrn_date,
            "mrn_no" => $request->mrn_no,
            'payment_reminder' => $request->payment_reminder,
            "credited_days" => $request->credited_days,
        ];

        if ($request->hasFile('mrn_image')) {
            // Delete old image if it exists
            if (!empty($sale->mrn_image) && file_exists(public_path('uploads/mrn_images/' . $sale->mrn_image))) {
                unlink(public_path('uploads/mrn_images/' . $sale->mrn_image)); // Delete existing image from storage
            }

            // Save new image
            $image = $request->file('mrn_image');
            $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads/mrn_images/'), $imageName); // Move new image
            $data['mrn_image'] = $imageName; // Store path in database
        }

        $sale->update($data);

        $log = [
            'subject' => 'Sale',
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'ip' => $request->ip(),
            'agent' => json_encode($data),
            'table_id' => $request->id,
            'vch_no' => $request->vch_No,
            'user_id' => auth()->check() ? auth()->user()->id : 1
        ];
        LogActivity::create($log);

        if ($request->ajax()) {
            return $this->withSuccess("MRN Updated successfully");
        }
        return $this->withSuccess("MRN Updated successfully")->back();
    }

    public function salePaymentDataUpdate(Request $request)
    {
        $request->validate([
            "id" => "required",
            "tds_category" => "sometimes|nullable",
            "tds_amount" => "sometimes|nullable",
            "amount" => "sometimes|nullable|array",
            "amount.*" => "nullable|numeric|min:0",
        ]);

        // Convert amount array into a comma-separated string
        $amounts = implode(',', $request->amount);

        $data = [
            "amount" => empty($amounts) ? null : $amounts,
            "tds_category_id" => $request->tds_category,
            "tds_amount" => $request->tds_amount,
            "payment_checkbox" => $request->payment_checkbox ?? 0,
        ];

        Sale::where('id', $request->id)->update($data);


        LogActivity::create([
            'subject' => 'Sale',
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'ip' => $request->ip(),
            'agent' => json_encode($data),
            'table_id' => $request->id,
            'vch_no' => $request->vch_No,
            'user_id' => auth()->check() ? auth()->user()->id : 1
        ]);

        if ($request->ajax()) {
            return $this->withSuccess("Payment Updated successfully");
        }
        return $this->withSuccess("Payment Updated successfully")->back();
    }

    public function saleModelData(Request $request)
    {
        $action = $request->action ?? null;
        $sales = Sale::with(['details', 'billDetails'])->find($request->id);
        $sale_returns = SaleReturn::where('sale_id', $request->id)->get()->toArray();

        $colorsdata = ['info' => '', 'mrn' => '', 'payment' => '', 'completed' => ''];

        /* info */
        if ($sales->lead_delivery_date != null || $sales->payment_checkbox == 1) {
            $colorsdata['info'] = "lightgreen";
        } else {
            $colorsdata['info'] = "lightcoral";
            if (empty($sales->sale_order_id)) {
                $colorsdata['info'] = "lightpurple";
            }
        }

        /* MRN */
        if (!empty($sales->mrn_date) || $sales->payment_checkbox == 1) {
            $colorsdata['mrn'] = "lightgreen";
        } else {
            if (!empty($sales->lead_delivery_date) && Carbon::parse($sales->lead_delivery_date) < Carbon::now()) {
                $colorsdata['mrn'] = "lightcoral";
            }
        }

        /* Payment */
        $amount = array_sum(array_map('intval', explode(',', $sales->amount)));
        if (!empty($sales->mrn_date) || $sales->payment_checkbox == 1) {
            if ($sales->payment_checkbox == 1) {
                $colorsdata['payment'] = "lightgreen";
            } else if ($sales->payment_checkbox == 0 && round($amount) < 1 && Carbon::parse($sales->mrn_date)->addDays($sales->credited_days) < Carbon::now()) {
                $colorsdata['payment'] = "lightcoral";
            } else if ($sales->payment_checkbox == 0 && $amount > 0) {
                $colorsdata['payment'] = "#ffcb72fa";
            }
        }

        if ($sales->payment_checkbox == 1) {
            $colorsdata['completed'] = 'lightgreen'; // green
        }




        return view('dashboard.sale_model', compact('sales', 'action', 'colorsdata', 'sale_returns'));
    }

    public function saleDelete(Request $request)
    {
        // Sale::with('details', 'billDetails')->where('id', $request->id)->forceDelete();

        DB::transaction(function () use ($request) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            // Delete related details records
            DB::table('sale_details')->where('sale_id', $request->id)->delete();

            // Delete related bill details records
            DB::table('sale_bill_details')->where('sale_id', $request->id)->delete();

            // Delete the sale order record
            DB::table('sales')->where('id', $request->id)->delete();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        });


        return $this->withSuccess("Data Deleted successfully");
    }

    public function purchaseDelete(Request $request)
    {
        // Purchase::with('details', 'billDetails')->where('id', $request->id)->forceDelete();

        DB::transaction(function () use ($request) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            $purchase = Purchase::with('details', 'billDetails')->findOrFail($request->id);

            // Delete related details records
            $purchase->details()->forceDelete();

            // Delete related bill details records
            $purchase->billDetails()->forceDelete();

            // Delete the purchase record
            $purchase->forceDelete();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        });


        return $this->withSuccess("Data Deleted successfully");
    }

    public function saleOrderModelData(Request $request)
    {
        $action = $request->action ?? null;
        $saleOrders = SaleOrder::with(['details', 'billDetails', 'buyer', 'sale', 'sale.saleReturns', 'workCategory'])->find($request->id);
        $saleIds = $saleOrders->sale->pluck('id')->toArray();
        $sale_returns = SaleReturn::whereIn('sale_id', $saleIds)->get()->toArray();
        return view('dashboard.sale_order_model', compact('saleOrders', 'action', 'sale_returns'));
    }

    public function getPurchaseReport(Request $request)
    {
        /* Define Searchable */

        $searchableColumns = [
            'name_1',
            'vch_No'
        ];


        $filters = [
            'party_id' => $request->party_id,
            'details:item_id' => $request->item_id,
            'new_type' => $request->new_type
        ];
        $deletePermission = $this->hasPermission("purchase-delete");

        if ($request->status == 'completed') {
            $filters['payment_checkbox'] = 1;
        }


        $modelClass = match ($request->status) {
            'info' => 'edit-btn',
            'payment' => 'payment-edit-btn',
            default => 'edit-btn',
        };
        /* Add Model here with datatablel */
        return $this->model(model: Purchase::class, with: ["createdBy", "details"])
            ->filter($filters)
            ->enableDateFilters('date')
            ->where(function ($query) use ($request) {
                if (!empty($request->color)) {
                    $color = $request->color;

                    $query->where(function ($subQuery) use ($color, $request) {
                        if ($request->status == 'info') {
                            if ($color == 'green') {
                                $subQuery->WhereNotNull('mtc_no'); // ✅ Green: mtc_no exists
                                $subQuery->WhereNotNull('credited_days');
                                $subQuery->orwhere('payment_checkbox', 1);
                            }
                            if ($color == 'red') {
                                $subQuery->WhereNull('mtc_no'); // ✅ Red: mtc_no is NULL
                                $subQuery->WhereNull('credited_days');
                                $subQuery->where('payment_checkbox', 0);
                            }
                            if ($color == 'orange') {
                                $subQuery->Where("id", 0);
                            }
                        } elseif ($request->status == 'payment') {
                            if ($color == 'green') {
                                $subQuery->orWhere(function ($q) {
                                    $q->where('payment_checkbox', 1); // ✅ Green: Payment completed
                                });
                            }
                            if ($color == 'red') {
                                $subQuery->orWhere(function ($q) {
                                    $q->whereNotNull('mtc_no')
                                        ->whereNotNull('credited_days')
                                        ->where('payment_checkbox', 0)
                                        ->whereRaw("DATE_ADD(date, INTERVAL credited_days DAY) < NOW()"); // ✅ Red: Payment overdue
                                });
                            }
                            if ($color == 'white') {
                                $subQuery->orWhere(function ($q) {
                                    $q->whereNull('mtc_no')
                                        ->whereNull('credited_days')
                                        ->where('payment_checkbox', 0); // ✅ Red: Payment overdue
                                });
                            }
                            if ($color == 'orange') {
                                $subQuery->WhereRaw("amount > 0"); // ✅ Orange: Amount is greater than 0
                                $subQuery->where('payment_checkbox', 0);
                            }
                        }
                    });
                }
                return $query;
            })
            ->orderBy("date", "DESC")
            ->formateArray(function ($row, $index) use ($request, $modelClass, $deletePermission) {
                $action = '';
                $color = '';
                $colorsdata = ['info' => 'white', 'payment' => 'white', 'completed' => 'white'];
                $amount = array_sum(array_map('intval', array: explode(',', $row->amount)));
                if ($request->status == 'info') {
                    $action = " <a class='" . $modelClass . " m-1'
                               data-id='{$row->id}'
                               data-mtc_no='{$row->mtc_no}'
                               data-credited_days='{$row->credited_days}'
                               data-new_type='{$row->new_type}'
                               data-vch-no='{$row->vch_No}'
                               data-bs-toggle='tooltip' data-bs-placement='top' data-bs-original-title='Edit' href='javascript:void(0);'>
                               <i class='far fa-edit' aria-hidden='true'></i>
                            </a>";
                    $color = 'background-color:#fb9191bf;';
                    if ((!empty($row->mtc_no) && !empty($row->credited_days)) || $row->payment_checkbox == 1) {
                        $color = 'background-color:#abedab';
                        $colorsdata['info'] = 'lightgreen';
                    } else {
                        $color = 'background-color:#fb9191bf';
                        $colorsdata['info'] = '#fb9191bf';
                    }
                } else if ($request->status == 'payment') {
                    $action = " <a class='" . $modelClass . " m-1'
                                data-id='{$row->id}'
                                data-tds_category_id='{$row->tds_category_id}'
                                data-tds_amount='{$row->tds_amount}'
                                data-amount='{$row->amount}'
                                data-payment_checkbox='{$row->payment_checkbox}'
                                data-vch-no='{$row->vch_No}'
                                data-bs-toggle='tooltip' data-bs-placement='top' data-bs-original-title='Edit' href='javascript:void(0);'>
                                <i class='far fa-edit' aria-hidden='true'></i>
                            </a>";

                    if ($row->payment_checkbox == 1) {
                        $color = 'background-color:#abedab'; // green
                        $colorsdata['payment'] = 'lightgreen'; // Light Green
                    } else if ($row->credited_days > 0 && round($amount) < 1 && Carbon::parse($row->date)->addDays($row->credited_days) < Carbon::now()) {
                        $color = 'background-color:#fb9191bf'; // red
                        $colorsdata['payment'] = 'lightcoral';
                    } else if ($amount > 0) {
                        $color = 'background-color:#ffcb72fa'; // orange
                        $colorsdata['payment'] = 'lightsalmon';
                    } else if (empty($row->credited_days) && $row->payment_checkbox == 0) {
                        $color = 'background-color:#ffffff'; // orange
                        $colorsdata['payment'] = 'white';
                    }
                }


                if ((!empty($row->mtc_no) && !empty($row->credited_days)) || $row->payment_checkbox == 1) {
                    $colorsdata['info'] = '#abedab';
                } else {
                    $colorsdata['info'] = '#fb9191bf';
                }


                if ($row->payment_checkbox == 1) {
                    $colorsdata['payment'] = '#abedab'; // Light Green
                } else if ($row->payment_checkbox == 0 && $row->credited_days > 0 && round($amount) < 1 && Carbon::parse($row->date)->addDays($row->credited_days) < Carbon::now()) {
                    $colorsdata['payment'] = '#fb9191bf';
                } else if ($row->payment_checkbox == 0 && $amount > 0) {
                    $colorsdata['payment'] = '#ffcb72fa';
                } else if (empty($row->credited_days) && $row->payment_checkbox == 0) {

                    $colorsdata['payment'] = '#ffffff';
                }


                if ($row->payment_checkbox == 1) {
                    $colorsdata['completed'] = '#abedab';
                }

                $action .= " <a class='itemModel text-white m-1'
                            data-id='{$row->id}'
                            data-bs-toggle='tooltip' data-bs-placement='top'
                            data-bs-original-title='Edit' href='javascript:void(0);'>
                            <i style='color:#007bff' class='far fa-eye' aria-hidden='true'></i>
                        </a>";

                if ($request->status == 'info' && $deletePermission) {
                    $action .= "<a class='delete-btn text-white m-1 '
                                data-id='{$row->id}'
                                data-bs-toggle='tooltip' data-bs-placement='top'
                                data-bs-original-title='Delete' href='javascript:void(0);'>
                                <i style='color:#ff0000' class='fas fa-trash-alt' aria-hidden='true'></i>
                            </a>";
                }

                $symbol = "<div style='display: flex; gap: 8px; padding: 10px;'>
                            <div data-inner='1'
                                style='width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; background-color: {$colorsdata['info']}; transition: all 0.3s ease-in-out;
                                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); font-size: 16px; font-weight: bold; cursor: pointer;'
                                onmouseover='this.style.transform=\"scale(1.2)\"; this.style.boxShadow=\"0 6px 12px rgba(0, 0, 0, 0.3)\";'
                                onmouseout='this.style.transform=\"scale(1)\"; this.style.boxShadow=\"0 4px 8px rgba(0, 0, 0, 0.2)\";'>
                                1
                            </div>
                            <div data-inner='2'
                                style='width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; background-color: {$colorsdata['payment']}; transition: all 0.3s ease-in-out;
                                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); font-size: 16px; font-weight: bold; cursor: pointer;'
                                onmouseover='this.style.transform=\"scale(1.2)\"; this.style.boxShadow=\"0 6px 12px rgba(0, 0, 0, 0.3)\";'
                                onmouseout='this.style.transform=\"scale(1)\"; this.style.boxShadow=\"0 4px 8px rgba(0, 0, 0, 0.2)\";'>
                                2
                            </div>
                            <div data-inner='3'
                                style='width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; background-color: {$colorsdata['completed']}; transition: all 0.3s ease-in-out;
                                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); font-size: 16px; font-weight: bold; cursor: pointer;'
                                onmouseover='this.style.transform=\"scale(1.2)\"; this.style.boxShadow=\"0 6px 12px rgba(0, 0, 0, 0.3)\";'
                                onmouseout='this.style.transform=\"scale(1)\"; this.style.boxShadow=\"0 4px 8px rgba(0, 0, 0, 0.2)\";'>
                                3
                            </div>
                        </div>";

                return [
                    "row_style" => $color,
                    "id" => $symbol,
                    "action" => $action,
                    "name" => $row->product_name,
                    "date" => $row->date ? Carbon::parse($row->date)->format('d-m-Y') : '',
                    "name_1" => $row->name_1,
                    "due_date" => Carbon::parse($row->date)->addDays($row->credited_days)->format('d-m-Y'),
                    "mtc_document" => $row->mtc_document ? "<a download='mtc_documents_{$row->mrn_image}' href='" . asset('uploads/mtc_documents/' . $row->mtc_document) . "' target='_blank'><i class='fas fa-download' style='color:rgb(238, 44, 44)'></i></a>" : '',
                    "type" => $row->type,
                    "new_type" => $row->new_type,
                    "status" => $row->status,
                    "mtc_no" => $row->mtc_no,
                    "credited_days" => $row->credited_days,
                    "vch_No" => $row->vch_No,
                    "amount" => $row->details->sum('amount') + $row->billDetails->sum('amount'),
                    "pendingAmount" => ($row->details->sum('amount') + $row->billDetails->sum('amount')) - $amount - $row->tds_amount,
                    "created_by" => $row->createdBy?->name,
                    "created_at" => $row->created_at ? $row->created_at->format('d/m/Y H:i:s') : '',
                    "updated_at" => $row->updated_at ? $row->updated_at->format('d/m/Y H:i:s') : '',
                ];
            })->getListAjax($searchableColumns);
    }

    public function purchaseDataUpdate(Request $request)
    {
        $request->validate([
            "mtc_no" => "sometimes|nullable|numeric",
            "mtc_document" => "nullable|file|max:5120",
            "credited_days" => "sometimes|nullable|numeric",
            "new_type" => "sometimes|nullable",
            "id" => "required",
        ]);

        $purchase = Purchase::findOrFail($request->id);

        $data = [
            "mtc_no" => $request->mtc_no,
            "credited_days" => $request->credited_days,
            "new_type" => $request->new_type,
        ];

        if ($request->hasFile('mtc_document')) {
            if (!empty($purchase->mtc_document) && file_exists(public_path('uploads/mtc_documents/' . $purchase->mtc_document))) {
                unlink(public_path('uploads/mtc_documents/' . $purchase->mtc_document)); // Delete existing image from storage
            }

            $image = $request->file('mtc_document');
            $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads/mtc_documents/'), $imageName); // Move new image
            $data['mtc_document'] = $imageName; // Store path in database
        }

        $purchase->update($data);

        $log = [
            'subject' => 'Purchase',
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'ip' => $request->ip(),
            'agent' => json_encode($data),
            'vch_no' => $request->vch_No,
            'table_id' => $request->id,
            'user_id' => auth()->check() ? auth()->user()->id : 1
        ];
        LogActivity::create($log);

        if ($request->ajax()) {
            return $this->withSuccess("Info Updated successfully");
        }
        return $this->withSuccess("Info Updated successfully")->back();
    }

    public function purchasePaymentDataUpdate(Request $request)
    {
        $request->validate([
            "id" => "required",
            "tds_category" => "sometimes|nullable",
            "tds_amount" => "sometimes|nullable",
            "amount" => "sometimes|nullable|array",
        ]);

        // Convert amount array into a comma-separated string
        $amounts = implode(',', $request->amount);

        $data = [
            "amount" => empty($amounts) ? null : $amounts,
            "tds_category_id" => $request->tds_category,
            "tds_amount" => $request->tds_amount,
            "payment_checkbox" => $request->payment_checkbox ?? 0,
        ];

        Purchase::where('id', $request->id)->update($data ?? []);

        $log = [
            'subject' => 'Purchase',
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'ip' => $request->ip(),
            'agent' => json_encode($data),
            'table_id' => $request->id,
            'vch_no' => $request->vch_No,
            'user_id' => auth()->check() ? auth()->user()->id : 1
        ];
        LogActivity::create($log);
        if ($request->ajax()) {
            return $this->withSuccess("Payment Updated successfully");
        }
        return $this->withSuccess("Payment Updated successfully")->back();
    }

    public function purchaseModelData(Request $request)
    {
        $action = $request->action ?? null;
        $purchases = Purchase::with(['details', 'billDetails'])->find($request->id);
        $purchase_returns = PurchaseReturn::where('purchase_id', $request->id)->get()->toArray();

        $colorsdata = ['info' => '', 'payment' => '', 'completed' => ''];


        $amount = array_sum(array_map('intval', explode(',', $purchases->amount)));

        if (!empty($purchases->mtc_no) && !empty($purchases->credited_days) || $purchases->payment_checkbox == 1) {
            $colorsdata['info'] = '#abedab';
        } else {
            $colorsdata['info'] = '#fb9191bf';
        }


        if ($purchases->payment_checkbox == 1) {
            $colorsdata['payment'] = '#abedab'; // Light Green
        } else if ($purchases->payment_checkbox == 0 && $purchases->credited_days > 0 && round($amount) < 1 && Carbon::parse($purchases->date)->addDays($purchases->credited_days) < Carbon::now()) {
            $colorsdata['payment'] = '#fb9191bf';
        } else if ($purchases->payment_checkbox == 0 && $amount > 0) {
            $colorsdata['payment'] = '#ffcb72fa';
        }


        if ($purchases->payment_checkbox == 1) {
            $colorsdata['completed'] = '#abedab';
        }


        return view('dashboard.purchase_model', compact('purchases', 'colorsdata', 'purchase_returns', 'action'));
    }

    public function productionSaleOrderForm(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|integer|exists:sale_orders,id',
            'remark' => 'nullable|string',
            'is_boq_status' => 'required|string|in:Pending,Partially Completed,Completed',
        ]);

        SaleOrder::where('id', $validated['id'])->update([
            'remark' => $validated['remark'],
            'is_boq_status' => $validated['is_boq_status'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Info Updated successfully'
        ]);
    }
}
