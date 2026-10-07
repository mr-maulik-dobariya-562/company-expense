<?php

namespace App\Http\Controllers\User;

use App\Helpers\Theme;
use App\Http\Controllers\Controller;
use App\Http\Requests\RoleRequest;
use App\Traits\DataTable;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller implements HasMiddleware
{
    use DataTable;

    public static function middleware(): array
    {
        return [
            new Middleware('permission:role-create', only: ['create']),
            new Middleware('permission:role-view', only: ['index', "getList"]),
            new Middleware('permission:role-edit', only: ['edit', "update"]),
            new Middleware('permission:role-delete', only: ['destroy']),
        ];
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view("User::roles.index");
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view("User::roles.create");
    }

    public function show()
    {
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RoleRequest $request)
    {

        $validateData = $request->validated();
        $role = new Role;
        $role->name = $validateData['name'];
        $role->status = $validateData['status'];
        $role->save();
        $permissions = [];
        for ($i = 0; $i < count($request->view); $i++) {
            $data = Permission::where('name', $request->slug[$i] . "-view")->get()->first();
            if (empty($data)) {
                $data = new Permission;
                $data->name = $request->slug[$i] . "-view";
                $data->guard_name = 'web';
                $data->save();
            }


            if ($request->view[$i] == TRUE) {
                $permissions[] = $request->slug[$i] . "-view";
            }
        }
        for ($i = 0; $i < count($request->create); $i++) {
            $data = Permission::where('name', $request->slug[$i] . "-create")->get()->first();
            if (empty($data)) {
                $data = new Permission;
                $data->name = $request->slug[$i] . "-create";
                $data->guard_name = 'web';
                $data->save();
            }
            if ($request->create[$i] == TRUE) {
                $permissions[] = $request->slug[$i] . "-create";
            }
        }
        for ($i = 0; $i < count($request->edit); $i++) {
            $data = Permission::where('name', $request->slug[$i] . "-edit")->get()->first();
            if (empty($data)) {
                $data = new Permission;
                $data->name = $request->slug[$i] . "-edit";
                $data->guard_name = 'web';
                $data->save();
            }
            if ($request->edit[$i] == TRUE) {
                $permissions[] = $request->slug[$i] . "-edit";
            }
        }
        for ($i = 0; $i < count($request->delete); $i++) {
            $data = Permission::where('name', $request->slug[$i] . "-delete")->get()->first();
            if (empty($data)) {
                $data = new Permission;
                $data->name = $request->slug[$i] . "-delete";
                $data->guard_name = 'web';
                $data->save();
            }
            if ($request->delete[$i] == TRUE) {
                $permissions[] = $request->slug[$i] . "-delete";
            }
        }

        $dataArray = [
            $request->saleorderbox,
            $request->salebox,
            $request->purchasebox,
            $request->saleorderfilter,
            $request->salefilter,
            $request->saleordercolumn,
            $request->salecolumn,
            $request->sitecolumn,
            $request->exportbutton,
            $request->saleorderattachment,
            $request->saleattachment,
            $request->compulsoryfield,
            $request->productionsaleorderbox,
            $request->boqapprovereport,
            $request->boqUpdating,
            $request->prUpdating,
            $request->approveBoq,
            $request->approvePr,
        ];

        foreach ($dataArray as $value) {
            if ($value != null && !Permission::where('name', $value)->first()) {
                $data = new Permission;
                $data->name = $value;
                $data->guard_name = 'web';
                $data->save();

                $permissions[] = $value;
            }
        }

        // Permission::updateOrInsert($permissions);
        $role->syncPermissions($permissions);

        return redirect()->route('users.role.index')->with("success", "Role And Permission Created Successfully");
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role)
    {
        // $this->authorize('role-edit', $role);
        $rolePermissions = Permission::join("role_has_permissions", "role_has_permissions.permission_id", "=", "permissions.id")
            ->where("role_has_permissions.role_id", $role->id)
            ->get();
        $sidebarMenu = Theme::getMenu();

        $rolePermissionsArray = $rolePermissions->pluck('permission_id', 'name')->all();
        // Iterate through the sidebar menu
        foreach ($sidebarMenu as &$row) {
            // Check if the menu item has no children and has a URL
            if (!isset($row["children"]) && !empty($row["url"])) {
                $this->addPermissions($row, $rolePermissionsArray);
            }

            // Check if the menu item has children
            if (isset($row["children"])) {
                // Iterate through the children
                foreach ($row["children"] as &$child) {
                    $this->addPermissions($child, $rolePermissionsArray);
                    if (isset($child["children"])) {
                        // Iterate through the children
                        foreach ($child["children"] as &$subChild) {
                            $this->addPermissions($subChild, $rolePermissionsArray);
                        }
                        unset($subChild); // Unset reference to avoid issues
                    }
                }
                unset($child); // Unset reference to avoid issues
            }
        }
        unset($row);
        $permissionData = $rolePermissions->pluck('name')->all();
        return view("User::roles.create", compact('role', 'sidebarMenu', 'permissionData'));
    }

    // Helper function to add permissions to menu items
    function addPermissions(&$menuItem, $permissions)
    {
        $slug = $menuItem['menu'];
        $permissionTypes = ['view', 'create', 'edit', 'delete', 'other'];

        foreach ($permissionTypes as $type) {
            if (isset($permissions["{$slug}-{$type}"])) {
                $menuItem[$type] = 1;
            } else {
                $menuItem[$type] = 0;
            }
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(RoleRequest $request, Role $role)
    {
        $validateData = $request->validated();

        $role->name = $validateData['name'];
        $role->status = $validateData['status'];
        $role->update($validateData);
        $permissions = [];
        DB::table('role_has_permissions')->where('role_id', $role->id)->delete();
        for ($i = 0; $i < count($request->view); $i++) {
            $data = Permission::where('name', $request->slug[$i] . "-view")->get()->first();
            if (empty($data)) {
                $data = new Permission;
                $data->name = $request->slug[$i] . "-view";
                $data->guard_name = 'web';
                $data->save();
            }
            if ($request->view[$i] == "1") {
                $permissions[] = $request->slug[$i] . "-view";
            }
        }
        for ($i = 0; $i < count($request->create); $i++) {
            $data = Permission::where('name', $request->slug[$i] . "-create")->get()->first();
            if (empty($data)) {
                $data = new Permission;
                $data->name = $request->slug[$i] . "-create";
                $data->guard_name = 'web';
                $data->save();
            }
            if ($request->create[$i] == "1") {
                $permissions[] = $request->slug[$i] . "-create";
            }
        }
        for ($i = 0; $i < count($request->edit); $i++) {
            $data = Permission::where('name', $request->slug[$i] . "-edit")->get()->first();
            if (empty($data)) {
                $data = new Permission;
                $data->name = $request->slug[$i] . "-edit";
                $data->guard_name = 'web';
                $data->save();
            }
            if ($request->edit[$i] == "1") {
                $permissions[] = $request->slug[$i] . "-edit";
            }
        }
        for ($i = 0; $i < count($request->delete); $i++) {
            $data = Permission::where('name', $request->slug[$i] . "-delete")->get()->first();
            if (empty($data)) {
                $data = new Permission;
                $data->name = $request->slug[$i] . "-delete";
                $data->guard_name = 'web';
                $data->save();
            }
            if ($request->delete[$i] == "1") {
                $permissions[] = $request->slug[$i] . "-delete";
            }
        }

        $dataArray = [
            $request->saleorderbox,
            $request->salebox,
            $request->purchasebox,
            $request->saleorderfilter,
            $request->salefilter,
            $request->saleordercolumn,
            $request->salecolumn,
            $request->sitecolumn,
            $request->exportbutton,
            $request->saleorderattachment,
            $request->saleattachment,
            $request->compulsoryfield,
            $request->productionsaleorderbox,
            $request->boqapprovereport,
            $request->boqUpdating,
            $request->prUpdating,
            $request->approveBoq,
            $request->approvePr,
        ];

        foreach ($dataArray as $value) {
            if ($value != null && !Permission::where('name', $value)->first()) {
                $data = new Permission;
                $data->name = $value;
                $data->guard_name = 'web';
                $data->save();

            }
            $permissions[] = $value;
        }

        $role->syncPermissions($permissions);
        return redirect()->route('users.role.index')->with("success", "Role And Permission Update Successfully");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        // $this->authorize('role-delete', $role);

        $role->delete();
        return back()->with("success", "Role And Lermission Delete Successfully");
    }

    public function getList(Request $request)
    {
        $searchableColumns = ['id', 'name'];

        $this->model(Role::class);

        $editPermission = $this->hasPermission("role-edit");
        $deletePermission = $this->hasPermission("role-delete");

        $this->formateArray(function ($row, $index) use ($editPermission, $deletePermission) {
            $editRoute = route("users.role.edit", ['role' => $row->id]);
            $deleteRoute = route("users.role.delete", ['role' => $row->id]);
            if ($editPermission) {
                $action = "<a class='btn edit-btn  btn-action bg-success text-white me-2'
                                    data-bs-toggle='tooltip' data-bs-placement='top' data-bs-original-title='Edit' href='{$editRoute}'>
                                    <i class='far fa-edit' aria-hidden='true'></i>
                                </a>";
            }

            $status = $row->status == "active" ? "<div class='badge bg-blue-lt'>Active</div>" : "<div class='badge bg-danger-lt'>InActive</div>";
            return [
                "sr_no" => $index + 1,
                "id" => $row->id,
                "name" => $row->name,
                "status" => $status,
                "action" => $action,
            ];
        });

        return $this->getListAjax(searchableColumns: $searchableColumns);
    }
}
