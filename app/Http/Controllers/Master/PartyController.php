<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Party;
use App\Traits\DataTable;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;

class PartyController extends Controller implements HasMiddleware
{
    use DataTable;

    public static function middleware(): array
    {
        return [
            new Middleware('permission:party-create', only: ['create']),
            new Middleware('permission:party-view', only: ['index', "getList"]),
            new Middleware('permission:party-edit', only: ['edit', "update"]),
            new Middleware('permission:party-delete', only: ['destroy']),
        ];
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('Master::party.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            "name" => "required|unique:partys,name",
        ]);

        $item = Party::create([
            "name" => $request->name,
            "created_by" => auth()->id()
        ]);

        if ($request->ajax()) {
            return $this->withSuccess("Party created successfully");
        }
        return $this->withSuccess("Party created successfully")->back();
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Party $party)
    {
        $request->validate([
            "name" => "required|unique:partys,name," . $party->id,
        ]);

        try {
            $updated = $party->update([
                "name" => $request->name,
            ]);

            if (!$updated) {
                return $this->withError("Party Not Updated");
            }

            if ($request->ajax()) {
                return $this->withSuccess("Party Updated successfully,");
            }
            return $this->withSuccess("Party Updated successfully")->back();
        } catch (\Throwable $th) {
            return $this->withError($th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Party $party)
    {
        $party->delete();
        if (request()->ajax()) {
            return $this->withSuccess("Party Deleted successfully");
        }
        return $this->withSuccess("Party Deleted successfully")->back();
    }

    public function getList(Request $request)
    {
        $searchableColumns = [
            'id',
            'name',
        ];

        $this->model(model: Party::class);

        $editPermission = $this->hasPermission("party-edit");
        $deletePermission = $this->hasPermission("party-delete");

        $this->formateArray(function ($row, $index) use ($editPermission, $deletePermission) {
            $delete = route("master.party.delete", ['party' => $row->id]);
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
                $action .= "
                            <a class='btn btn-action bg-danger text-white me-2 btn-delete'
                                data-id='{$row->id}'
                                data-bs-toggle='tooltip'
                                data-bs-placement='top' data-bs-original-title='Delete'
                                href='{$delete}'>
                                <i class='fas fa-trash'></i>
                            </a>
                        ";
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
