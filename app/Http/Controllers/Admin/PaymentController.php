<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::with(['user', 'paidBy'])->latest('payment_date')->latest('id');

        if ($request->filled('employee_id')) {
            $query->where('user_id', $request->employee_id);
        }
        if ($request->filled('from_date')) {
            $query->whereDate('payment_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('payment_date', '<=', $request->to_date);
        }
        if ($request->filled('month')) {
            $query->whereBetween('payment_date', [
                Carbon::parse($request->month.'-01')->startOfMonth()->toDateString(),
                Carbon::parse($request->month.'-01')->endOfMonth()->toDateString(),
            ]);
        }

        return view('admin.payments.index', [
            'payments' => $query->paginate(20)->withQueryString(),
            'employees' => User::where('role', 'employee')->orderBy('name')->get(),
            'filters' => $request->only(['employee_id', 'from_date', 'to_date', 'month']),
        ]);
    }
}
