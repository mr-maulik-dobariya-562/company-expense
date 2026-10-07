<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::with('paidBy')
            ->where('user_id', $request->user()->id)
            ->latest('payment_date')
            ->latest('id');

        if ($request->filled('month')) {
            $query->whereBetween('payment_date', [
                Carbon::parse($request->month.'-01')->startOfMonth()->toDateString(),
                Carbon::parse($request->month.'-01')->endOfMonth()->toDateString(),
            ]);
        }

        return view('employee.payments.index', [
            'payments' => $query->paginate(20)->withQueryString(),
            'month' => $request->query('month'),
        ]);
    }
}
