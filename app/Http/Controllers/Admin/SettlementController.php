<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\User;
use App\Services\SettlementService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SettlementController extends Controller
{
    public function index(Request $request, SettlementService $settlements)
    {
        return view('admin.settlements.index', [
            'rows' => $settlements->getAllEmployeeOutstanding(),
            'summary' => $settlements->getCompanyFundSummary(),
            'pendingPayable' => $settlements->getTotalPendingEmployeePayable(),
        ]);
    }

    public function pay(Request $request, User $employee, SettlementService $settlements)
    {
        abort_unless($employee->isEmployee(), 404);

        $data = $request->validate([
            'amount' => ['nullable', 'numeric', 'gt:0'],
            'note' => ['nullable', 'string'],
        ]);

        $outstanding = $settlements->getEmployeeOutstanding($employee->id);
        $pending = $outstanding['pending_receivable'];

        if ($pending <= 0) {
            return back()->with('success', 'No pending receivable for this employee.');
        }

        $amount = isset($data['amount']) ? (float) $data['amount'] : $pending;

        if ($amount > $pending) {
            return back()->withErrors(['amount' => 'Payment amount must not be greater than pending receivable.']);
        }

        $fundSummary = $settlements->getCompanyFundSummary();
        if ($fundSummary['available_balance'] < $amount) {
            return back()->withErrors(['amount' => 'Insufficient company fund balance. Please add fund first.']);
        }

        $paymentDate = now()->toDateString();

        Payment::create([
            'user_id' => $employee->id,
            'payment_date' => $paymentDate,
            'month_date' => Carbon::parse($paymentDate)->startOfMonth()->toDateString(),
            'amount' => $amount,
            'note' => $data['note'] ?? (isset($data['amount']) ? 'Partial pending settlement paid' : 'Full pending settlement paid'),
            'paid_by' => $request->user()->id,
            'paid_at' => now(),
        ]);

        return back()->with('success', 'Payment recorded successfully.');
    }
}
