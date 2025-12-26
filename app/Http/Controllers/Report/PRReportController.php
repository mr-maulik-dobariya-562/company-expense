<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\Boq;
use App\Models\BoqDetail;
use App\Models\Item;
use App\Models\Party;
use App\Models\Pr;
use App\Models\PrBillDetail;
use App\Models\PrDetail;
use App\Models\PrRevision;
use App\Models\PrRevisionBillDetail;
use App\Models\PrRevisionDetail;
use App\Models\Type;
use App\Traits\DataTable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;

class PRReportController extends Controller implements HasMiddleware
{
    use DataTable;
    public static function middleware(): array
    {
        return [
            new Middleware('permission:pr-report-view', only: ['index', "getList", "purchaseModelData"]),
        ];
    }

    public function index()
    {
        $items = Item::all();
        $partys = Party::all();
        return view('report.pr.index', compact('items', 'partys'));
    }

    public function store(Request $request)
    {
        if (isset($request->pr_id) && $request->pr_id != null) {
            return $this->update($request);
        } else {
            $request->validate([
                // your form should ideally send BOTH
                'boq_id' => ['required', 'integer', 'exists:boq,id'],
                'party_id' => ['required', 'integer', 'exists:partys,id'],
                'est_delivery_date' => ['required', 'date'],

                'item_id' => ['required', 'array', 'min:1'],
                'item_id.*' => ['required'],
                'qty' => ['required', 'array', 'min:1'],
                'qty.*' => ['required', 'numeric', 'gt:0'],
                'price' => ['required', 'array', 'min:1'],
                'price.*' => ['required', 'numeric', 'gt:0'],

                // totals from hidden inputs
                'sub_total' => ['required', 'numeric', 'gte:0'],
                'charges_total' => ['required', 'numeric'],
                'grand_total' => ['required', 'numeric', 'gt:0'],

                // charges rows (optional)
                'charges' => ['nullable', 'array'],
                'charges.*.name' => ['required_with:charges', 'string'],
                'charges.*.mode' => ['required_with:charges', 'in:percent,fixed'],
                'charges.*.sign' => ['nullable', 'numeric', 'in:-1,1'],
                'charges.*.percent' => ['nullable', 'numeric', 'gte:0'],
                'charges.*.amount' => ['nullable'],
                'charges.*.fixed' => ['nullable', 'numeric'],
            ]);

            $userId = auth()->id();

            return DB::transaction(function () use ($request, $userId) {
                $vchNo = BOQ::where('id', $request->boq_id)->value('ref_no');

                $prCount = Pr::where('boq_id', $request->boq_id)->count() + 1;
                $refNo = $vchNo . '/PR' . str_pad($prCount, 2, '0', STR_PAD_LEFT);
                // 1) Create PR
                $pr = Pr::create([
                    'status' => 'APPROVED',
                    'boq_id' => $request->boq_id,
                    'party_id' => $request->party_id,
                    'date' => $request->est_delivery_date,
                    'created_by' => $userId,

                    // optional fields if you have
                    'product_name' => 'Main',
                    'vch_no' => $refNo,
                    'type' => $request->type_id,
                    'remark' => $request->description,
                    'new_type' => NULL,
                    'name_1' => Party::find($request->party_id)->name,
                    'name_2' => 'Main Location',
                ]);

                // 2) Insert PR DETAILS (items)
                $itemIds = $request->item_id;
                $qtys = $request->qty;
                $prices = $request->price;

                foreach ($itemIds as $i => $itemId) {
                    $qty = (float) ($qtys[$i] ?? 0);
                    $price = (float) ($prices[$i] ?? 0);
                    $amount = $qty * $price;

                    PrDetail::create([
                        'pr_id' => $pr->id,
                        'date' => $request->est_delivery_date,
                        'vch_type' => $request->vch_type, // optional
                        'item_id' => $itemId,
                        'item_name' => Item::find($itemId)->name ?? null, // if you send it
                        'unit_name' => $request->unit_name[$i] ?? null,
                        'qty' => $qty,
                        'qty_main_unit' => $request->qty_main_unit[$i] ?? null,
                        'qty_alt_unit' => $request->qty_alt_unit[$i] ?? null,
                        'item_HSN_code' => $request->item_HSN_code[$i] ?? null,
                        'item_tax_category' => $request->item_tax_category[$i] ?? null,
                        'price' => $price,
                        'amount' => (int) round($amount),      // your table is int
                        'net_amount' => (int) round($amount),  // keep same (or set later)
                        'description' => $request->item_description[$i] ?? null,
                        'created_by' => $userId,
                    ]);
                }

                // 3) Insert BILL DETAILS (GST/charges)
                // NOTE: unique key (pr_id, bs_name) => use updateOrCreate
                $charges = $request->input('charges', []);

                foreach ($charges as $c) {
                    $bsName = trim($c['name'] ?? '');
                    if ($bsName === '')
                        continue;

                    $mode = $c['mode'] ?? 'percent';
                    $sign = (float) ($c['sign'] ?? 1);

                    $percent = (float) ($c['percent'] ?? 0);
                    $subTotal = (float) $request->sub_total;

                    // amount: percent-based OR fixed
                    if ($mode === 'fixed') {
                        $amt = ((float) ($c['fixed'] ?? 0)) * $sign;
                    } else {
                        // if your UI sends amount readonly you can use that too
                        $amt = ($subTotal * $percent / 100) * $sign;
                    }
                    if ($percent > 0 || $amt > 0) {
                        PrBillDetail::updateOrCreate(
                            ['pr_id' => $pr->id, 'bs_name' => $bsName],
                            [
                                'percent_val' => $mode === 'percent' ? (string) $percent : null,
                                'percent_operated_on' => (string) $subTotal,
                                'amount' => number_format($amt, 2, '.', ''),
                                'date' => $request->est_delivery_date,
                                'vch_no' => $request->vch_No,
                                'type' => $mode, // store mode here
                                'tmp_vch_code' => null,
                                'tmp_bs_code' => null,
                                'created_by' => $userId,
                            ]
                        );
                    }
                }

                // (Optional) You can also store totals as bill_details lines:
                // Sub Total / Grand Total (if you want). Otherwise keep in request only.

                return response()->json([
                    'status' => true,
                    'message' => 'PR created successfully.',
                    'pr_id' => $pr->id,
                ]);
            });
        }
    }

    public function getList(Request $request)
    {
        /* Define Searchable */
        $searchableColumns = [
            'saleOrder:name_1',
            'ref_no',
            'remark',
            'createdBy:name',
        ];

        /* Add Model here with relation */
        $this->model(model: Boq::class, with: ["createdBy", "saleOrder"]);

        $this->enableDateFilters('created_at');
        $this->orderBy("created_at", "DESC");

        $this->filter([
            'status' => 'APPROVED',
            'is_pr_status' => $request->is_pr_status,
            "saleOrder:party_id" => $request->party_id,
        ]);

        // $deletePermission = $this->hasPermission("sale-order-delete");
        /* Add Formatting here */
        $this->formateArray(function ($row, $index) use ($searchableColumns) {

            $action = "<a class='detailModel text-white m-1'
                            data-id='{$row->id}'
                            data-bs-toggle='tooltip' data-bs-placement='top'
                            data-bs-original-title='Edit' href='javascript:void(0);'>
                            <i style='color:#007bff' class='far fa-eye' aria-hidden='true'></i>
                        </a>";

            $color = '';
            switch ($row->is_pr_status) {
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

            return [
                "id" => $row->id,
                "name_1" => $row->saleOrder->name_1,
                "action" => $action,
                "ref_no" => $row->ref_no,
                "remark" => $row->remark,
                "is_pr_status" => $row->is_pr_status,
                "row_style" => $color,
                "created_by" => $row->createdBy?->displayName(),
                "created_at" => $row->created_at ? $row->created_at->format('d/m/Y H:i:s') : '',
                "updated_at" => $row->updated_at ? $row->updated_at->format('d/m/Y H:i:s') : '',
            ];
        });
        return $this->getListAjax($searchableColumns);
    }

    public function modelData(Request $request)
    {
        $details = Boq::with(['boqDetails', 'saleOrder'])->find($request->id);
        $prs = Pr::where('boq_id', $request->id)->get();
        return view('report.pr.boq_model', compact('details', 'prs'));
    }

    public function prModelData(Request $request)
    {
        $items = BoqDetail::where('boq_id', $request->id)->get();
        $partys = Party::select('id', 'name')->get();
        $gstTypes = DB::table('sale_order_bill_details')
            ->select(DB::raw('bs_name'))
            ->groupBy('bs_name')
            ->get();
        $types = Type::all();
        $units = DB::table('sale_order_details')
            ->select(DB::raw('unit_name'))
            ->groupBy('unit_name')
            ->get();
        return view('report.pr.pr_model', compact('items', 'partys', 'gstTypes', 'types', 'units'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'pr_id' => ['required', 'integer', 'exists:pr,id'],

            // keep same as store
            'party_id' => ['required', 'integer', 'exists:partys,id'],
            'est_delivery_date' => ['required', 'date'],

            'item_id' => ['required', 'array', 'min:1'],
            'item_id.*' => ['required'],
            'qty' => ['required', 'array', 'min:1'],
            'qty.*' => ['required', 'numeric', 'gt:0'],
            'price' => ['required', 'array', 'min:1'],
            'price.*' => ['required', 'numeric', 'gt:0'],

            // totals from hidden inputs (same as store)
            'sub_total' => ['required', 'numeric', 'gte:0'],
            'charges_total' => ['nullable', 'numeric'],
            'grand_total' => ['required', 'numeric', 'gt:0'],

            // charges rows (same as store)
            'charges' => ['nullable', 'array'],
            'charges.*.name' => ['required_with:charges', 'string'],
            'charges.*.mode' => ['required_with:charges', 'in:percent,fixed'],
            'charges.*.sign' => ['nullable', 'numeric', 'in:-1,1'],
            'charges.*.percent' => ['nullable', 'numeric', 'gte:0'],
            'charges.*.fixed' => ['nullable', 'numeric', 'gte:0'],
            'charges.*.amount' => ['nullable'], // UI can send, but we’ll recompute
        ]);

        return DB::transaction(function () use ($request) {
            $userId = auth()->id();

            $pr = Pr::where('id', $request->pr_id)->lockForUpdate()->firstOrFail();

            if ((int) $pr->created_by !== (int) $userId) {
                return response()->json(['status' => false, 'message' => 'Not allowed'], 403);
            }

            // 1) Pending revision? use it, else create new version
            $rev = PrRevision::where('pr_id', $pr->id)
                ->where('status', 'PENDING')
                ->lockForUpdate()
                ->latest('version')
                ->first();

            if (!$rev) {
                $nextVersion = ((int) PrRevision::where('pr_id', $pr->id)->max('version')) + 1;

                $rev = PrRevision::create([
                    'pr_id' => $pr->id,
                    'version' => $nextVersion,
                    'status' => 'PENDING',
                    'submitted_by' => $userId,
                    'submitted_at' => now(),
                ]);
            }

            // 2) Update revision header (same fields you used in store)
            $rev->update([
                'status' => 'PENDING',
                'submitted_by' => $userId,
                'submitted_at' => now(),

                'remark' => $request->description,
                'party_id' => $request->party_id,
                'boq_id' => $request->boq_id,
                'type' => $request->type_id,
                'date' => $request->est_delivery_date,

                // clear decision fields
                'approved_by' => null,
                'approved_at' => null,
                'rejected_by' => null,
                'rejected_at' => null,
                'rejection_reason' => null,
            ]);

            // 3) Replace revision details (delete + insert)
            PrRevisionDetail::where('pr_revision_id', $rev->id)->delete();
            PrRevisionBillDetail::where('pr_revision_id', $rev->id)->delete();

            $itemIds = $request->item_id;
            $qtys = $request->qty;
            $prices = $request->price;

            // optimize: fetch names once
            $itemNameMap = Item::whereIn('id', $itemIds)->pluck('name', 'id'); // [id => name]

            $detailRows = [];
            foreach ($itemIds as $i => $itemId) {
                $qty = (float) ($qtys[$i] ?? 0);
                $price = (float) ($prices[$i] ?? 0);
                $amt = $qty * $price;

                $detailRows[] = [
                    'pr_revision_id' => $rev->id,
                    'item_id' => $itemId,
                    'item_name' => $itemNameMap[$itemId] ?? null,
                    'qty' => $qty,
                    'price' => $price,
                    'amount' => $amt,
                    'net_amount' => $amt,
                    'unit_name' => $request->unit_name[$i] ?? null,
                    'item_HSN_code' => $request->item_HSN_code[$i] ?? null,
                    'description' => $request->item_description[$i] ?? null,
                    'created_by' => $userId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (!empty($detailRows)) {
                PrRevisionDetail::insert($detailRows);
            }

            // 4) Insert revision bill details (same logic as store)
            $charges = $request->input('charges', []);
            $subTotal = (float) $request->sub_total;

            $billRows = [];
            foreach ($charges as $c) {
                $bsName = trim($c['name'] ?? '');
                if ($bsName === '') {
                    continue;
                }

                $mode = $c['mode'] ?? 'percent';
                $sign = (float) ($c['sign'] ?? 1);

                $percent = (float) ($c['percent'] ?? 0);

                if ($mode === 'fixed') {
                    $amt = ((float) ($c['fixed'] ?? 0)) * $sign;
                    $percentVal = null;
                } else {
                    $amt = ($subTotal * $percent / 100) * $sign;
                    $percentVal = (string) $percent;
                }

                // store only meaningful lines
                if (abs($amt) < 0.00001 && $percent <= 0) {
                    continue;
                }

                $billRows[] = [
                    'pr_revision_id' => $rev->id,
                    'bs_name' => $bsName,
                    'percent_val' => $percentVal,
                    'percent_operated_on' => (string) $subTotal,
                    'amount' => number_format($amt, 2, '.', ''),
                    'created_by' => $userId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (!empty($billRows)) {
                PrRevisionBillDetail::insert($billRows);
            }

            // 5) PR status pending because revision submitted
            $pr->update(['status' => 'PENDING']);

            return response()->json([
                'status' => true,
                'message' => "Submitted for approval (Revision v{$rev->version})",
                'pr_id' => $pr->id,
                'revision_id' => $rev->id,
                'version' => $rev->version,
            ]);
        });
    }

    public function approvePr(Request $request)
    {
        DB::beginTransaction();
        try {
            $revisionId = $request->id;
            $rev = PrRevision::where('pr_id', $revisionId)->where('status', 'PENDING')->lockForUpdate()->firstOrFail();
            $pr = Pr::where('id', $rev->pr_id)->lockForUpdate()->firstOrFail();

            if ($rev->status !== 'PENDING') {
                DB::rollBack();
                return response()->json(['status' => false, 'message' => 'Only PENDING can be approved'], 422);
            }

            $revDetails = PrRevisionDetail::where('pr_revision_id', $rev->id)->get();
            if ($revDetails->count() === 0) {
                DB::rollBack();
                return response()->json(['status' => false, 'message' => 'No revision details found to approve.'], 422);
            }

            // 1) approve revision
            $rev->update([
                'status' => 'APPROVED',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            // 2) update PR header (apply revision header)
            $pr->update([
                'status' => 'APPROVED', // or 'APPROVED' if you add enum
                'party_id' => $rev->party_id,
                'boq_id' => $rev->boq_id,
                'date' => $rev->date,
            ]);

            // 3) update FINAL pr_details from this approved revision
            DB::table('pr_details')->where('pr_id', $pr->id)->delete();

            $finalRows = $revDetails->map(function ($d) use ($pr) {
                return [
                    'pr_id' => $pr->id,
                    'date' => $pr->date,
                    'vch_type' => null,
                    'item_id' => $d->item_id,
                    'item_name' => $d->item_name,
                    'unit_name' => $d->unit_name,
                    'item_HSN_code' => $d->item_HSN_code,
                    'qty' => $d->qty,
                    'price' => (int) round($d->price),
                    'amount' => (int) round($d->amount),
                    'net_amount' => (int) round($d->net_amount ?? $d->amount),
                    'description' => $d->description,
                    'created_by' => auth()->id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->toArray();

            DB::table('pr_details')->insert($finalRows);

            // 4) update FINAL pr_bill_details from revision bill details
            DB::table('pr_bill_details')->where('pr_id', $pr->id)->delete();

            $revBills = PrRevisionBillDetail::where('pr_revision_id', $rev->id)->get();

            $billInsert = $revBills->map(function ($b) use ($pr) {
                return [
                    'pr_id' => $pr->id,
                    'bs_name' => $b->bs_name,
                    'percent_val' => $b->percent_val,
                    'percent_operated_on' => $b->percent_operated_on,
                    'amount' => $b->amount,
                    'date' => $pr->date,
                    'vch_no' => $pr->vch_No,
                    'type' => $b->percent_val !== null ? 'percent' : 'fixed',
                    'tmp_vch_code' => null,
                    'tmp_bs_code' => null,
                    'created_by' => auth()->id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->toArray();

            if (!empty($billInsert)) {
                DB::table('pr_bill_details')->insert($billInsert);
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => "Approved (v{$rev->version}) and PR final details updated."
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => 'Error', 'error' => $e->getMessage()], 500);
        }
    }

    public function rejectPr(Request $request)
    {
        $request->validate([
            'id' => ['required', 'integer'],        // revisionId
            'reason' => ['required', 'string', 'max:255'],
        ]);

        DB::beginTransaction();
        try {
            $rev = PrRevision::where('pr_id', $request->id)->where('status', 'PENDING')->lockForUpdate()->firstOrFail();

            if ($rev->status !== 'PENDING') {
                DB::rollBack();
                return response()->json(['status' => false, 'message' => 'Only PENDING can be rejected'], 422);
            }

            $rev->update([
                'status' => 'REJECTED',
                'rejected_by' => auth()->id(),
                'rejected_at' => now(),
                'rejection_reason' => $request->reason,
            ]);

            $pr = Pr::where('id', $rev->pr_id)->lockForUpdate()->firstOrFail();
            $pr->update([
                'status' => 'REJECTED', // or 'PENDING' if you add enum
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => "Rejected (v{$rev->version}). Final PR data unchanged."
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => 'Error', 'error' => $e->getMessage()], 500);
        }
    }

    public function prEditModelData(Request $request)
    {
        $partys = Party::select('id', 'name')->get();
        $gstTypes = DB::table('sale_order_bill_details')
            ->select(DB::raw('bs_name'))
            ->groupBy('bs_name')
            ->get();
        $types = Type::all();
        $pr = Pr::with('details', 'billDetails')->where('id', $request->id)->first();
        $items = BoqDetail::where('boq_id', $pr->boq_id)->get();
        $units = DB::table('sale_order_details')
            ->select(DB::raw('unit_name'))
            ->groupBy('unit_name')
            ->get();
        return view('report.pr.pr_edit_model', compact('items', 'partys', 'gstTypes', 'types', 'pr', 'units'));
    }

    public function prForm(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|integer|exists:pr,id',
            'remark' => 'nullable|string',
            'is_po_status' => 'required|string|in:Pending,Partially Completed,Completed',
        ]);

        Pr::where('id', $validated['id'])->update([
            'po_remark' => $validated['remark'],
            'is_po_status' => $validated['is_po_status'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Info Updated successfully'
        ]);
    }
}
