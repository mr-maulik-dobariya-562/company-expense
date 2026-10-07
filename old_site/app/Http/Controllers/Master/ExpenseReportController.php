<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ExpenseReportController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:expense-view', only: ['index', 'getList', 'details']),
            new Middleware('permission:expense-edit', only: ['payMonth']),
        ];
    }

    public function index()
    {
        return view('Master::expense.report');
    }

    /**
     * created_by wise month summary (DEBIT only)
     */
    public function getList(Request $request)
    {
        $request->validate(['month' => 'required']);
        $month = $request->month;

        $start = $month . '-01';
        $end = date('Y-m-t', strtotime($start));

        // Base query for month
        $base = Expense::query()
            ->whereNull('deleted_at')
            ->whereDate('date', '>=', $start)
            ->whereDate('date', '<=', $end);

        // Non-admin => only own
        // if (!auth()->user()->hasRole('Admin')) {
        //     $base->where('created_by', Auth::id());
        // }

        /**
         * ✅ TOP SUMMARY (single balance for the month)
         */
        $summary = (Expense::query())->selectRaw("
        COALESCE(SUM(CASE WHEN type='CREDIT' THEN amount ELSE 0 END),0) - COALESCE(SUM(CASE WHEN type='DEBIT'  THEN amount ELSE 0 END),0) as balance
    ")->first();

        /**
         * ✅ TABLE DATA (created_by wise, DEBIT only)
         */
        $rows = ($base)
            ->where('type', 'DEBIT')
            ->select([
                'created_by',
                DB::raw("COALESCE(SUM(amount),0) as total_debit"),
                DB::raw("COALESCE(SUM(CASE WHEN pay_status = '1' THEN amount ELSE 0 END),0) as total_paid"),
                DB::raw("COALESCE(SUM(CASE WHEN pay_status = '0' THEN amount ELSE 0 END),0) as total_unpaid"),
                DB::raw("COALESCE(SUM(CASE WHEN pay_status = '0' THEN 1 ELSE 0 END),0) as unpaid_count"),
            ])
            ->groupBy('created_by')
            ->get();

        $userNames = DB::table('users')
            ->whereIn('id', $rows->pluck('created_by')->unique())
            ->pluck('name', 'id');

        $data = $rows->map(function ($r) use ($userNames, $month) {
            $isAdmin = auth()->user()->hasRole('Admin');
            $canPay = $isAdmin && ((int) $r->unpaid_count > 0);

            $payBtn = $canPay ? "
            <button type='button' class='btn btn-success btn-sm pay-month'
                data-user_id='{$r->created_by}' data-month='{$month}'>
                <i class='fas fa-check-circle me-1'></i> Pay
            </button>
        " : "";

            $viewBtn = "
            <button type='button' class='btn btn-primary btn-sm view-month ms-2'
                data-user_id='{$r->created_by}' data-month='{$month}'>
                <i class='fas fa-eye me-1'></i> View
            </button>
        ";

            return [
                'user' => $userNames[$r->created_by] ?? ('User #' . $r->created_by),
                'total_debit' => number_format((float) $r->total_debit, 2),
                'total_paid' => number_format((float) $r->total_paid, 2),
                'total_unpaid' => number_format((float) $r->total_unpaid, 2),
                'action' => $payBtn . $viewBtn,
            ];
        })->values();

        return response()->json([
            'summary' => [
                'balance' => number_format((float) $summary->balance, 2),
            ],
            'data' => $data,
        ]);
    }


    /**
     * Mark selected month + user unpaid DEBIT expenses as paid
     */
    public function payMonth(Request $request)
    {
        if (!auth()->user()->hasRole('Admin')) {
            return response()->json(['success' => false, 'message' => 'Only Admin can pay expenses.']);
        }

        $request->validate([
            'month' => 'required',
            'user_id' => 'required|integer',
        ]);

        $month = $request->month;
        $start = $month . '-01';
        $end = date('Y-m-t', strtotime($start));

        $updated = Expense::query()
            ->whereNull('deleted_at')
            ->where('type', 'DEBIT')
            ->where('created_by', $request->user_id)
            ->whereDate('date', '>=', $start)
            ->whereDate('date', '<=', $end)
            ->where('pay_status', '0')
            ->update(['pay_status' => '1'])
        ;


        return response()->json([
            'success' => true,
            'message' => $updated ? "Paid successfully. Updated {$updated} entries." : "No unpaid entries found.",
        ]);
    }

    /**
     * Month list for user (DEBIT only)
     */
    public function details(Request $request)
    {
        $request->validate([
            'month' => 'required',
            'user_id' => 'required|integer',
        ]);

        // Non-admin restriction
        if (!auth()->user()->hasRole('Admin') && Auth::id() !== (int) $request->user_id) {
            return response()->json(['data' => []]);
        }

        $month = $request->month;
        $start = $month . '-01';
        $end = date('Y-m-t', strtotime($start));

        $rows = Expense::query()
            ->whereNull('deleted_at')
            ->where('type', 'DEBIT')
            ->where('created_by', $request->user_id)
            ->whereBetween('date', [$start, $end])
            ->orderBy('date', 'desc')
            ->get();

        $data = $rows->map(function ($r) {
            $status = $r->pay_status == "1"
                ? "<span class='badge bg-success'>Paid</span>"
                : "<span class='badge bg-warning text-dark'>Not Paid</span>";

            return [
                'id' => $r->id,
                'amount' => number_format((float) $r->amount, 2),
                'date' => $r->date ? date('d-m-Y', strtotime($r->date)) : '',
                'description' => e($r->description),
                'pay_status' => $status,
                'created_at' => $r->created_at ? $r->created_at->format('d/m/Y H:i:s') : '',
            ];
        })->values();

        return response()->json(['data' => $data]);
    }
}
