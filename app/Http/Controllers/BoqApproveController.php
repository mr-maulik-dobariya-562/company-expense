<?php

namespace App\Http\Controllers;

use App\Models\Boq;
use App\Models\BoqRevision;
use App\Models\Pr;
use App\Models\PrRevision;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Traits\DataTable;
use Illuminate\Support\Facades\DB;

class BoqApproveController extends Controller
{
    use DataTable;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('boq-approve.index');
    }

    public function getUpdatingList(Request $request)
    {
        /* Define Searchable */
        $searchableColumns = [
            'status',
            'remark',
            'submittedBy:name',
            'approvedBy:name',
            'rejectedBy:name',
        ];

        /* Add Model here with relation */
        $this->model(model: BoqRevision::class, with: ["submittedBy", "approvedBy", "rejectedBy"]);

        $this->filter([
            // 'status' => 'PENDING',
        ]);

        $this->enableDateFilters('created_at');
        $this->orderBy("created_at", "DESC");

        // $deletePermission = $this->hasPermission("sale-order-delete");
        /* Add Formatting here */
        $this->formateArray(function ($row, $index) use ($searchableColumns) {

            $action = "";
            if ($row->status == 'PENDING') {
                $action .= "<a class='approveBoq text-white m-1'
                            data-id='{$row->id}'
                            data-bs-toggle='tooltip' data-bs-placement='top'
                            data-bs-original-title='Approve' href='javascript:void(0);'>
                            <i style='color:#28a745' class='fas fa-check' aria-hidden='true'></i>
                        </a>

                        <a class='rejectBoq text-white m-1'
                            data-id='{$row->id}'
                            data-bs-toggle='tooltip' data-bs-placement='top'
                            data-bs-original-title='Reject' href='javascript:void(0);'>
                            <i style='color:#dc3545' class='fas fa-times' aria-hidden='true'></i>
                        </a>";
            }

            $action .= "<a class='detailModel text-white m-1'
                            data-id='{$row->id}'
                            data-bs-toggle='tooltip' data-bs-placement='top'
                            data-bs-original-title='Edit' href='javascript:void(0);'>
                            <i style='color:#007bff' class='far fa-eye' aria-hidden='true'></i>
                        </a>";

            return [
                "id" => $row->id,
                "action" => $action,
                "status" => $row->status,
                "remark" => $row->remark,
                "submitted_by" => $row->submittedBy?->displayName(),
                "submitted_at" => $row->submitted_at ? $row->submitted_at->format('d/m/Y H:i:s') : '',
                "approved_by" => $row->approvedBy?->displayName(),
                "approved_at" => $row->approved_at ? $row->approved_at->format('d/m/Y H:i:s') : '',
                "rejected_by" => $row->rejectedBy?->displayName(),
                "rejected_at" => $row->rejected_at ? $row->rejected_at->format('d/m/Y H:i:s') : '',
                "rejection_reason" => $row->rejection_reason,
                "created_at" => $row->created_at ? $row->created_at->format('d/m/Y H:i:s') : '',
                "updated_at" => $row->updated_at ? $row->updated_at->format('d/m/Y H:i:s') : '',
            ];
        });
        return $this->getListAjax($searchableColumns);
    }

    public function boqUpdatingModelData(Request $request)
    {
        if (!$request->ajax())
            abort(404);

        $request->validate([
            'boq_revision_id' => 'required|integer|exists:boq_revisions,id',
        ]);

        $revision = BoqRevision::with([
            'boq.boqDetails',       // boq_detail
            'details',           // boq_revision_details
            'submittedBy',
            'approvedBy',
            'rejectedBy',
        ])->findOrFail($request->boq_revision_id);

        $boq = $revision->boq;

        return view('boq-approve.detail-modal', [
            'boq' => $boq,
            'boqItems' => $boq->boqDetails,      // boq_detail
            'revision' => $revision,
            'revItems' => $revision->details, // boq_revision_details
        ])->render();

    }

    public function getApprovedList(Request $request)
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

        $this->filter([
            'status' => 'APPROVED',
        ]);

        $this->enableDateFilters('created_at');
        $this->orderBy("created_at", "DESC");

        // $deletePermission = $this->hasPermission("sale-order-delete");
        /* Add Formatting here */
        $this->formateArray(function ($row, $index) use ($searchableColumns) {

            $action = "
                <a class='text-white m-1 boqApproveDetailModel'
                    data-id='{$row->id}'
                    data-bs-toggle='tooltip' data-bs-placement='top'
                    data-bs-original-title='View' href='javascript:void(0);'>
                    <i style='color:#007bff' class='far fa-eye' aria-hidden='true'></i>
                </a>

                <a class='text-white m-1 finalBoqApprove'
                    data-id='{$row->id}'
                    data-bs-toggle='tooltip' data-bs-placement='top'
                    data-bs-original-title='Approve' href='javascript:void(0);'>
                    <i style='color:#28a745' class='fas fa-check' aria-hidden='true'></i>
                </a>

                <a class='text-white m-1 finalBoqReject'
                    data-id='{$row->id}'
                    data-bs-toggle='tooltip' data-bs-placement='top'
                    data-bs-original-title='Reject' href='javascript:void(0);'>
                    <i style='color:#dc3545' class='fas fa-times' aria-hidden='true'></i>
                </a>
            ";


            return [
                "action" => $action,
                "name_1" => $row->saleOrder->name_1,
                "ref_no" => $row->ref_no,
                "remark" => $row->remark,
                "is_boq_status" => $row->is_boq_status,
                "created_by" => $row->createdBy?->displayName(),
                "created_at" => $row->created_at ? $row->created_at->format('d/m/Y H:i:s') : '',
                "updated_at" => $row->updated_at ? $row->updated_at->format('d/m/Y H:i:s') : '',
            ];
        });
        return $this->getListAjax($searchableColumns);
    }

    public function finalApprove(Request $request, $id)
    {
        $request->validate([
            'id' => 'required|integer',
        ]);

        DB::beginTransaction();
        try {

            Boq::findOrFail($id)->update([
                'is_boq_status' => 'APPROVED',
                'rejection_reason' => null, // clear if previously rejected
            ]);

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'BOQ Approved successfully.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Approval failed.',
            ], 500);
        }
    }

    public function finalReject(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:boq,id',
            'reason' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {

            Boq::findOrFail($request->id)->update([
                'is_boq_status' => 'REJECTED',
                'rejection_reason' => $request->reason ?? null,
            ]);

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'BOQ Rejected successfully.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Rejection failed.',
            ], 500);
        }
    }

    public function getPrUpdatingList(Request $request)
    {
        $searchableColumns = [
            'status',
            'date',
            'name_1',
            'vch_no',
            'remark',
        ];

        $this->model(model: PrRevision::class, with: ["submittedBy", "approvedBy", "rejectedBy", "pr"]);

        $this->enableDateFilters('date');
        $this->orderBy('date', 'DESC');

        $this->formateArray(function ($row) {
            $action = "";
            if ($row->status == 'PENDING') {
                $action .= "<a class='text-white m-1 finalPrApprove'
                        data-id='{$row->id}'
                        data-bs-toggle='tooltip' data-bs-placement='top'
                        data-bs-original-title='Approve' href='javascript:void(0);'>
                        <i style='color:#28a745' class='fas fa-check' aria-hidden='true'></i>
                    </a>

                    <a class='text-white m-1 finalPrReject'
                        data-id='{$row->id}'
                        data-bs-toggle='tooltip' data-bs-placement='top'
                        data-bs-original-title='Reject' href='javascript:void(0);'>
                        <i style='color:#dc3545' class='fas fa-times' aria-hidden='true'></i>
                    </a>
                    ";
            }

            $action .= "<a class='prDetailModel text-white m-1'
                        data-id='{$row->id}'
                        data-bs-toggle='tooltip' data-bs-placement='top'
                        data-bs-original-title='View' href='javascript:void(0);'>
                        <i style='color:#007bff' class='far fa-eye' aria-hidden='true'></i>
                    </a>";

            return [
                "action" => $action,
                "status" => $row->status,
                "date" => $row->date ? \Carbon\Carbon::parse($row->date)->format('d-m-Y') : '',
                "name_1" => $row->pr->name_1,
                "vch_no" => $row->pr->vch_no,
                "remark" => $row->remark,
                "created_by" => $row->createdBy?->displayName(),
                "created_at" => $row->created_at ? $row->created_at->format('d/m/Y H:i:s') : '',
            ];
        });

        return $this->getListAjax($searchableColumns);
    }

    public function prUpdatingModelData(Request $request)
    {
        if (!$request->ajax())
            abort(404);

        $request->validate([
            'pr_id' => 'required|integer|exists:pr,id',
        ]);

        $pr = Pr::with([
            'details',       // pr_details
            'billDetails',   // pr_bill_details
            'createdBy',
            'revisions.details',
            'revisions.billDetails',
            'revisions.submittedBy',
            'revisions.approvedBy',
            'revisions.rejectedBy',
        ])->findOrFail($request->pr_id);

        // dd($pr->toArray());

        return view('boq-approve.pr-detail-modal', [
            'pr' => $pr,
            'prItems' => $pr->details,
            'prBills' => $pr->billDetails,
            'revision' => $pr->revisions,
            'revItems' => $pr->revisions->details ?? collect(),
            'revBills' => $pr->revisions->billDetails ?? collect(),
        ])->render();
    }

    public function boqApproveDetailModelData(Request $request)
    {
        if (!$request->ajax())
            abort(404);

        $request->validate([
            'boq_id' => 'required|integer|exists:boq,id',
        ]);

        $boq = Boq::with([
            'saleOrder',
            'boqDetails',
            'createdBy',
        ])->findOrFail($request->boq_id);

        return view('boq-approve.approve-detail-modal', [
            'boq' => $boq,
            'boqItems' => $boq->boqDetails,
        ])->render();
    }

    public function getPrApprovedList(Request $request)
    {
        /* Define Searchable */
        $searchableColumns = [
            'name_1',
            'vch_no',
            'remark',
            'createdBy:name',
        ];

        /* Add Model here with relation */
        $this->model(model: Pr::class, with: ["createdBy"]);

        $this->filter([
            'status' => 'APPROVED',
        ]);

        $this->enableDateFilters('created_at');
        $this->orderBy("created_at", "DESC");

        $this->formateArray(function ($row, $index) use ($searchableColumns) {

            $action = "
                <a class='text-white m-1 PrApproveDetailModel'
                    data-id='{$row->id}'
                    data-bs-toggle='tooltip' data-bs-placement='top'
                    data-bs-original-title='View' href='javascript:void(0);'>
                    <i style='color:#007bff' class='far fa-eye' aria-hidden='true'></i>
                </a>

                <a class='text-white m-1 prApprove'
                    data-id='{$row->id}'
                    data-bs-toggle='tooltip' data-bs-placement='top'
                    data-bs-original-title='Approve' href='javascript:void(0);'>
                    <i style='color:#28a745' class='fas fa-check' aria-hidden='true'></i>
                </a>

                <a class='text-white m-1 prReject'
                    data-id='{$row->id}'
                    data-bs-toggle='tooltip' data-bs-placement='top'
                    data-bs-original-title='Reject' href='javascript:void(0);'>
                    <i style='color:#dc3545' class='fas fa-times' aria-hidden='true'></i>
                </a>
            ";


            return [
                "action" => $action,
                "is_pr_status" => $row->is_pr_status,
                "name_1" => $row->name_1,
                "vch_no" => $row->vch_no,
                "remark" => $row->remark,
                "created_by" => $row->createdBy?->displayName(),
                "created_at" => $row->created_at ? $row->created_at->format('d/m/Y H:i:s') : '',
                "updated_at" => $row->updated_at ? $row->updated_at->format('d/m/Y H:i:s') : '',
            ];
        });
        return $this->getListAjax($searchableColumns);
    }

    public function PrApprove(Request $request, $id)
    {
        $request->validate([
            'id' => 'required|integer',
        ]);

        DB::beginTransaction();
        try {

            Pr::findOrFail($id)->update([
                'is_pr_status' => 'APPROVED',
                'rejection_reason' => null, // clear if previously rejected
            ]);

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'PR Approved successfully.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Approval failed.',
            ], 500);
        }
    }

    public function PrReject(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:pr,id',
            'reason' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            Pr::findOrFail($request->id)->update([
                'is_pr_status' => 'REJECTED',
                'rejection_reason' => $request->reason ?? null,
            ]);

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'PR Rejected successfully.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Rejection failed.',
            ], 500);
        }
    }

    public function PrApproveDetailModelData(Request $request)
    {
        if (!$request->ajax())
            abort(404);

        $request->validate([
            'pr_id' => 'required|integer|exists:pr,id',
        ]);

        $pr = Pr::with([
            'details',       // pr_details
            'billDetails',   // pr_bill_details
            'createdBy',
        ])->findOrFail($request->pr_id);

        return view('boq-approve.pr-approve-detail-modal', [
            'pr' => $pr,
            'prDetails' => $pr->details,
            'prBills' => $pr->billDetails,
        ])->render();
    }
}
