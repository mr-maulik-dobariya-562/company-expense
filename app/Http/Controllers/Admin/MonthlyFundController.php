<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MonthlyFund;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MonthlyFundController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = MonthlyFund::with('createdBy')->latest('fund_date');

        if ($request->filled('month')) {
            $query->whereBetween('fund_date', [
                Carbon::parse($request->month.'-01')->startOfMonth()->toDateString(),
                Carbon::parse($request->month.'-01')->endOfMonth()->toDateString(),
            ]);
        }

        return view('admin.monthly-funds.index', [
            'funds' => $query->paginate(20)->withQueryString(),
            'month' => $request->query('month'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.monthly-funds.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'fund_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'note' => ['nullable', 'string'],
        ]);

        MonthlyFund::create([
            'fund_date' => $data['fund_date'],
            'month_date' => Carbon::parse($data['fund_date'])->startOfMonth()->toDateString(),
            'amount' => $data['amount'],
            'note' => $data['note'] ?? null,
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('admin.monthly-funds.index')->with('success', 'Company fund saved successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(MonthlyFund $monthlyFund)
    {
        return redirect()->route('admin.monthly-funds.edit', $monthlyFund);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MonthlyFund $monthlyFund)
    {
        return view('admin.monthly-funds.edit', compact('monthlyFund'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MonthlyFund $monthlyFund)
    {
        $data = $request->validate([
            'fund_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'note' => ['nullable', 'string'],
        ]);

        $monthlyFund->update([
            'fund_date' => $data['fund_date'],
            'month_date' => Carbon::parse($data['fund_date'])->startOfMonth()->toDateString(),
            'amount' => $data['amount'],
            'note' => $data['note'] ?? null,
        ]);

        return redirect()->route('admin.monthly-funds.index')->with('success', 'Company fund updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MonthlyFund $monthlyFund)
    {
        $monthlyFund->delete();

        return back()->with('success', 'Company fund deleted successfully.');
    }
}
