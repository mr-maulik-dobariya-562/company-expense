<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\User;
use App\Services\SettlementService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request, SettlementService $settlements)
    {
        $fundSummary = $settlements->getCompanyFundSummary();
        $totalApprovedExpenses = (float) Expense::where('status', 'approved')->sum('amount');

        return view('admin.dashboard', [
            'totalEmployees' => User::where('role', 'employee')->count(),
            'activeEmployees' => User::where('role', 'employee')->where('status', 'active')->count(),
            'totalApprovedExpenses' => $totalApprovedExpenses,
            'totalCompanyFunds' => $fundSummary['total_funds'],
            'totalPaidToEmployees' => (float) Payment::sum('amount'),
            'availableCompanyBalance' => $fundSummary['available_balance'],
            'totalPendingEmployeePayable' => $settlements->getTotalPendingEmployeePayable(),
            'recentExpenses' => Expense::with('user')->latest()->take(8)->get(),
        ]);
    }
}
