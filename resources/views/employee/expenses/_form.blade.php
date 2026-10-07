@csrf
<div class="row">
    <div class="col-md-6 form-group"><label for="title">Title</label><input id="title" class="form-control" name="title" value="{{ old('title', $expense->title ?? '') }}" placeholder="e.g. Office supplies" maxlength="255" required></div>
    <div class="col-md-3 form-group"><label for="amount">Amount (₹)</label><input id="amount" type="number" inputmode="decimal" step="0.01" min="0.01" class="form-control" name="amount" value="{{ old('amount', $expense->amount ?? '') }}" required></div>
    <div class="col-md-3 form-group"><label for="expense_date">Expense Date</label><input id="expense_date" type="date" class="form-control" name="expense_date" value="{{ old('expense_date', isset($expense) ? $expense->expense_date->format('Y-m-d') : now()->format('Y-m-d')) }}" max="{{ now()->format('Y-m-d') }}" required></div>
    <div class="col-md-12 form-group"><label for="description">Description</label><textarea id="description" class="form-control" name="description" rows="3" placeholder="Optional details">{{ old('description', $expense->description ?? '') }}</textarea></div>
</div>
<div class="form-actions">
    <button class="btn btn-primary"><i class="fas fa-check mr-1"></i> Save Expense</button>
    <a href="{{ route('employee.expenses.index') }}" class="btn btn-secondary">Cancel</a>
</div>
