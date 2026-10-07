{{-- Approve / Reject buttons, hiding the one matching the current status --}}
@foreach (['approved' => ['success', 'Approve', 'check'], 'rejected' => ['danger', 'Reject', 'times']] as $status => [$tone, $label, $icon])
    @continue($expense->status === $status)
    <form method="POST" action="{{ route('admin.expenses.status', $expense) }}">@csrf @method('PATCH')
        <input type="hidden" name="status" value="{{ $status }}">
        <button class="btn {{ $size ?? 'btn-sm' }} btn-{{ $tone }}"><i class="fas fa-{{ $icon }} mr-1"></i>{{ $label }}</button>
    </form>
@endforeach
