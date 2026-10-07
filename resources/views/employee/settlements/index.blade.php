@extends('layouts.app')
@section('title', 'My Settlement')
@section('content')
<div class="row">
@foreach ([['Total Expenses',$outstanding['total_expenses'],'bg-warning'],['Total Paid',$outstanding['total_paid'],'bg-success'],['Pending Receivable',$outstanding['pending_receivable'],'bg-danger']] as [$label,$value,$color])
<div class="col-6 col-md-4"><div class="small-box {{ $color }}"><div class="inner"><h4>&#8377;{{ number_format($value, 2) }}</h4><p>{{ $label }}</p></div></div></div>
@endforeach
</div>
<div class="card d-none d-md-block"><div class="card-header"><h3 class="card-title">Month-wise Expense Report</h3></div><div class="card-body table-responsive p-0"><table class="table table-striped mb-0"><thead><tr><th>Month</th><th>Total Expenses</th></tr></thead><tbody>
@forelse ($monthlyExpenses as $row)
<tr><td>{{ $row->month_date->format('M Y') }}</td><td>&#8377;{{ number_format($row->total_expenses, 2) }}</td></tr>
@empty
<tr><td colspan="2" class="text-center">No approved expenses found.</td></tr>
@endforelse
</tbody></table></div></div>

<div class="d-md-none">
<h2 class="h6 font-weight-bold mb-2">Month-wise Expense Report</h2>
@forelse ($monthlyExpenses as $row)
<div class="card mobile-record-card"><div class="card-body d-flex justify-content-between"><div class="value">{{ $row->month_date->format('M Y') }}</div><div class="amount">&#8377;{{ number_format($row->total_expenses, 2) }}</div></div></div>
@empty
<div class="empty-state"><i class="fas fa-receipt"></i>No approved expenses found.</div>
@endforelse
</div>
@endsection
