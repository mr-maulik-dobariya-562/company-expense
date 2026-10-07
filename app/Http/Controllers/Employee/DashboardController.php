<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Payment;
use App\Services\SettlementService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request, SettlementService $settlements)
    {
        $outstanding = $settlements->getEmployeeOutstanding($request->user()->id);

        return view('employee.dashboard', [
            'outstanding' => $outstanding,
            'recentExpenses' => Expense::where('user_id', $request->user()->id)->latest('expense_date')->take(8)->get(),
            'recentPayments' => Payment::with('paidBy')->where('user_id', $request->user()->id)->latest('payment_date')->take(8)->get(),
        ]);
    }
}
