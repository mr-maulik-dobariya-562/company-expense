<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\MonthlyFund;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class SettlementService
{
    public function monthStart(string $month): string
    {
        return Carbon::parse(strlen($month) === 7 ? $month.'-01' : $month)->startOfMonth()->toDateString();
    }

    public function getEmployeeOutstanding(int $userId): array
    {
        $totalExpenses = (float) Expense::where('user_id', $userId)
            ->where('status', 'approved')
            ->sum('amount');

        $totalPaid = (float) Payment::where('user_id', $userId)->sum('amount');

        return [
            'total_expenses' => $totalExpenses,
            'total_paid' => $totalPaid,
            'pending_receivable' => max($totalExpenses - $totalPaid, 0),
        ];
    }

    public function getEmployeeOutstandingUntilDate(int $userId, string $date): array
    {
        $until = Carbon::parse($date)->toDateString();

        $totalExpenses = (float) Expense::where('user_id', $userId)
            ->where('status', 'approved')
            ->whereDate('expense_date', '<=', $until)
            ->sum('amount');

        $totalPaid = (float) Payment::where('user_id', $userId)
            ->whereDate('payment_date', '<=', $until)
            ->sum('amount');

        return [
            'total_expenses' => $totalExpenses,
            'total_paid' => $totalPaid,
            'pending_receivable' => max($totalExpenses - $totalPaid, 0),
        ];
    }

    public function getCompanyFundSummary(): array
    {
        $totalFunds = (float) MonthlyFund::sum('amount');
        $totalPaid = (float) Payment::sum('amount');

        return [
            'total_funds' => $totalFunds,
            'total_paid' => $totalPaid,
            'available_balance' => $totalFunds - $totalPaid,
        ];
    }

    public function getAllEmployeeOutstanding(): Collection
    {
        // One query with aggregate sub-selects instead of two queries per employee.
        return User::where('role', 'employee')
            ->where('status', 'active')
            ->orderBy('name')
            ->withSum(['expenses as approved_expenses_total' => fn ($q) => $q->where('status', 'approved')], 'amount')
            ->withSum('payments as payments_total', 'amount')
            ->get()
            ->map(function (User $employee) {
                $totalExpenses = (float) $employee->approved_expenses_total;
                $totalPaid = (float) $employee->payments_total;
                $pending = max($totalExpenses - $totalPaid, 0);

                return [
                    'employee' => $employee,
                    'name' => $employee->name,
                    'total_expenses' => $totalExpenses,
                    'total_paid' => $totalPaid,
                    'pending_receivable' => $pending,
                    'payment_status' => $pending > 0 ? 'Pending' : 'Paid',
                ];
            });
    }

    public function getTotalPendingEmployeePayable(): float
    {
        // Same per-employee "never below zero" rule, computed in a single query.
        return (float) User::where('role', 'employee')
            ->where('status', 'active')
            ->withSum(['expenses as approved_expenses_total' => fn ($q) => $q->where('status', 'approved')], 'amount')
            ->withSum('payments as payments_total', 'amount')
            ->get(['id'])
            ->sum(fn (User $e) => max((float) $e->approved_expenses_total - (float) $e->payments_total, 0));
    }

    public function getEmployeeMonthlyExpenseReport(int $userId): Collection
    {
        return Expense::query()
            ->where('user_id', $userId)
            ->where('status', 'approved')
            ->selectRaw('month_date, SUM(amount) as total_expenses')
            ->groupBy('month_date')
            ->orderByDesc('month_date')
            ->get();
    }
}
