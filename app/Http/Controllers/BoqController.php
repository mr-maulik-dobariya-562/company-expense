<?php

namespace App\Http\Controllers;

use App\Models\Boq;
use App\Models\BoqDetail;
use App\Models\BoqRevision;
use App\Models\BoqRevisionDetail;
use App\Models\Item;
use App\Models\Party;
use App\Traits\DataTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class BoqController extends Controller implements HasMiddleware
{
    use DataTable;
    public static function middleware(): array
    {
        return [
            new Middleware('permission:boq-report-view', only: ['index', "getList"]),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $partys = Party::all();
        $items = Item::all();
        return view('boq.index', compact('partys', 'items'));
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
            'saleOrder:is_boq_status' => $request->is_boq_status,
            'saleOrder:party_id' => $request->party_id
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
            switch ($row->saleOrder->is_boq_status) {
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
                "qty" => $row->boqDetails->sum('qty'),
                "is_boq_status" => $row->saleOrder->is_boq_status,
                "remark" => $row->remark,
                "row_style" => $color,
                "created_by" => $row->createdBy?->displayName(),
                "created_at" => $row->created_at ? $row->created_at->format('d/m/Y H:i:s') : '',
                "updated_at" => $row->updated_at ? $row->updated_at->format('d/m/Y H:i:s') : '',
            ];
        });
        return $this->getListAjax($searchableColumns);
    }

    public function getBoqModal(Request $request)
    {
        $details = Boq::with(['boqDetails', 'saleOrder'])->find($request->id);
        return view('boq.boq_model', compact('details'));
    }

    public function getBoqModalData(Request $request)
    {
        $items = Item::all();
        $boq = [];
        $details = [];
        if ($request->type == 'show') {
            $details = Boq::with([
                'boqDetails',
                'saleOrder',
                'revisions.details',
                'revisions.submittedBy',
                'revisions.approvedBy',
                'revisions.rejectedBy',
            ])->findOrFail($request->id);
            $boq = Boq::with('boqDetails')->find($request->saleOrderId);
        }
        return view('dashboard.boq_model', compact('boq', 'items', 'details'));
    }

    public function storeBoq(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'ref_no' => 'required|string',
            'remark' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.qty' => 'required|numeric|min:1',
        ]);


        try {

            // COUNT BOQs FOR SALE
            $boqCount = Boq::where('sale_order_id', $request->id)->count() + 1;

            // GENERATE REF NO
            $refNo = $request->ref_no . '/BOQ' . str_pad($boqCount, 2, '0', STR_PAD_LEFT);

            // CREATE BOQ
            $boq = Boq::create([
                'sale_order_id' => $request->id,
                'remark' => $request->remark,
                'ref_no' => $refNo,
                'created_by' => auth()->id(),
            ]);

            // CREATE BOQ DETAILS
            foreach ($request->items as $row) {
                BoqDetail::create([
                    'boq_id' => $boq->id,
                    'item_id' => $row['item_id'],
                    'item_name' => Item::find($row['item_id'])->name,
                    'qty' => $row['qty'],
                    'created_by' => auth()->id(),
                ]);
            }


            return response()->json([
                'status' => true,
                'message' => 'BOQ saved successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getBoqEditModal(Request $request)
    {
        $items = Item::all();
        $boq = Boq::with('boqDetails')->find($request->id);
        return view('boq.boq_edit_model', compact('boq', 'items'));
    }

    public function updateBoq(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'remark' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.qty' => 'required|numeric|min:1',
        ]);

        DB::beginTransaction();
        try {
            $boqId = $request->id;
            $boq = Boq::where('id', $boqId)->lockForUpdate()->firstOrFail();

            if ($boq->created_by !== auth()->id()) {
                DB::rollBack();
                return response()->json(['status' => false, 'message' => 'Not allowed'], 403);
            }

            // if pending revision already exists -> edit same pending
            $pendingRev = BoqRevision::where('boq_id', $boq->id)
                ->where('status', BoqRevision::STATUS_PENDING)
                ->lockForUpdate()
                ->latest('version')
                ->first();

            if ($pendingRev) {
                $rev = $pendingRev;
            } else {
                $nextVersion = (int) BoqRevision::where('boq_id', $boq->id)->max('version') + 1;

                $rev = BoqRevision::create([
                    'boq_id' => $boq->id,
                    'version' => $nextVersion,
                    'status' => BoqRevision::STATUS_PENDING,
                    'submitted_by' => auth()->id(),
                    'submitted_at' => now(),
                    'remark' => $request->remark,
                ]);
            }

            // update remark + resubmit time (important)
            $rev->update([
                'remark' => $request->remark,
                'status' => BoqRevision::STATUS_PENDING,
                'submitted_by' => auth()->id(),
                'submitted_at' => now(),

                // clear decision fields (if it was rejected and resubmitted)
                'approved_by' => null,
                'approved_at' => null,
                'rejected_by' => null,
                'rejected_at' => null,
                'rejection_reason' => null,
            ]);

            // replace revision details (NOT boq_details)
            BoqRevisionDetail::where('boq_revision_id', $rev->id)->delete();

            $itemIds = collect($request->items)->pluck('item_id')->unique()->values();
            $itemNames = Item::whereIn('id', $itemIds)->pluck('name', 'id');

            $rows = collect($request->items)->map(function ($row) use ($rev, $itemNames) {
                return [
                    'boq_revision_id' => $rev->id,
                    'item_id' => $row['item_id'],
                    'item_name' => $itemNames[$row['item_id']] ?? null,
                    'qty' => $row['qty'],
                    'created_by' => auth()->id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->all();

            BoqRevisionDetail::insert($rows);
            Boq::where('id', $boqId)->update(['updated_at' => now(), 'status' => Boq::STATUS_PENDING]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => "Submitted for approval (Revision v{$rev->version})",
                'revision_id' => $rev->id,
                'version' => $rev->version,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => 'Error', 'error' => $e->getMessage()], 500);
        }
    }

    public function approveBoq($revisionId)
    {
        DB::beginTransaction();
        try {
            $rev = BoqRevision::where('id', $revisionId)->lockForUpdate()->firstOrFail();
            $boq = Boq::where('id', $rev->boq_id)->lockForUpdate()->firstOrFail();

            if ($rev->status !== BoqRevision::STATUS_PENDING) {
                DB::rollBack();
                return response()->json([
                    'status' => false,
                    'message' => 'Only PENDING can be approved'
                ], 422);
            }

            // Load revision details
            $revDetails = BoqRevisionDetail::where('boq_revision_id', $rev->id)->get();
            if ($revDetails->count() === 0) {
                DB::rollBack();
                return response()->json([
                    'status' => false,
                    'message' => 'No revision details found to approve.'
                ], 422);
            }

            // 1) Approve revision
            $rev->update([
                'status' => BoqRevision::STATUS_APPROVED,
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            // 2) Set active revision in BOQ + (optional) update remark to approved remark
            $boq->update([
                'status' => Boq::STATUS_APPROVED,
                'active_revision_id' => $rev->id,
                'remark' => $rev->remark, // optional if boq.remark is "final remark"
            ]);

            // 3) Update FINAL boq_details from this approved revision
            BoqDetail::where('boq_id', $boq->id)->delete();

            $insertRows = $revDetails->map(function ($d) use ($boq) {
                return [
                    'boq_id' => $boq->id,
                    'item_id' => $d->item_id,
                    'item_name' => $d->item_name,
                    'qty' => $d->qty,
                    'created_by' => auth()->id(), // or $d->created_by if you want original submitter
                    'created_at' => now(),
                ];
            })->toArray();

            BoqDetail::insert($insertRows);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => "Approved (v{$rev->version}) and BOQ details updated."
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function rejectBoq(Request $request)
    {
        $request->validate(['reason' => 'required|string|max:255', 'id' => 'required|integer']);

        DB::beginTransaction();
        try {
            $revisionId = $request->id;
            $rev = BoqRevision::where('id', $revisionId)->lockForUpdate()->firstOrFail();

            if ($rev->status !== BoqRevision::STATUS_PENDING) {
                DB::rollBack();
                return response()->json([
                    'status' => false,
                    'message' => 'Only PENDING can be rejected'
                ], 422);
            }

            $rev->update([
                'status' => BoqRevision::STATUS_REJECTED,
                'rejected_by' => auth()->id(),
                'rejected_at' => now(),
                'rejection_reason' => $request->reason,
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => "Rejected (v{$rev->version}). Final BOQ data unchanged."
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function BoqForm(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|integer|exists:boq,id',
            'pr_remark' => 'nullable|string',
            'is_pr_status' => 'required|string|in:Pending,Partially Completed,Completed',
        ]);

        Boq::where('id', $validated['id'])->update([
            'pr_remark' => $validated['pr_remark'],
            'is_pr_status' => $validated['is_pr_status'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Info Updated successfully'
        ]);
    }
}
