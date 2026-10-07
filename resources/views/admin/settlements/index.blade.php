@extends('layouts.app')
@section('title', 'Employee Settlement / Running Settlement')
@section('content')
<div class="row">
@foreach ([['Total Company Funds',$summary['total_funds'],'bg-primary'],['Total Paid to Employees',$summary['total_paid'],'bg-success'],['Available Company Balance',$summary['available_balance'],'bg-info'],['Total Pending Payable',$pendingPayable,'bg-danger']] as [$label,$value,$color])
<div class="col-6 col-md-3"><div class="small-box {{ $color }}"><div class="inner"><h4>&#8377;{{ number_format($value, 2) }}</h4><p>{{ $label }}</p></div></div></div>
@endforeach
</div>
<div class="card d-none d-md-block"><div class="card-body table-responsive p-0"><table class="table table-striped mb-0"><thead><tr><th>Employee Name</th><th>Total Approved Expenses</th><th>Total Paid</th><th>Pending Receivable</th><th>Status</th><th width="310">Action</th></tr></thead><tbody>
@foreach ($rows as $row)
<tr>
    <td>{{ $row['name'] }}</td>
    <td>&#8377;{{ number_format($row['total_expenses'], 2) }}</td>
    <td>&#8377;{{ number_format($row['total_paid'], 2) }}</td>
    <td>&#8377;{{ number_format($row['pending_receivable'], 2) }}</td>
    <td><span class="badge badge-{{ $row['pending_receivable'] > 0 ? 'warning' : 'success' }}">{{ $row['payment_status'] }}</span></td>
    <td>
        <form method="POST" action="{{ route('admin.settlements.pay', $row['employee']) }}" class="d-inline">@csrf
            <input type="hidden" name="note" value="Full pending settlement paid">
            <button class="btn btn-sm btn-success" @disabled($row['pending_receivable'] <= 0)>Pay Full</button>
        </form>
        <form method="POST" action="{{ route('admin.settlements.pay', $row['employee']) }}" class="d-inline-flex ml-1" style="gap:4px">@csrf
            <input type="number" step="0.01" min="0.01" max="{{ $row['pending_receivable'] }}" name="amount" class="form-control form-control-sm" style="width:110px" placeholder="Partial" @disabled($row['pending_receivable'] <= 0)>
            <input type="hidden" name="note" value="Partial pending settlement paid">
            <button class="btn btn-sm btn-primary" @disabled($row['pending_receivable'] <= 0)>Pay</button>
        </form>
    </td>
</tr>
@endforeach
</tbody></table></div></div>

<div class="d-md-none">
@forelse ($rows as $row)
<div class="card mobile-record-card"><div class="card-body">
    <div class="d-flex justify-content-between align-items-start"><div class="value">{{ $row['name'] }}</div><span class="badge badge-{{ $row['pending_receivable'] > 0 ? 'warning' : 'success' }}">{{ $row['payment_status'] }}</span></div>
    <div class="row mt-2">
        <div class="col-6 mb-2"><span class="label">Expenses</span><div class="amount">&#8377;{{ number_format($row['total_expenses'], 2) }}</div></div>
        <div class="col-6 mb-2"><span class="label">Paid</span><div class="amount">&#8377;{{ number_format($row['total_paid'], 2) }}</div></div>
        <div class="col-12"><span class="label">Pending</span><div class="amount text-danger">&#8377;{{ number_format($row['pending_receivable'], 2) }}</div></div>
    </div>
    <div class="actions">
        <form method="POST" action="{{ route('admin.settlements.pay', $row['employee']) }}">@csrf<input type="hidden" name="note" value="Full pending settlement paid"><button class="btn btn-success btn-sm" @disabled($row['pending_receivable'] <= 0)>Pay Full</button></form>
        <form method="POST" action="{{ route('admin.settlements.pay', $row['employee']) }}">@csrf<input type="number" step="0.01" min="0.01" max="{{ $row['pending_receivable'] }}" name="amount" class="form-control form-control-sm mb-2" placeholder="Partial amount" @disabled($row['pending_receivable'] <= 0)><input type="hidden" name="note" value="Partial pending settlement paid"><button class="btn btn-primary btn-sm" @disabled($row['pending_receivable'] <= 0)>Pay Partial</button></form>
    </div>
</div></div>
@empty
<div class="empty-state"><i class="fas fa-balance-scale"></i>No pending settlement.</div>
@endforelse
</div>
@endsection
