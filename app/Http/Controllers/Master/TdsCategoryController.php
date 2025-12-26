<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TdsCategory;
use App\Traits\DataTable;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;

class TdsCategoryController extends Controller implements HasMiddleware
{
    use DataTable;

    public static function middleware(): array
    {
        return [
            new Middleware('permission:tds-category-create', only: ['create']),
            new Middleware('permission:tds-category-view', only: ['index', "getList"]),
            new Middleware('permission:tds-category-edit', only: ['edit', "update"]),
            new Middleware('permission:tds-category-delete', only: ['destroy']),
        ];
    }

    public function index()
    {
        return view('Master::tds_category.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            "name" => "required|unique:tds_categories,name",
        ]);

        $TdsCategory = TdsCategory::create([
            "name" => $request->name,
            "created_by" => auth()->id()
        ]);

        if ($request->ajax()) {
            return $this->withSuccess("Tds Category created successfully");
        }
        return $this->withSuccess("Tds Category created successfully")->back();
    }

    public function update(Request $request, TdsCategory $tdsCategory)
    {
        $request->validate([
            "name" => "required|unique:tds_categories,name," . $tdsCategory->id,
        ]);

        try {
            $tdsCategory->update([
                "name" => $request->name,
            ]);

            if ($request->ajax()) {
                return $this->withSuccess("Tds category Updated successfully");
            }
            return $this->withSuccess("Tds category Updated successfully")->back();
        } catch (\Throwable $th) {
            return $this->withError($th->getMessage());
        }
    }

    public function destroy(TdsCategory $tdsCategory)
    {
        // $tdsCategory->delete();
        // if (request()->ajax()) {
        //     return $this->withSuccess("Tds category Deleted successfully");
        // }
        return $this->withSuccess("Tds category Deleted successfully")->back();
    }

    public function getList(Request $request)
    {
        $searchableColumns = [
            'id',
            'name',
        ];

        $this->model(model: TdsCategory::class);


        $editPermission = $this->hasPermission("tds-category-edit");
        $deletePermission = $this->hasPermission("tds-category-delete");

        $this->formateArray(function ($row, $index) use ($editPermission, $deletePermission) {
            $delete = route("master.tds-category.delete", ['tdsCategory' => $row->id]);
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
