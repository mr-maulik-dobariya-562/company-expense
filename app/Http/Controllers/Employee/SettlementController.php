<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Services\SettlementService;
use Illuminate\Http\Request;

class SettlementController extends Controller
{
    public function index(Request $request, SettlementService $settlements)
    {
        return view('employee.settlements.index', [
            'outstanding' => $settlements->getEmployeeOutstanding($request->user()->id),
            'monthlyExpenses' => $settlements->getEmployeeMonthlyExpenseReport($request->user()->id),
        ]);
    }
}
