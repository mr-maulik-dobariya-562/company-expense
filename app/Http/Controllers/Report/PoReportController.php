<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\Pr;
use App\Models\Party;
use App\Traits\DataTable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class PoReportController extends Controller implements HasMiddleware
{
    use DataTable;
    public static function middleware(): array
    {
        return [
            new Middleware('permission:po-report-view', only: ['index', "getList"]),
        ];
    }

    public function index()
    {
        $partys = Party::all();
        return view('report.po.index', compact('partys'));
    }

    public function getList(Request $request)
    {
        /* Define Searchable */
        $searchableColumns = [];

        /* Add Model here with relation */
        $this->model(model: Pr::class, with: ["createdBy"]);

        $this->enableDateFilters('created_at');
        $this->orderBy("created_at", "DESC");

        $this->filter([
            'status' => 'APPROVED',
            'is_po_status' => $request->is_po_status,
            "party_id" => $request->party_id,
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
            switch ($row->is_po_status) {
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
                "name_1" => $row->name_1,
                "action" => $action,
                "ref_no" => $row->vch_no,
                "remark" => $row->remark,
                "is_po_status" => $row->is_po_status,
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
        $pr = PR::with(['details', 'billDetails'])->find($request->id);
        return view('report.po.pr_model', compact('pr'));
    }
}
