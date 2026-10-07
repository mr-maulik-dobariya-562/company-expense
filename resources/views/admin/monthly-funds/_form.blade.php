@csrf
<div class="row">
    <div class="col-md-3 form-group"><label>Fund Date</label><input type="date" class="form-control" name="fund_date" value="{{ old('fund_date', isset($monthlyFund) ? $monthlyFund->fund_date->format('Y-m-d') : now()->toDateString()) }}" required></div>
    <div class="col-md-3 form-group"><label>Amount</label><input type="number" step="0.01" min="0.01" class="form-control" name="amount" value="{{ old('amount', $monthlyFund->amount ?? '') }}" required></div>
    <div class="col-md-6 form-group"><label>Note</label><input class="form-control" name="note" value="{{ old('note', $monthlyFund->note ?? '') }}"></div>
</div>
<button class="btn btn-primary">Save Company Fund</button>
<a href="{{ route('admin.monthly-funds.index') }}" class="btn btn-secondary">Cancel</a>
