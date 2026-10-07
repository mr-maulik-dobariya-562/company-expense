<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Customer;
use App\Models\State;
use App\Traits\DataTable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class CustomerController extends Controller implements HasMiddleware
{
    use DataTable;
    public static function middleware(): array
    {
        return [
            new Middleware('permission:customer-create', only: ['create']),
            new Middleware('permission:customer-view', only: ['index', "getList"]),
            new Middleware('permission:customer-edit', only: ['edit', "update"]),
            new Middleware('permission:customer-delete', only: ['destroy']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $partyNames = Customer::select('name','id')->get();
        $states = State::all();
        $citys = City::all();
        return view("User::customer.index", compact('partyNames', 'states', 'citys'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $parentUser = Customer::all();
        return view("User::customer.create", compact('parentUser'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'               => ['required'],
            'mobile'             => ['required', 'unique:customer,mobile', 'numeric', 'digits:10'],
            'email'              => ['required', 'unique:customer,email', 'email'],
            "status"             => ["required", "in:ACTIVE,INACTIVE"],
            "country_id"         => ["required"],
            "state_id"           => ["required"],
            "city_id"            => ["required"],
            "address"            => ["nullable"],
            "contact_person"     => ["nullable"],
            "area"            => ["nullable"],
            "pincode"            => ["required"],
            "password"           => ["required"],
            'gst'                => ['nullable', function ($attribute, $value, $fail) {
                if (!empty($value)) {
                    $pattern = '/^([0]{1}[1-9]{1}|[1-2]{1}[0-9]{1}|[3]{1}[0-7]{1})[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/';
                    if (!preg_match($pattern, $value)) {
                        $fail('The ' . $attribute . ' is not in the correct format.');
                    }
                }
            }],
            "pan_no"             => ['nullable', function ($attribute, $value, $fail) {
                if (!empty($value)) {
                    $pattern = '/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/';
                    if (!preg_match($pattern, $value)) {
                        $fail('The ' . $attribute . ' is not in the correct format.');
                    }
                }
            }],
            "discount"           => ["nullable"],
        ]);

        try {
            $validated['password']   = $validated['password'];
            $validated['created_by'] = auth()->id();
            if ($request->filled("city_id"))
                $validated['city_id'] = findOrCreate(City::class, "name", $request->input("city_id"));

            $customer = Customer::create($validated);

            if ($customer) {
                return $this->withSuccess("Customer added successfully");
            }
            return $this->withSuccess("Customer added successfully")->back();
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Customer $customer)
    {
        $parentUser = Customer::all();
        return view("User::customer.create", compact('partyTypes', 'transport', 'couriers', 'areas', 'customer', 'partyGroups', 'customerDetails', 'billGroups', 'parentUser', 'partyCategorys'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name'               => ['required'],
            'mobile'             => ['required', 'unique:customer,mobile,' . $customer->id, 'numeric', 'digits:10'],
            'email'              => ['required', 'unique:customer,email,' . $customer->id, 'email'],
            "status"             => ["required", "in:ACTIVE,INACTIVE"],
            "country_id"         => ["required"],
            "state_id"           => ["required"],
            "city_id"            => ["required"],
            "address"            => ["nullable"],
            "contact_person"     => ["nullable"],
            "area"            => ["nullable"],
            "pincode"            => ["required"],
            "gst"                => ['nullable', function ($attribute, $value, $fail) {
                if (!empty($value)) {
                    $pattern = '/^([0]{1}[1-9]{1}|[1-2]{1}[0-9]{1}|[3]{1}[0-7]{1})[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/';
                    if (!preg_match($pattern, $value)) {
                        $fail('The ' . $attribute . ' is not in the correct format.');
                    }
                }
            }],
            "pan_no"             => ['nullable', function ($attribute, $value, $fail) {
                if (!empty($value)) {
                    $pattern = '/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/';
                    if (!preg_match($pattern, $value)) {
                        $fail('The ' . $attribute . ' is not in the correct format.');
                    }
                }
            }],
            "discount"           => ["nullable"],
        ]);

        $validated['created_by'] = auth()->id();
        $validated['password']   = $request->password;
        $validated['parent_id']  = $request->parent_id ?? NULL;
        if ($request->filled("city_id"))
            $validated['city_id'] = findOrCreate(City::class, "name", $request->input("city_id"));
        $customer->update($validated);

        if ($request->ajax()) {
            return $this->withSuccess("Customer Updated successfully");
        }
        return $this->withSuccess("Customer Updated successfully")->back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        $customer->delete();
        if (request()->ajax()) {
            return $this->withSuccess("Customer Deleted successfully");
        }
        return $this->withSuccess("Customer Deleted successfully")->back();
    }

    public function getList(Request $request)
    {
        $data = Customer::with(['city', 'state', 'country', 'createdBy'])->select('customer.*');

        if ($request->status) {
            $data->where('customer.status', $request->status);
        }

        if ($request->partyId) {
            $data->where('customer.id', $request->partyId);
        }

        if ($request->city) {
            $data->whereIn('customer.city_id', $request->city);
        }

        if ($request->state) {
            $data->whereIn('customer.state_id', $request->state);
        }

        if (!empty($request->fromDate)) {
            $data->whereDate('customer.created_at', '>=', $request->fromDate);
        }
        if (!empty($request->toDate)) {
            $data->whereDate('customer.created_at', '<=', $request->toDate);
        }

        $editPermission   = $this->hasPermission("customer-edit");
        $deletePermission = $this->hasPermission("customer-delete");

        return DataTables::of($data)
            ->addColumn('checkbox', function($row) {
                return '<input type="checkbox" class="print-check" name="id[]" value="'.$row->id.'">';
            })
            ->addColumn('city', function($row) {
                return $row->city ? $row?->city?->name : 'N/A'; // Assuming 'name' is a field in the City model
            })
            ->addColumn('state', function($row) {
                return $row->state ? $row?->state?->name : 'N/A'; // Assuming 'name' is a field in the City model
            })
            ->addColumn('country', function($row) {
                return $row->country ? $row?->country?->name : 'N/A'; // Assuming 'name' is a field in the City model
            })
            ->addColumn('created_by', function($row) {
                return $row->created_by ? $row?->createdBy?->name : 'N/A'; // Assuming 'name' is a field in the City model
            })
            ->addColumn('action', function($row) use ($editPermission, $deletePermission) {
                $delete = route("customer.delete", ['customer' => $row->id]);
                $edit   = route("customer.edit", ['customer' => $row->id]);
                $action = "";
                if ($editPermission) {
                    $action .= "<a class='btn edit-btn btn-sm btn-action bg-success text-white'
                                    data-bs-toggle='tooltip'
                                    data-bs-placement='top'
                                    data-bs-original-title='Edit'
                                    href='$edit'>
                                    <i class='far fa-edit' aria-hidden='true'></i>
                                </a>";
                }

                if ($deletePermission) {
                    $action .= "<a
                                class='btn btn-action bg-danger btn-sm text-white btn-delete m-1'
                                data-bs-toggle='tooltip'
                                data-bs-placement='top'
                                data-bs-original-title='Delete'
                                href='{$delete}'>
                                <i class='fas fa-trash'></i>
                            </a>";
                }
                return $action;
            })
            ->rawColumns(['checkbox', 'action'])
            ->make(true);
    }
}
