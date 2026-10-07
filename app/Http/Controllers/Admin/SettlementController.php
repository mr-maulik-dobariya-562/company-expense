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
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $outstanding = $settlements->getEmployeeOutstanding($employee->id);
        $pending = $outstanding['pending_receivable'];

        if ($pending <= 0) {
            return $this->fail($request, 'No pending receivable for this employee.');
        }

        $amount = isset($data['amount']) ? round((float) $data['amount'], 2) : $pending;

        if ($amount > $pending) {
            return $this->fail($request, 'Payment amount must not be greater than pending receivable.');
        }

        $fundSummary = $settlements->getCompanyFundSummary();
        if ($fundSummary['available_balance'] < $amount) {
            return $this->fail($request, 'Insufficient company fund balance. Please add fund first.');
        }

        $paymentDate = now()->toDateString();
        $note = $data['note'] ?? (isset($data['amount']) ? 'Partial pending settlement paid' : 'Full pending settlement paid');

        $payment = Payment::create([
            'user_id' => $employee->id,
            'payment_date' => $paymentDate,
            'month_date' => Carbon::parse($paymentDate)->startOfMonth()->toDateString(),
            'amount' => $amount,
            'note' => $note,
            'paid_by' => $request->user()->id,
            'paid_at' => now(),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Payment recorded successfully.',
                'payment' => [
                    'id' => $payment->id,
                    'employee' => $employee->name,
                    'amount' => $amount,
                    'note' => $note,
                    'paid_at' => $payment->paid_at->format('d M Y, h:i A'),
                ],
                'remaining' => $settlements->getEmployeeOutstanding($employee->id)['pending_receivable'],
            ]);
        }

        return back()->with('success', 'Payment recorded successfully.');
    }

    private function fail(Request $request, string $message)
    {
        return $request->expectsJson()
            ? response()->json(['message' => $message, 'errors' => ['amount' => [$message]]], 422)
            : back()->withErrors(['amount' => $message]);
    }
}
