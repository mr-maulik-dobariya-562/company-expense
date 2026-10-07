<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Expense::where('user_id', $request->user()->id)->latest('expense_date');

        if ($request->filled('month')) {
            $query->whereDate('month_date', $request->month.'-01');
        }

        return view('employee.expenses.index', [
            'expenses' => $query->paginate(20)->withQueryString(),
            'month' => $request->query('month'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('employee.expenses.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'expense_date' => ['required', 'date'],
        ]);

        $data['user_id'] = $request->user()->id;
        $data['month_date'] = Carbon::parse($data['expense_date'])->startOfMonth()->toDateString();
        $data['status'] = 'approved';
        Expense::create($data);

        return redirect()->route('employee.expenses.index')->with('success', 'Expense added successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Expense $expense)
    {
        $this->authorizeEmployeeExpense($expense);

        return view('employee.expenses.show', compact('expense'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Expense $expense)
    {
        $this->authorizeEmployeeExpense($expense);

        return view('employee.expenses.edit', compact('expense'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Expense $expense)
    {
        $this->authorizeEmployeeExpense($expense);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'expense_date' => ['required', 'date'],
        ]);

        $data['month_date'] = Carbon::parse($data['expense_date'])->startOfMonth()->toDateString();
        $expense->update($data);

        return redirect()->route('employee.expenses.index')->with('success', 'Expense updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Expense $expense)
    {
      
        $this->authorizeEmployeeExpense($expense);
        $expense->delete();

        return back()->with('success', 'Expense deleted successfully.');
    }

    private function authorizeEmployeeExpense(Expense $expense): void
    {
        abort_unless($expense->user_id === auth()->id(), 403);
    }
}
