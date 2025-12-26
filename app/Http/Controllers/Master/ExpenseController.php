<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Traits\DataTable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;

class ExpenseController extends Controller implements HasMiddleware
{
    use DataTable;

    public static function middleware(): array
    {
        return [
            new Middleware('permission:expense-create', only: ['create']),
            new Middleware('permission:expense-view', only: ['index', "getList"]),
            new Middleware('permission:expense-edit', only: ['edit', "update"]),
            new Middleware('permission:expense-delete', only: ['destroy']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('Master::expense.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            "amount" => "required",
            "date" => "required",
            "description" => "required",
        ]);

        Expense::create([
            "amount" => $request->amount,
            "date" => $request->date,
            "payment_type" => (auth()->user()->role == 'Admin') ? $request->payment_type : 'DEBIT',
            "pay_status" => (auth()->user()->role == 'Admin') ? $request->pay_status : '0',
            "description" => $request->description,
            "created_by" => auth()->id()
        ]);

        if ($request->ajax()) {
            return $this->withSuccess("Expense created successfully");
        }
        return $this->withSuccess("Expense created successfully")->back();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Expense $expense)
    {
        $request->validate([
            "amount" => "required",
            "date" => "required",
            "description" => "required",
        ]);

        try {
            $expense->update([
                "amount" => $request->amount,
                "date" => $request->date,
                "payment_type" => (auth()->user()->role == 'Admin') ? $request->payment_type : 'DEBIT',
                "pay_status" => (auth()->user()->role == 'Admin') ? $request->pay_status : '0',
                "description" => $request->description
            ]);

            if ($request->ajax()) {
                return $this->withSuccess("Expense Updated successfully");
            }
            return $this->withSuccess("Expense Updated successfully")->back();
        } catch (\Throwable $th) {
            return $this->withError($th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Expense $expense)
    {
        $expense->delete();
        if (request()->ajax()) {
            return $this->withSuccess("Expense Deleted successfully");
        }
        return $this->withSuccess("Expense Deleted successfully")->back();
    }

    public function getList(Request $request)
    {
        $searchableColumns = [
            'id',
            'amount',
            'date',
            'description'
        ];

        $this->model( Expense::class);

        if(auth()->user()->role == 'Admin'){
            $this->filter([
                "created_by" => Auth::id(),
            ]);
        }
            
        $editPermission = $this->hasPermission("expense-edit");
        $deletePermission = $this->hasPermission("expense-delete");

        $this->formateArray(function ($row, $index) use ($editPermission, $deletePermission) {
            $delete = route("master.expense.delete", ['expense' => $row->id]);
            $action = "";

            if ($editPermission && $row->pay_status == "0") {
                $action .= "
                            <a class='btn edit-btn  btn-action bg-success text-white me-2'
                                data-id='{$row->id}'
                                data-amount='{$row->amount}'
                                data-date='{$row->date}'
                                data-description='{$row->description}'
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
                "amount" => $row->amount,
                "date" => $row->date,
                "description" => $row->description,
                "action" => $action,
                "created_by" => $row->createdBy->name,
                "created_at" => $row->created_at ? $row->created_at->format('d/m/Y H:i:s') : '',
                "updated_at" => $row->updated_at ? $row->updated_at->format('d/m/Y H:i:s') : '',
            ];
        });
        return $this->getListAjax($searchableColumns);
    }
}
