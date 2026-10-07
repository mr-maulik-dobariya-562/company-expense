<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $query = Expense::with('user')->latest('expense_date');

        if ($request->filled('employee_id')) {
            $query->where('user_id', $request->employee_id);
        }
        if ($request->filled('month')) {
            $query->whereDate('month_date', $request->month.'-01');
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return view('admin.expenses.index', [
            'expenses' => $query->paginate(20)->withQueryString(),
            'employees' => User::where('role', 'employee')->orderBy('name')->get(),
            'filters' => $request->only(['employee_id', 'month', 'status']),
        ]);
    }

    public function show(Expense $expense)
    {
        return view('admin.expenses.show', compact('expense'));
    }

    public function updateStatus(Request $request, Expense $expense)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['approved', 'rejected', 'pending'])],
        ]);

        $expense->update($data);

        return back()->with('success', 'Expense status updated.');
    }
}
