<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BuyerSingle;
use App\Traits\DataTable;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;

class BuyerController extends Controller implements HasMiddleware
{
    use DataTable;

    public static function middleware(): array
    {
        return [
            new Middleware('permission:buyer-create', only: ['create']),
            new Middleware('permission:buyer-view', only: ['index', "getList"]),
            new Middleware('permission:buyer-edit', only: ['edit', "update"]),
            new Middleware('permission:buyer-delete', only: ['destroy']),
        ];
    }

    public function index()
    {
        return view('Master::buyer.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            "name" => "required|unique:buyer_singles,name",
        ]);

        $Buyer = BuyerSingle::create([
            "name" => $request->name,
            "created_by" => auth()->id()
        ]);

        if ($request->ajax()) {
            return $this->withSuccess("Buyer created successfully");
        }
        return $this->withSuccess("Buyer created successfully")->back();
    }

    public function update(Request $request, BuyerSingle $buyer)
    {

        $request->validate([
            "name" => "required|unique:buyer_singles,name," . $buyer->id,
        ]);

        try {
            $updated = $buyer->update([
                "name" => $request->name,
            ]);

            if (!$updated) {
                return $this->withError("Buyer Not Updated");
            }

            if ($request->ajax()) {
                return $this->withSuccess("Buyer Updated successfully,");
            }
            return $this->withSuccess("Buyer Updated successfully")->back();
        } catch (\Throwable $th) {
            return $this->withError($th->getMessage());
        }
    }

    public function destroy(BuyerSingle $buyer)
    {
        // $buyer->delete();
        // if (request()->ajax()) {
        //     return $this->withSuccess("Buyer Deleted successfully");
        // }
        return $this->withSuccess("Buyer Deleted successfully")->back();
    }

    public function getList(Request $request)
    {
        $searchableColumns = [
            'id',
            'name',
        ];

        $this->model(model: BuyerSingle::class);

        $editPermission = $this->hasPermission("buyer-edit");
        $deletePermission = $this->hasPermission("buyer-delete");

        $this->formateArray(function ($row, $index) use ($editPermission, $deletePermission) {
            $delete = route("master.buyer.delete", ['buyer' => $row->id]);
            $action = "";

            if ($editPermission) {
                $action .= "
                            <a class='btn edit-btn  btn-action bg-success text-white me-2'
                                data-id='{$row->id}'
                                data-name='{$row->name}'
                                data-bs-toggle='tooltip' data-bs-placement='top' data-bs-original-title='Edit' href='javascript:void(0);'>
                                <i class='far fa-edit' aria-hidden='true'></i>
                            </a>
                        ";
            }
            if ($deletePermission) {
                // $action .= "
                //             <a class='btn btn-action bg-danger text-white me-2 btn-delete'
                //                 data-id='{$row->id}'
                //                 data-bs-toggle='tooltip'
                //                 data-bs-placement='top' data-bs-original-title='Delete'
                //                 href='{$delete}'>
                //                 <i class='fas fa-trash'></i>
                //             </a>
                //         ";
            }

            return [
                "id" => $row->id,
                "name" => $row->name,
                "action" => $action,
                "created_by" => $row->createdBy?->displayName(),
                "created_at" => $row->created_at ? $row->created_at->format('d/m/Y H:i:s') : '',
                "updated_at" => $row->updated_at ? $row->updated_at->format('d/m/Y H:i:s') : '',
            ];
        });
        return $this->getListAjax($searchableColumns);
    }
}
