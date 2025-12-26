<?php

namespace App\Http\Controllers\Master;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\WorkCategory;
use App\Traits\DataTable;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;

class WorkCategoryController extends Controller implements HasMiddleware
{
    use DataTable;

    public static function middleware(): array
    {
        return [
            new Middleware('permission:work-category-create', only: ['create']),
            new Middleware('permission:work-category-view', only: ['index', "getList"]),
            new Middleware('permission:work-category-edit', only: ['edit', "update"]),
            new Middleware('permission:work-category-delete', only: ['destroy']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('Master::work_category.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            "name" => "required|unique:work_categories,name",
        ]);

        WorkCategory::create([
            "name" => $request->name,
            "created_by" => auth()->id()
        ]);

        if ($request->ajax()) {
            return $this->withSuccess("Work category created successfully");
        }
        return $this->withSuccess("Work category created successfully")->back();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, WorkCategory $workCategory)
    {
        $request->validate([
            "name" => "required|unique:work_categories,name," . $workCategory->id,
        ]);

        try {
            $workCategory->update([
                "name" => $request->name,
            ]);

            if ($request->ajax()) {
                return $this->withSuccess("Work category Updated successfully");
            }
            return $this->withSuccess("Work category Updated successfully")->back();
        } catch (\Throwable $th) {
            return $this->withError($th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(WorkCategory $workCategory)
    {
        // $workCategory->delete();
        // if (request()->ajax()) {
        //     return $this->withSuccess("Work category Deleted successfully");
        // }
        return $this->withSuccess("Work category Deleted successfully")->back();
    }

    public function getList(Request $request)
    {
        $searchableColumns = [
            'id',
            'name',
        ];

        $this->model(model: WorkCategory::class);


        $editPermission = $this->hasPermission("work-category-edit");
        $deletePermission = $this->hasPermission("work-category-delete");

        $this->formateArray(function ($row, $index) use ($editPermission, $deletePermission) {
            $delete = route("master.work-category.delete", ['workCategory' => $row->id]);
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
