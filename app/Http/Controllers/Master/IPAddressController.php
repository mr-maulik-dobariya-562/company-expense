<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\IPAddress;
use Illuminate\Http\Request;
use App\Traits\DataTable;
use Illuminate\Routing\Controllers\Middleware;

class IPAddressController extends Controller
{

    use DataTable;


    public static function middleware(): array
    {
        return [
            new Middleware('permission:ip-create', only: ['create']),
            new Middleware('permission:ip-view', only: ['index', "getList"]),
            new Middleware('permission:ip-edit', only: ['edit', "update"]),
            new Middleware('permission:ip-delete', only: ['destroy']),
        ];
    }

    public function index()
    {
        return view('Master::ip_address.index');
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $request->validate([
            "name" => "required|unique:ip_addresses,name,NULL,id,deleted_at,NULL|ip",
        ]);

        IPAddress::create([
            "name" => $request->name,
            "created_by" => auth()->id()
        ]);

        if ($request->ajax()) {
            return $this->withSuccess("IP Address created successfully");
        }
        return $this->withSuccess("IP Address created successfully")->back();
    }

    public function show(string $id)
    {
        //
    }

    public function edit(string $id)
    {
        //
    }

    public function update(Request $request, IPAddress $ipAddress)
    {
        $request->validate([
            "name" => "required|ip|unique:ip_addresses,name," . $ipAddress->id . ",id,deleted_at,NULL",
        ]);

        try {
            $ipAddress->update([
                "name" => $request->name,
            ]);

            if ($request->ajax()) {
                return $this->withSuccess("IP Address Updated successfully");
            }
            return $this->withSuccess("IP Address Updated successfully")->back();
        } catch (\Throwable $th) {
            return $this->withError($th->getMessage());
        }
    }

    public function destroy(IPAddress $ipAddress)
    {
        $ipAddress->delete();
        if (request()->ajax()) {
            return $this->withSuccess("IP Address Deleted successfully");
        }
        return $this->withSuccess("IP Address Deleted successfully")->back();
    }

    public function getList(Request $request)
    {
        $searchableColumns = [
            'id',
            'name',
        ];

        $this->model(IPAddress::class);


        $editPermission = $this->hasPermission("ip-edit");
        $deletePermission = $this->hasPermission("ip-delete");

        $this->formateArray(function ($row, $index) use ($editPermission, $deletePermission) {
            $delete = route("master.ip-address.delete", ['ipAddress' => $row->id]);
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
                "ip_address" => $row->name,
                "action" => $action,
                "created_by" => $row->createdBy?->displayName(),
                "created_at" => $row->created_at ? $row->created_at->format('d/m/Y H:i:s') : '',
                "updated_at" => $row->updated_at ? $row->updated_at->format('d/m/Y H:i:s') : '',
            ];
        });
        return $this->getListAjax($searchableColumns);
    }
}
