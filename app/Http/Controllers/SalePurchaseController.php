<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Sale;
use App\Models\Party;
use App\Models\Stock;
use App\Models\Purchase;
use App\Models\Materials;
use App\Models\SaleOrder;
use App\Models\SaleReturn;
use App\Models\LogActivity;
use App\Models\StockDetail;
use App\Imports\StockImport;
use Illuminate\Http\Request;
use App\Mail\PendingInfoMail;
use App\Models\PurchaseReturn;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;

class SalePurchaseController extends Controller implements HasMiddleware
{

    public static function middleware(): array
    {
        return [
            new Middleware('permission:sale-purchase-create', only: ['create', "store"]),
            new Middleware('permission:sale-purchase-view', only: ['index', "getList"]),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('sale-purchase.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate data_type field
        if (!in_array($request->data_type, ['all_data', 'stock'])) {
            return response()->json(["success" => false, "message" => "Invalid data type selected."], 400);
        }

        // Validate the uploaded file based on the data_type
        $rules = [
            'file' => 'required|file',
        ];

        // if ($request->data_type == 'all_data') {
        //     $rules['file'] .= '|mimes:dat'; // Allow only XML and TXT files
        // } else if ($request->data_type == 'stock') {
        //     $rules['file'] .= '|mimes:csv'; // Allow only CSV files
        // }

        // Perform validation
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->withError($validator->errors(), [], 200);
        }

        if ($request->data_type == 'all_data') {
            $this->allData($request);
            return $this->withSuccess("Data Updated successfully");
        } else if ($request->data_type == 'stock') {
            $this->stockData($request);
            return $this->withSuccess("Data Updated successfully");
        }

        return $this->withSuccess("Please Select Valid Data Type");
    }

    public function stockData($request)
    {
        DB::table('stock_details')->delete();
        DB::table('stocks')->delete();

        Excel::import(new StockImport, $request->file('file'));
        $log = [
            'subject' => 'Stock',
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'ip' => $request->ip(),
            'user_id' => auth()->check() ? auth()->user()->id : 1
        ];
        LogActivity::create($log);
    }

    public function allData($request)
    {
        // Validate the file upload
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:xml,txt',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        // Read the uploaded file
        $file = $request->file('file');
        $xmlContent = File::get($file);

        // Parse the XML content
        $xmlObject = simplexml_load_string($xmlContent);
        if ($xmlObject === false) {
            return response()->json(['error' => 'Failed to parse XML'], 400);
        }

        // Convert XML to JSON and associative array
        $jsonData = json_encode($xmlObject, JSON_PRETTY_PRINT);
        $jsonArray = json_decode($jsonData, true);

        // Process sales data
        $salesData = $this->processSales($jsonArray['Sales']['Sale'] ?? []);
        $salesData = $this->filterNewEntries($salesData, 'sales');

        // Process purchase data
        $purchasesData = $this->processPurchases($jsonArray['Purcs']['Purchase'] ?? []);
        $purchasesData = $this->filterNewEntries($purchasesData, 'purchases');

        // Process sales data
        $saleOrderData = $this->processSalesOrder($jsonArray['SlOds']['SaleOrder'] ?? []);
        $saleOrderData = $this->filterNewEntries($saleOrderData, 'sale_orders');

        // Process sales return data
        $saleReturnData = $this->processSalesReturn($jsonArray['SlRts']['SaleReturn'] ?? []);
        $saleReturnData = $this->filterNewEntries($saleReturnData, 'sale_returns');

        // Process purchases return data
        $purchaseReturnData = $this->processPurchasesReturn($jsonArray['PrRts']['PurchaseReturn'] ?? []);
        $purchaseReturnData = $this->filterNewEntries($purchaseReturnData, 'purchase_returns');

        // Msterial data
        $materialsData = $this->processMaterials($jsonArray['MtIss']['MaterialIssue'] ?? []);
        $materialsData = $this->filterNewEntries($materialsData, 'materials');

        $saleOrderDetail = [];
        foreach ($saleOrderData as $saleOrder) {



            $sales = SaleOrder::create([
                'product_name' => $saleOrder['VchSeriesName'] ?? null,
                'date' => isset($saleOrder['Date']) ? date('Y-m-d', strtotime($saleOrder['Date'])) : null,

                'party_id' => $this->getPartyId(@$saleOrder['MasterName1']),
                'name_1' => $saleOrder['MasterName1'] ?? null,
                'name_2' => $saleOrder['MasterName2'] ?? null,
                'type' => $saleOrder['VchType'] ?? null,
                'vch_No' => $saleOrder['vch_No'] ?? null,
                'created_by' => auth()->user()->id
            ]);
            foreach ($saleOrder['Details'] as $detail) {
                $saleOrderDetail = [
                    'sale_order_id' => $sales->id ?? null,
                    'date' => isset($detail['Date']) ? date('Y-m-d', strtotime($detail['Date'])) : null,
                    'vch_type' => $detail['VchType'] ?? null,
                    'item_id' => Item::firstOrCreate(['name' => $detail['ItemName'] ?? null], ['name' => $detail['ItemName'] ?? null])->id,
                    'item_name' => $detail['ItemName'] ?? null,
                    'unit_name' => $detail['UnitName'] ?? null,
                    'qty' => $detail['Qty'] ?? null,
                    'qty_main_unit' => $detail['QtyMainUnit'] ?? null,
                    'qty_alt_unit' => $detail['QtyAltUnit'] ?? null,
                    'item_HSN_code' => $detail['ItemHSNCode'] ?? null,
                    'item_tax_category' => $detail['ItemTaxCategory'] ?? null,
                    'price' => $detail['Price'] ?? null,
                    'amount' => $detail['Amount'] ?? null,
                    'net_amount' => $detail['NetAmount'] ?? null,
                    'description' => is_array($detail['Description']) ? implode(', ', $detail['Description']) : $detail['Description'] ?? null,
                    'created_by' => auth()->user()->id
                ];
                $this->insertData($saleOrderDetail, 'sale_order_details');
            }

            foreach ($saleOrder['BillDetails'] as $billDetail) {
                $saleOrderBillDetail = [
                    'sale_order_id' => $sales->id ?? null,
                    'date' => isset($detail['Date']) ? date('Y-m-d', strtotime($detail['Date'])) : null,
                    'bs_name' => $billDetail['BSName'] ?? null,
                    'percent_val' => $billDetail['PercentVal'] ?? null,
                    'percent_operated_on' => $billDetail['PercentOperatedOn'] ?? null,
                    'amount' => in_array($billDetail['BSName'], ['Discount', 'Discount (FRIEGHT)', 'TDS on Pymt./Purc. of Goods']) ? -1 * abs($billDetail['Amt']) : $billDetail['Amt'] ?? null,
                    'vch_no' => $billDetail['VchNo'] ?? null,
                    'type' => $billDetail['VchType'] ?? null,
                    'tmp_vch_code' => $billDetail['tmpVchCode'] ?? null,
                    'tmp_bs_code' => $billDetail['tmpBSCode'] ?? null,
                    'created_by' => auth()->user()->id
                ];
                $this->insertData($saleOrderBillDetail, 'sale_order_bill_details');
            }
        }

        $log = [
            'subject' => 'Sale Order',
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'ip' => $request->ip(),
            'user_id' => auth()->check() ? auth()->user()->id : 1
        ];
        LogActivity::create($log);

        $materialDetail = [];
        foreach ($materialsData as $materials) {
            $refNo = "";
            if (count($materials['pendingOrder']) > 0) {
                $refNo = $materials['pendingOrder'][0]['RefNo'];
            }

            $material = Materials::create([
                'product_name' => $materials['VchSeriesName'] ?? null,
                'date' => isset($materials['Date']) ? date('Y-m-d', strtotime($materials['Date'])) : null,


                'party_id' => $this->getPartyId(@$materials['MasterName1']),
                'sale_order_id' => SaleOrder::where('vch_No', $refNo)->first()->id ?? null,
                'name_1' => $materials['MasterName1'] ?? null,
                'name_2' => $materials['MasterName2'] ?? null,
                'type' => $materials['VchType'] ?? null,
                'ref_no' => $refNo ?? null,
                'vch_No' => $materials['vch_No'] ?? null,
                'created_by' => auth()->user()->id
            ]);
            foreach ($materials['Details'] as $detail) {
                $materialDetail = [
                    'material_id' => $material->id,
                    'date' => isset($detail['Date']) ? date('Y-m-d', strtotime($detail['Date'])) : null,
                    'vch_type' => $detail['VchType'] ?? null,
                    'item_id' => Item::firstOrCreate(['name' => $detail['ItemName'] ?? null], ['name' => $detail['ItemName'] ?? null])->id,
                    'item_name' => $detail['ItemName'] ?? null,
                    'unit_name' => $detail['UnitName'] ?? null,
                    'qty' => $detail['Qty'] ?? null,
                    'qty_main_unit' => $detail['QtyMainUnit'] ?? null,
                    'qty_alt_unit' => $detail['QtyAltUnit'] ?? null,
                    'item_HSN_code' => $detail['ItemHSNCode'] ?? null,
                    'item_tax_category' => $detail['ItemTaxCategory'] ?? null,
                    'price' => $detail['Price'] ?? null,
                    'amount' => $detail['Amount'] ?? null,
                    'net_amount' => $detail['NetAmount'] ?? null,
                    'description' => is_array($detail['Description']) ? implode(', ', $detail['Description']) : $detail['Description'] ?? null,
                    'created_by' => auth()->user()->id
                ];
                $this->insertData($materialDetail, 'material_details');
            }
        }

        $log = [
            'subject' => 'Material',
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'ip' => $request->ip(),
            'user_id' => auth()->check() ? auth()->user()->id : 1
        ];
        LogActivity::create($log);

        $saleDetail = [];
        foreach ($salesData as $sale) {
            $refNo = "";
            if (count($sale['ChallanRef']) > 0) {
                $refNo = $sale['ChallanRef'][0]['RefNo'];
            }
            $mateData = Materials::where('vch_No', $refNo)->first();
            $saleOrder = SaleOrder::where('id', $mateData->sale_order_id ?? null)->first();
            if (empty($saleOrder)) {
                $saleOrder = SaleOrder::where('vch_No', $sale['VchOtherInfo']['OFInfo'] ?? null)->first();
            }
            $sales = Sale::create([
                'product_name' => $sale['VchSeriesName'] ?? null,
                'new_type' => $saleOrder->new_type ?? null,
                'date' => isset($sale['Date']) ? date('Y-m-d', strtotime($sale['Date'])) : null,
                'party_id' => $this->getPartyId(@$sale['MasterName1']),
                'name_1' => $sale['MasterName1'] ?? null,
                'name_2' => $sale['MasterName2'] ?? null,
                "store_phone" => $saleOrder->store_phone ?? null,
                "store_email" => $saleOrder->store_email ?? null,
                "store_cc_email" => $saleOrder->store_cc_email ?? null,
                "purchase_phone" => $saleOrder->purchase_phone ?? null,
                "purchase_email" => $saleOrder->purchase_email ?? null,
                "purchase_cc_email" => $saleOrder->purchase_cc_email ?? null,
                'type' => $sale['VchType'] ?? null,
                'vch_No' => $sale['vch_No'] ?? null,
                'einvi_rv' => $sale['einvi_rv'] ?? null,
                'einvi_ack_no' => $sale['einvi_ack_no'] ?? null,
                'einvi_ack_date' => $sale['einvi_ack_date'] ?? null,
                'transport' => $sale['VchOtherInfo']['Transport'] ?? null,
                'vehicle_no' => $sale['VchOtherInfo']['VehicleNo'] ?? null,
                'form31_no' => $sale['VchOtherInfo']['Form31No'] ?? null,
                'of1_info' => $sale['VchOtherInfo']['OFInfo'] ?? null,
                'ref_no' => $refNo ?? null,
                'sale_order_id' => $saleOrder->id ?? null,
                'site_id' => $saleOrder->site_id ?? null,
                'buyer_id' => $saleOrder->buyer_id ?? null,
                'work_category_id' => $saleOrder->work_category_id ?? null,
                'material_id' => $mateData->id ?? null,
                'created_by' => auth()->user()->id
            ]);

            foreach ($sale['Details'] as $detail) {
                $saleDetail = [
                    'sale_id' => $sales->id,
                    'date' => isset($detail['Date']) ? date('Y-m-d', strtotime($detail['Date'])) : null,
                    'vch_type' => $detail['VchType'] ?? null,
                    'item_id' => Item::firstOrCreate(['name' => $detail['ItemName'] ?? null], ['name' => $detail['ItemName'] ?? null])->id,
                    'item_name' => $detail['ItemName'] ?? null,
                    'unit_name' => $detail['UnitName'] ?? null,
                    'qty' => $detail['Qty'] ?? null,
                    'qty_main_unit' => $detail['QtyMainUnit'] ?? null,
                    'qty_alt_unit' => $detail['QtyAltUnit'] ?? null,
                    'item_HSN_code' => $detail['ItemHSNCode'] ?? null,
                    'item_tax_category' => $detail['ItemTaxCategory'] ?? null,
                    'price' => $detail['Price'] ?? null,
                    'amount' => $detail['Amount'] ?? null,
                    'net_amount' => $detail['NetAmount'] ?? null,
                    'description' => is_array($detail['Description']) ? implode(', ', $detail['Description']) : $detail['Description'] ?? null,
                    'created_by' => auth()->user()->id
                ];
                $this->insertData($saleDetail, 'sale_details');
            }

            foreach ($sale['BillDetails'] as $billDetail) {
                $saleBillDetail = [
                    'sale_id' => $sales->id,
                    'date' => isset($detail['Date']) ? date('Y-m-d', strtotime($detail['Date'])) : null,
                    'bs_name' => $billDetail['BSName'] ?? null,
                    'percent_val' => $billDetail['PercentVal'] ?? null,
                    'percent_operated_on' => $billDetail['PercentOperatedOn'] ?? null,
                    'amount' => in_array($billDetail['BSName'], ['Discount', 'Discount (FRIEGHT)', 'TDS on Pymt./Purc. of Goods']) ? -1 * abs($billDetail['Amt']) : $billDetail['Amt'] ?? null,
                    'vch_no' => $billDetail['VchNo'] ?? null,
                    'type' => $billDetail['VchType'] ?? null,
                    'tmp_vch_code' => $billDetail['tmpVchCode'] ?? null,
                    'tmp_bs_code' => $billDetail['tmpBSCode'] ?? null,
                    'created_by' => auth()->user()->id
                ];
                $this->insertData($saleBillDetail, 'sale_bill_details');
            }
        }

        $log = [
            'subject' => 'Sale',
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'ip' => $request->ip(),
            'user_id' => auth()->check() ? auth()->user()->id : 1
        ];
        LogActivity::create($log);

        $purchaseDetail = [];
        foreach ($purchasesData as $purchase) {
            $purchases = Purchase::create([
                'product_name' => $purchase['VchSeriesName'] ?? null,
                'date' => isset($purchase['Date']) ? date('Y-m-d', strtotime($purchase['Date'])) : null,
                'party_id' => $this->getPartyId(@$purchase['MasterName1']),
                'name_1' => $purchase['MasterName1'] ?? null,
                'name_2' => $purchase['MasterName2'] ?? null,
                'type' => $purchase['VchType'] ?? null,
                'vch_No' => $purchase['vch_No'] ?? null,
                'created_by' => auth()->user()->id
            ]);
            foreach ($purchase['Details'] as $detail) {
                $purchaseDetail = [
                    'purchase_id' => $purchases->id,
                    'date' => isset($detail['Date']) ? date('Y-m-d', strtotime($detail['Date'])) : null,
                    'vch_type' => $detail['VchType'] ?? null,
                    'item_id' => Item::firstOrCreate(['name' => $detail['ItemName'] ?? null], ['name' => $detail['ItemName'] ?? null])->id,
                    'item_name' => $detail['ItemName'] ?? null,
                    'unit_name' => $detail['UnitName'] ?? null,
                    'qty' => $detail['Qty'] ?? null,
                    'qty_main_unit' => $detail['QtyMainUnit'] ?? null,
                    'qty_alt_unit' => $detail['QtyAltUnit'] ?? null,
                    'item_HSN_code' => $detail['ItemHSNCode'] ?? null,
                    'item_tax_category' => $detail['ItemTaxCategory'] ?? null,
                    'price' => $detail['Price'] ?? null,
                    'amount' => $detail['Amount'] ?? null,
                    'net_amount' => $detail['NetAmount'] ?? null,
                    'description' => is_array($detail['Description']) ? implode(', ', $detail['Description']) : $detail['Description'] ?? null,
                    'created_by' => auth()->user()->id
                ];
                $this->insertData($purchaseDetail, 'purchase_details');
            }

            foreach ($purchase['BillDetails'] as $billDetail) {
                $purchaseBillDetail = [
                    'purchase_id' => $purchases->id,
                    'date' => isset($detail['Date']) ? date('Y-m-d', strtotime($detail['Date'])) : null,
                    'bs_name' => $billDetail['BSName'] ?? null,
                    'percent_val' => $billDetail['PercentVal'] ?? null,
                    'percent_operated_on' => $billDetail['PercentOperatedOn'] ?? null,
                    'amount' => in_array($billDetail['BSName'], ['Discount', 'Discount (FRIEGHT)', 'TDS on Pymt./Purc. of Goods']) ? -1 * abs($billDetail['Amt']) : $billDetail['Amt'] ?? null,
                    'vch_no' => $billDetail['VchNo'] ?? null,
                    'type' => $billDetail['VchType'] ?? null,
                    'tmp_vch_code' => $billDetail['tmpVchCode'] ?? null,
                    'tmp_bs_code' => $billDetail['tmpBSCode'] ?? null,
                    'created_by' => auth()->user()->id
                ];
                $this->insertData($purchaseBillDetail, 'purchase_bill_details');
            }
        }

        $log = [
            'subject' => 'Purchase',
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'ip' => $request->ip(),
            'user_id' => auth()->check() ? auth()->user()->id : 1
        ];
        LogActivity::create($log);

        $saleReturnDetail = [];
        foreach ($saleReturnData as $saleReturn) {
            $refNo = "";
            if (count($saleReturn['ChallanRef']) > 0) {
                $refNo = $saleReturn['ChallanRef'][0]['RefNo'];
            }

            $saleReturns = SaleReturn::create([
                'product_name' => $saleReturn['VchSeriesName'] ?? null,
                'date' => isset($saleReturn['Date']) ? date('Y-m-d', strtotime($saleReturn['Date'])) : null,
                'party_id' => $this->getPartyId(@$saleReturn['MasterName1']),
                'name_1' => $saleReturn['MasterName1'] ?? null,
                'name_2' => $saleReturn['MasterName2'] ?? null,
                'type' => $saleReturn['VchType'] ?? null,
                'vch_No' => $saleReturn['vch_No'] ?? null,
                'ref_no' => $refNo,
                'sale_id' => Sale::where(['vch_No' => $refNo ?? null])->first()->id ?? null,
                'created_by' => auth()->user()->id
            ]);

            foreach ($saleReturn['Details'] as $detail) {
                $saleReturnDetail = [
                    'sale_return_id' => $saleReturns->id,
                    'date' => isset($detail['Date']) ? date('Y-m-d', strtotime($detail['Date'])) : null,
                    'vch_type' => $detail['VchType'] ?? null,
                    'item_id' => Item::firstOrCreate(['name' => $detail['ItemName'] ?? null], ['name' => $detail['ItemName'] ?? null])->id,
                    'item_name' => $detail['ItemName'] ?? null,
                    'unit_name' => $detail['UnitName'] ?? null,
                    'qty' => $detail['Qty'] ?? null,
                    'qty_main_unit' => $detail['QtyMainUnit'] ?? null,
                    'qty_alt_unit' => $detail['QtyAltUnit'] ?? null,
                    'item_HSN_code' => $detail['ItemHSNCode'] ?? null,
                    'item_tax_category' => $detail['ItemTaxCategory'] ?? null,
                    'price' => $detail['Price'] ?? null,
                    'amount' => $detail['Amount'] ?? null,
                    'net_amount' => $detail['NetAmount'] ?? null,
                    'description' => is_array($detail['Description']) ? implode(', ', $detail['Description']) : $detail['Description'] ?? null,
                    'created_by' => auth()->user()->id
                ];
                $this->insertData($saleReturnDetail, 'sale_return_details');
            }

            foreach ($saleReturn['BillDetails'] as $billDetail) {
                $saleReturnBillDetail = [
                    'sale_return_id' => $saleReturns->id,
                    'date' => isset($detail['Date']) ? date('Y-m-d', strtotime($detail['Date'])) : null,
                    'bs_name' => $billDetail['BSName'] ?? null,
                    'percent_val' => $billDetail['PercentVal'] ?? null,
                    'percent_operated_on' => $billDetail['PercentOperatedOn'] ?? null,
                    'amount' => in_array($billDetail['BSName'], ['Discount', 'Discount (FRIEGHT)', 'TDS on Pymt./Purc. of Goods']) ? -1 * abs($billDetail['Amt']) : $billDetail['Amt'] ?? null,
                    'vch_no' => $billDetail['VchNo'] ?? null,
                    'type' => $billDetail['VchType'] ?? null,
                    'tmp_vch_code' => $billDetail['tmpVchCode'] ?? null,
                    'tmp_bs_code' => $billDetail['tmpBSCode'] ?? null,
                    'created_by' => auth()->user()->id
                ];
                $this->insertData($saleReturnBillDetail, 'sale_return_bill_details');
            }
        }

        $log = [
            'subject' => 'Sale Return',
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'ip' => $request->ip(),
            'user_id' => auth()->check() ? auth()->user()->id : 1
        ];
        LogActivity::create($log);

        $purchaseReturnDetail = [];
        foreach ($purchaseReturnData as $purchaseReturn) {
            $refNo = "";
            if (count($purchaseReturn['ChallanRef']) > 0) {
                $refNo = $purchaseReturn['ChallanRef'][0]['RefNo'];
            }
            $purchaseReturns = PurchaseReturn::create([
                'product_name' => $purchaseReturn['VchSeriesName'] ?? null,
                'date' => isset($purchaseReturn['Date']) ? date('Y-m-d', strtotime($purchaseReturn['Date'])) : null,
                'party_id' => $this->getPartyId(@$purchaseReturn['MasterName1']),
                'name_1' => $purchaseReturn['MasterName1'] ?? null,
                'name_2' => $purchaseReturn['MasterName2'] ?? null,
                'type' => $purchaseReturn['VchType'] ?? null,
                'vch_No' => $purchaseReturn['vch_No'] ?? null,
                'ref_no' => $refNo,
                'purchase_id' => Purchase::where(['vch_no' => $refNo ?? null])->first()->id ?? null,
                'created_by' => auth()->user()->id
            ]);

            foreach ($purchaseReturn['Details'] as $detail) {
                $purchaseReturnDetail = [
                    'purchase_return_id' => $purchaseReturns->id,
                    'date' => isset($detail['Date']) ? date('Y-m-d', strtotime($detail['Date'])) : null,
                    'vch_type' => $detail['VchType'] ?? null,
                    'item_id' => Item::firstOrCreate(['name' => $detail['ItemName'] ?? null], ['name' => $detail['ItemName'] ?? null])->id,
                    'item_name' => $detail['ItemName'] ?? null,
                    'unit_name' => $detail['UnitName'] ?? null,
                    'qty' => $detail['Qty'] ?? null,
                    'qty_main_unit' => $detail['QtyMainUnit'] ?? null,
                    'qty_alt_unit' => $detail['QtyAltUnit'] ?? null,
                    'item_HSN_code' => $detail['ItemHSNCode'] ?? null,
                    'item_tax_category' => $detail['ItemTaxCategory'] ?? null,
                    'price' => $detail['Price'] ?? null,
                    'amount' => $detail['Amount'] ?? null,
                    'net_amount' => $detail['NetAmount'] ?? null,
                    'description' => is_array($detail['Description']) ? implode(', ', $detail['Description']) : $detail['Description'] ?? null,
                    'created_by' => auth()->user()->id
                ];
                $this->insertData($purchaseReturnDetail, 'purchase_return_details');
            }

            foreach ($purchaseReturn['BillDetails'] as $billDetail) {
                $purchaseReturnBillDetail = [
                    'purchase_order_id' => $purchaseReturns->id ?? null,
                    'date' => isset($detail['Date']) ? date('Y-m-d', strtotime($detail['Date'])) : null,
                    'bs_name' => $billDetail['BSName'] ?? null,
                    'percent_val' => $billDetail['PercentVal'] ?? null,
                    'percent_operated_on' => $billDetail['PercentOperatedOn'] ?? null,
                    'amount' => in_array($billDetail['BSName'], ['Discount', 'Discount (FRIEGHT)', 'TDS on Pymt./Purc. of Goods']) ? -1 * abs($billDetail['Amt']) : $billDetail['Amt'] ?? null,
                    'vch_no' => $billDetail['VchNo'] ?? null,
                    'type' => $billDetail['VchType'] ?? null,
                    'tmp_vch_code' => $billDetail['tmpVchCode'] ?? null,
                    'tmp_bs_code' => $billDetail['tmpBSCode'] ?? null,
                    'created_by' => auth()->user()->id
                ];
                $this->insertData($purchaseReturnBillDetail, 'purchase_return_bill_details');
            }
        }

        $log = [
            'subject' => 'Purchase Return',
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'ip' => $request->ip(),
            'user_id' => auth()->check() ? auth()->user()->id : 1
        ];
        LogActivity::create($log);
    }

    private function processSales($salesData)
    {
        if (isset($salesData['VchSeriesName']) && isset($salesData['Date'])) {
            $salesData = [$salesData];
        }
        return array_map(function ($sale) {
            return [
                'VchSeriesName' => $sale['VchSeriesName'] ?? null,
                'Date' => $sale['Date'] ?? null,
                'MasterName1' => $sale['MasterName1'] ?? null,
                'MasterName2' => $sale['MasterName2'] ?? null,
                'VchType' => $sale['VchType'] ?? null,
                'vch_No' => $sale['VchNo'] ?? null,
                'einvi_rv' => $sale['EInvIRN'] ?? null,
                'einvi_ack_no' => $sale['EInvAckNo'] ?? null,
                'einvi_ack_date' => $sale['EInvAckDate'] ?? null,
                'Details' => $this->processItemDetails($sale['ItemEntries']['ItemDetail'] ?? []),
                'BillDetails' => $this->processBillSundries($sale['BillSundries']['BSDetail'] ?? []),
                'ChallanRef' => $this->processChallanRefDetails($sale['PendingChallans']['ChallanDetail'] ?? []),
                'VchOtherInfo' => $this->processVchOtherInfo($sale['VchOtherInfoDetails'] ?? []),
            ];
        }, $salesData);
    }



    private function processMaterials($materialsData)
    {
        if (isset($materialsData['VchSeriesName']) && isset($materialsData['Date'])) {
            $materialsData = [$materialsData];
        }

        return array_map(function ($material) {
            return [
                'VchSeriesName' => $material['VchSeriesName'] ?? null,
                'Date' => $material['Date'] ?? null,
                'MasterName1' => $material['MasterName1'] ?? null,
                'MasterName2' => $material['MasterName2'] ?? null,
                'VchType' => $material['VchType'] ?? null,
                'vch_No' => $material['VchNo'] ?? null,
                'Details' => $this->processItemDetails($material['ItemEntries']['ItemDetail'] ?? []),
                'pendingOrder' => $this->processPendingOrder($material['PendingOrders']['OrderDetail'] ?? []),
            ];
        }, $materialsData);
    }


    private function processPendingOrder($Pendingorders)
    {
        if (isset($Pendingorders['OrderRefs'])) {
            $Pendingorders = [$Pendingorders];
        }

        return array_map(function ($citem) {
            $PendingordersRefs = $citem['OrderRefs'] ?? null;

            if (is_array($PendingordersRefs)) {
                // Check if it's a list (indexed array) or an associative array
                if (isset($PendingordersRefs[0]) && is_array($PendingordersRefs[0])) {
                    // If it's a list, take the first element
                    $PendingordersRefs = $PendingordersRefs[0];
                }
            }

            return [
                'RefNo' => $PendingordersRefs['RefNo'] ?? null,
                'Date' => $PendingordersRefs['Date'] ?? null
            ];
        }, $Pendingorders);
    }

    private function processSalesOrder($salesData)
    {
        if (isset($salesData['VchSeriesName']) && isset($salesData['Date'])) {
            $salesData = [$salesData];
        }

        return array_map(function ($sale) {
            return [
                'VchSeriesName' => $sale['VchSeriesName'] ?? null,
                'Date' => $sale['Date'] ?? null,
                'MasterName1' => $sale['MasterName1'] ?? null,
                'MasterName2' => $sale['MasterName2'] ?? null,
                'VchType' => $sale['VchType'] ?? null,
                'vch_No' => $sale['VchNo'] ?? null,
                'Details' => $this->processItemDetails($sale['ItemEntries']['ItemDetail'] ?? []),
                'BillDetails' => $this->processBillSundries($sale['BillSundries']['BSDetail'] ?? []),
            ];
        }, $salesData);
    }

    private function processSalesReturn($salesData)
    {
        if (isset($salesData['VchSeriesName']) && isset($salesData['Date'])) {
            $salesData = [$salesData];
        }

        return array_map(function ($sale) {
            return [
                'VchSeriesName' => $sale['VchSeriesName'] ?? null,
                'Date' => $sale['Date'] ?? null,
                'MasterName1' => $sale['MasterName1'] ?? null,
                'MasterName2' => $sale['MasterName2'] ?? null,
                'VchType' => $sale['VchType'] ?? null,
                'vch_No' => $sale['VchNo'] ?? null,
                'Details' => $this->processItemDetails($sale['ItemEntries']['ItemDetail'] ?? []),
                'BillDetails' => $this->processBillSundries($sale['BillSundries']['BSDetail'] ?? []),
                'ChallanRef' => $this->processReturnChallanRefDetails($sale['PendingBillDetails']['BillDetail'] ?? []),
            ];
        }, $salesData);
    }

    private function processVchOtherInfo($vchOtherInfo)
    {
        return [
            'OFInfo' => $vchOtherInfo['OFInfo']['OF1'] ?? null,
            'Transport' => $vchOtherInfo['Transport'] ?? null,
            'VehicleNo' => $vchOtherInfo['VehicleNo'] ?? null,
            'Form31No' => $vchOtherInfo['Form31No'] ?? null,
        ];
    }

    private function processPurchasesReturn($salesData)
    {
        if (isset($salesData['VchSeriesName']) && isset($salesData['Date'])) {
            $salesData = [$salesData];
        }

        return array_map(function ($sale) {
            return [
                'VchSeriesName' => $sale['VchSeriesName'] ?? null,
                'Date' => $sale['Date'] ?? null,
                'MasterName1' => $sale['MasterName1'] ?? null,
                'MasterName2' => $sale['MasterName2'] ?? null,
                'VchType' => $sale['VchType'] ?? null,
                'vch_No' => $sale['VchNo'] ?? null,
                'Details' => $this->processItemDetails($sale['ItemEntries']['ItemDetail'] ?? []),
                'BillDetails' => $this->processBillSundries($sale['BillSundries']['BSDetail'] ?? []),
                'ChallanRef' => $this->processReturnChallanRefDetails($sale['PendingBillDetails']['BillDetail'] ?? []),
            ];
        }, $salesData);
    }

    private function processPurchases($purchaseData)
    {
        if (isset($purchaseData['VchSeriesName']) && isset($purchaseData['Date'])) {
            $purchaseData = [$purchaseData];
        }

        return array_map(function ($purchase) {
            return [
                'VchSeriesName' => $purchase['VchSeriesName'] ?? null,
                'Date' => $purchase['Date'] ?? null,
                'MasterName1' => $purchase['MasterName1'] ?? null,
                'MasterName2' => $purchase['MasterName2'] ?? null,
                'VchType' => $purchase['VchType'] ?? null,
                'vch_No' => $purchase['VchNo'] ?? null,
                'Details' => $this->processItemDetails($purchase['ItemEntries']['ItemDetail'] ?? []),
                'BillDetails' => $this->processBillSundries($purchase['BillSundries']['BSDetail'] ?? []),
            ];
        }, $purchaseData);
    }

    private function processItemDetails($itemDetails)
    {
        if (isset($itemDetails['Date'])) {
            $itemDetails = [$itemDetails];
        }

        return array_map(function ($item) {
            return [
                'Date' => $item['Date'] ?? null,
                'VchType' => $item['VchType'] ?? null,
                'ItemName' => $item['ItemName'] ?? null,
                'UnitName' => $item['UnitName'] ?? null,
                'Qty' => $item['Qty'] ?? null,
                'QtyMainUnit' => $item['QtyMainUnit'] ?? null,
                'QtyAltUnit' => $item['QtyAltUnit'] ?? null,
                'ItemHSNCode' => $item['ItemHSNCode'] ?? null,
                'ItemTaxCategory' => $item['ItemTaxCategory'] ?? null,
                'Price' => $item['Price'] ?? null,
                'Amount' => $item['Amt'] ?? null,
                'NetAmount' => $item['NettAmount'] ?? null,
                'Description' => $item['ItemDescInfo'] ?? null,
            ];
        }, $itemDetails);
    }

    private function processChallanRefDetails($PendingChallans)
    {
        if (isset($PendingChallans['ChallanRefs'])) {
            $PendingChallans = [$PendingChallans]; // Normalize single entry
        }

        return array_map(function ($citem) {
            $challanRefs = $citem['ChallanRefs'] ?? null;

            if (is_array($challanRefs)) {
                // Check if it's a list (indexed array) or an associative array
                if (isset($challanRefs[0]) && is_array($challanRefs[0])) {
                    // If it's a list, take the first element
                    $challanRefs = $challanRefs[0];
                }
            }

            return [
                'RefNo' => $challanRefs['RefNo'] ?? null,
                'Date' => $challanRefs['Date'] ?? null
            ];
        }, $PendingChallans);
    }

    private function processReturnChallanRefDetails($PendingChallans)
    {
        if (isset($PendingChallans['BillRefs'])) {
            $PendingChallans = [$PendingChallans];
        }

        return array_map(function ($citem) {
            $challanRefs = $citem['BillRefs'] ?? null;

            if (is_array($challanRefs)) {
                if (isset($challanRefs[0]) && is_array($challanRefs[0])) {
                    $challanRefs = $challanRefs[0];
                }
            }

            return [
                'RefNo' => $challanRefs['RefNo'] ?? null,
                'Date' => $challanRefs['Date'] ?? null
            ];
        }, $PendingChallans);
    }

    private function processBillSundries($bsDetails)
    {
        if (isset($bsDetails['SrNo'])) {
            $bsDetails = [$bsDetails];
        }

        return array_map(function ($bsDetail) {
            return [
                'SrNo' => $bsDetail['SrNo'] ?? null,
                'BSName' => $bsDetail['BSName'] ?? null,
                'PercentVal' => $bsDetail['PercentVal'] ?? null,
                'PercentOperatedOn' => $bsDetail['PercentOperatedOn'] ?? null,
                'Amt' => in_array($bsDetail['BSName'], ['Discount', 'Discount (FRIEGHT)', 'TDS on Pymt./Purc. of Goods']) ? -1 * $bsDetail['Amt'] : $bsDetail['Amt'] ?? null,
                'Date' => $bsDetail['Date'] ?? null,
                'VchNo' => $bsDetail['VchNo'] ?? null,
                'VchType' => $bsDetail['VchType'] ?? null,
                'tmpVchCode' => $bsDetail['tmpVchCode'] ?? null,
                'tmpBSCode' => $bsDetail['tmpBSCode'] ?? null,
            ];
        }, $bsDetails);
    }

    private function getPartyId($partyName)
    {
        if (!empty($partyName)) {
            // Try to find the party first
            $party = Party::where('name', $partyName)->first();

            // If not found, create a new one
            if (!$party) {
                $party = Party::create([
                    'name' => $partyName,
                    'created_by' => auth()->user()->id,
                ]);
            }
            return $party->id;
        } else {
            return null;
        }
    }

    private function filterNewEntries($data, $tableName, $key = 'vch_No')
    {
        // Extract all VchNo values from the current batch
        $batchKeys = collect($data)->pluck($key)->filter()->unique();

        // Query the database for existing VchNo values in this batch
        $existingKeys = DB::table($tableName)
            ->whereIn($key, $batchKeys)
            ->pluck($key)
            ->toArray();

        // Filter out entries that already exist in the database
        return collect($data)->reject(function ($entry) use ($existingKeys, $key) {
            return in_array($entry[$key] ?? null, $existingKeys);
        })->values()->toArray();
    }

    private function insertData($data, $tableName)
    {
        DB::table($tableName)->insert($data);
    }

    public function sendEmail()
    {
        $data = Sale::where('id', 100)->first();
        if (!empty($data['store_email'])) {
            Mail::to(explode(",", $data['store_email']))->send(new PendingInfoMail($data));
            return $this->withSuccess("Email sent successfully")->back();
        } else {
            return $this->withError("Email not sent")->back();
        }
    }
}
