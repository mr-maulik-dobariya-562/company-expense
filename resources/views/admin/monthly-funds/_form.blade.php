@csrf
<div class="row">
    <div class="col-md-3 form-group"><label for="fund_date">Fund Date</label><input id="fund_date" type="date" class="form-control" name="fund_date" value="{{ old('fund_date', isset($monthlyFund) ? $monthlyFund->fund_date->format('Y-m-d') : now()->toDateString()) }}" required></div>
    <div class="col-md-3 form-group"><label for="amount">Amount (₹)</label><input id="amount" type="number" inputmode="decimal" step="0.01" min="0.01" class="form-control" name="amount" value="{{ old('amount', $monthlyFund->amount ?? '') }}" required></div>
    <div class="col-md-6 form-group"><label for="note">Note</label><input id="note" class="form-control" name="note" value="{{ old('note', $monthlyFund->note ?? '') }}" placeholder="Optional"></div>
</div>
<div class="form-actions">
    <button class="btn btn-primary"><i class="fas fa-check mr-1"></i> Save Company Fund</button>
    <a href="{{ route('admin.monthly-funds.index') }}" class="btn btn-secondary">Cancel</a>
</div>
