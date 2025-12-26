<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\Buyer;
use App\Models\Pr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommonController extends Controller
{
    public function getSite(Request $request)
    {
        if (!$request->has('work_category_id')) {
            return response()->json([]);
        }
        $data = Site::where('work_category_id', $request->work_category_id)->get();
        return response()->json(['data' => $data, 'success' => true]);
    }

    public function getBuyer(Request $request)
    {
        if (!$request->has('site_id')) {
            return response()->json([]);
        }
        $data = Buyer::where('site_id', $request->site_id)
            ->select('buyers.*', 'buyer_singles.name')
            ->leftJoin('buyer_singles', 'buyer_singles.id', '=', 'buyers.buyer_single_id')->get();
        // dd($data);
        return response()->json(['data' => $data, 'success' => true]);
    }

    public function dataClean()
    {
        exit;
        // DB::table('sales')->delete();
        // DB::table('sale_details')->delete();
        // DB::table('sale_bill_details')->delete();

        // DB::table('purchases')->delete();
        // DB::table('purchase_details')->delete();
        // DB::table('purchase_bill_details')->delete();

        // DB::table('sale_returns')->delete();
        // DB::table('sale_return_details')->delete();

        // DB::table('purchase_returns')->delete();
        // DB::table('purchase_return_details')->delete();

        // DB::table('sale_orders')->delete();
        // DB::table('sale_order_details')->delete();
        // DB::table('sale_order_bill_details')->delete();

        // DB::table('stock_details')->delete();

        // DB::table('materials')->delete();
        // DB::table('material_details')->delete();

        // return redirect()->back();
    }

    public function logExport(Request $request)
    {
        $log = [
            'subject' => 'Export',
            'url' => $request->export_url,
            'method' => '',
            'ip' => '',
            'agent' => 'web',
            'table_id' => '',
            'vch_no' => $request->export_type,
            'user_id' => auth()->check() ? auth()->user()->id : 1
        ];

        return true;
    }

    public function availableQty(Request $request)
    {
        // Validate incoming request
        $request->validate([
            'item_id' => ['required', 'integer'],
            'boq_id' => ['required', 'integer'],
            'record_id' => ['nullable', 'integer'],  // Add a nullable record_id field to identify the editing record
        ]);

        // Calculate the available quantity
        $available = DB::table('boq_detail')
            ->where('item_id', $request->item_id)
            ->where('boq_id', $request->boq_id)
            ->whereNull('deleted_at')
            ->sum('qty');

        // Subtract already used quantity from PR details (new orders)
        $prIds = Pr::where('boq_id', $request->boq_id)->pluck('id')->toArray();
        $usedQty = DB::table('pr_details')
            ->whereIn('pr_id', $prIds)
            ->where('item_id', $request->item_id)
            ->whereNull('deleted_at')
            ->sum('qty');

        // If editing an existing record, subtract the previously entered quantity for that record
        if ($request->filled('record_id')) {
            $existingQty = DB::table('pr_details')
                ->where('id', $request->record_id)
                ->value('qty');

            // Adjust the used quantity if we're editing the same record
            $usedQty -= $existingQty;
        }

        // Subtract the used quantity from available quantity
        $available -= $usedQty;

        return response()->json([
            'available_qty' => max(0, (float) $available),  // Ensure no negative values
        ]);
    }

}
