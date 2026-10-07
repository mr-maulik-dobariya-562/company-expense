@php $emp = $row['employee']; @endphp
<button type="button" class="btn btn-primary {{ $size }}" data-pay-open
        data-name="{{ $emp->name }}"
        data-upi="{{ $emp->upi_id }}"
        data-amount="{{ number_format($row['pending_receivable'], 2, '.', '') }}"
        data-action="{{ route('admin.settlements.pay', $emp) }}">
    <i class="fas fa-{{ $emp->upi_id ? 'qrcode' : 'money-bill-wave' }} mr-1"></i>Pay
</button>
