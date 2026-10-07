@csrf
<div class="row">
    <div class="col-md-6 form-group"><label>Name</label><input class="form-control" name="name" value="{{ old('name', $employee->name ?? '') }}" required></div>
    <div class="col-md-6 form-group"><label>Email</label><input type="email" class="form-control" name="email" value="{{ old('email', $employee->email ?? '') }}" required></div>
    <div class="col-md-6 form-group"><label>Phone</label><input class="form-control" name="phone" value="{{ old('phone', $employee->phone ?? '') }}"></div>
    <div class="col-md-3 form-group"><label>Password {{ isset($employee) ? '(optional)' : '' }}</label><input type="password" class="form-control" name="password" {{ isset($employee) ? '' : 'required' }}></div>
    <div class="col-md-3 form-group"><label>Status</label><select class="form-control" name="status" required><option value="active" @selected(old('status', $employee->status ?? 'active') === 'active')>Active</option><option value="inactive" @selected(old('status', $employee->status ?? '') === 'inactive')>Inactive</option></select></div>
</div>
<button class="btn btn-primary">Save Employee</button>
<a href="{{ route('admin.employees.index') }}" class="btn btn-secondary">Cancel</a>
