@csrf
<div class="row">
    <div class="col-md-6 form-group"><label>Title</label><input class="form-control" name="title" value="{{ old('title', $expense->title ?? '') }}" required></div>
    <div class="col-md-3 form-group"><label>Amount</label><input type="number" step="0.01" min="0.01" class="form-control" name="amount" value="{{ old('amount', $expense->amount ?? '') }}" required></div>
    <div class="col-md-3 form-group"><label>Expense Date</label><input type="date" class="form-control" name="expense_date" value="{{ old('expense_date', isset($expense) ? $expense->expense_date->format('Y-m-d') : now()->format('Y-m-d')) }}" required></div>
    <div class="col-md-12 form-group"><label>Description</label><textarea class="form-control" name="description" rows="3">{{ old('description', $expense->description ?? '') }}</textarea></div>
</div>
<button class="btn btn-primary">Save Expense</button>
<a href="{{ route('employee.expenses.index') }}" class="btn btn-secondary">Cancel</a>
