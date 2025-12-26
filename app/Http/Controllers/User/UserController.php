<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Location;
use App\Models\User;
use App\Traits\DataTable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;


class UserController extends Controller implements HasMiddleware
{
    use DataTable;

    public static function middleware(): array
    {
        return [
            new Middleware('permission:users-create', only: ['create']),
            new Middleware('permission:users-view', only: ['index', "getList"]),
            new Middleware('permission:users-edit', only: ['edit', "update"]),
            new Middleware('permission:users-delete', only: ['destroy']),
        ];
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $roles = Role::all();
        return view('User::index', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            "name" => "required",
            "mobile" => "required|unique:users,mobile",
            "email" => "required|unique:users,email",
            "username" => "required|unique:users,username",
            "password" => "required|min:6",
            "role" => "required",
            "status" => "required|in:ACTIVE,INACTIVE",
        ]);

        $user = User::create([
            "name" => $request->name,
            "mobile" => $request->mobile,
            "email" => $request->email,
            "username" => trim(preg_replace('/\s+/', '', $request->username)),
            "password" => bcrypt($request->password),
            "status" => $request->status,
            "created_by" => auth()->id()
        ]);

        // assign role to user
        $user->assignRole($request->role);

        if ($request->ajax()) {
            return $this->withSuccess("New User created successfully");
        }
        return $this->withSuccess("New User created successfully")->back();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            "name" => "required",
            "mobile" => "required|unique:users,mobile,$user->id",
            "email" => "required|unique:users,email,$user->id",
            "username" => "required|unique:users,username,$user->id",
            "password" => "nullable|min:6",
            "role" => "required",
            "status" => "required|in:ACTIVE,INACTIVE",
        ]);

        // assign role to user
        $user->syncRoles($request->role);

        $user->update([
            "name" => $request->name,
            "mobile" => $request->mobile,
            "email" => $request->email,
            "username" => trim(preg_replace('/\s+/', '', $request->username)),
            "password" => $request->password ? bcrypt($request->password) : $user->password,
            "status" => $request->status,
            "created_by" => auth()->id()
        ]);
        if ($request->ajax()) {
            return $this->withSuccess("User updated successfully");
        }
        return $this->withSuccess("User updated successfully")->back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();
        if (request()->ajax()) {
            return $this->withSuccess("User delete successfully");
        }
        return $this->withSuccess("User delete successfully")->back();
    }

    public function getList()
    {
        $searchableColumns = [
            'id',
            'name',
            "mobile",
            "email",
        ];

        $this->model(model: User::class, with: ["createdBy", 'roles']);

        $editPermission = $this->hasPermission("users-edit");
        $deletePermission = $this->hasPermission("users-delete");

        $this->formateArray(function (User $row, $index) use ($editPermission, $deletePermission) {
            $action = "";
            if ($editPermission) {
                $dataset = generateDataSet(collect($row->only(['id', 'name', "email", "mobile", "status"]))->merge(["role" => $row->getRoleNames()->implode("")])->toArray());
                $action .= "<a class='btn edit-btn  btn-action bg-success text-white m-1'
                        data-id='{$row->id}'
                        data-name='{$row->name}'
                        data-email='{$row->email}'
                        data-username='{$row->username}'
                        data-mobile='{$row->mobile}'
                        data-status='{$row->status}'
                        data-role='{$row->getRoleNames()->implode("")}'
                        data-bs-toggle='tooltip'
                        data-bs-placement='top'
                        data-bs-original-title='Edit'
                        href='javascript:void(0);'>
                        <i class='far fa-edit' aria-hidden='true'></i>
                    </a>";
            }
            if ($deletePermission) {
                $delete = route("users.user.delete", ['user' => $row->id]);
                $action .= "
                    <a
                        class='btn btn-action bg-danger text-white m-1 btn-delete'
                        data-bs-toggle='tooltip'
                        data-bs-placement='top'
                        data-bs-original-title='Delete'
                        href='{$delete}'>
                        <i class='fas fa-trash-alt'></i>
                    </a>";
            }
            return [
                "id" => $row->id,
                "name" => $row->name,
                "action" => $action,
                "mobile" => $row->mobile,
                "email" => $row->email,
                "username" => $row->username,
                "status" => $row->status,
                "role" => $row->getRoleNames()->implode(", "),
                "created_by" => $row->createdBy?->displayName(),
                "created_at" => $row->created_at ? $row->created_at->format('d/m/Y H:i:s') : '',
                "updated_at" => $row->updated_at ? $row->updated_at->format('d/m/Y H:i:s') : '',
            ];
        });
        return $this->getListAjax($searchableColumns);
    }
}
