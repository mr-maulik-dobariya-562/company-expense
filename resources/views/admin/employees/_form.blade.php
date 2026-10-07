@csrf
<div class="row">
    <div class="col-md-6 form-group"><label for="name">Name</label><input id="name" class="form-control" name="name" value="{{ old('name', $employee->name ?? '') }}" required></div>
    <div class="col-md-6 form-group"><label for="email">Email</label><input id="email" type="email" class="form-control" name="email" value="{{ old('email', $employee->email ?? '') }}" required></div>
    <div class="col-md-6 form-group"><label for="phone">Phone</label><input id="phone" type="tel" class="form-control" name="phone" value="{{ old('phone', $employee->phone ?? '') }}"></div>
    <div class="col-md-6 form-group"><label for="upi_id">UPI ID <span class="text-danger">*</span></label><input id="upi_id" class="form-control" name="upi_id" value="{{ old('upi_id', $employee->upi_id ?? '') }}" placeholder="e.g. name@okaxis" pattern="[a-zA-Z0-9._\-]{2,256}@[a-zA-Z][a-zA-Z0-9]{1,63}" title="Valid UPI ID like name@okaxis" autocapitalize="off" spellcheck="false" required><small class="text-muted">Used to generate the payment QR code.</small></div>
    <div class="col-md-3 form-group"><label for="password">Password @isset($employee)<span class="text-muted font-weight-normal">(optional)</span>@endisset</label><input id="password" type="password" class="form-control" name="password" autocomplete="new-password" @unless(isset($employee)) required @endunless></div>
    <div class="col-md-3 form-group"><label for="status">Status</label><select id="status" class="form-control" name="status" required><option value="active" @selected(old('status', $employee->status ?? 'active') === 'active')>Active</option><option value="inactive" @selected(old('status', $employee->status ?? '') === 'inactive')>Inactive</option></select></div>
</div>
<div class="form-actions">
    <button class="btn btn-primary"><i class="fas fa-check mr-1"></i> Save Employee</button>
    <a href="{{ route('admin.employees.index') }}" class="btn btn-secondary">Cancel</a>
</div>
