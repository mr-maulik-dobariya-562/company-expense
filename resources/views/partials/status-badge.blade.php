@php $tone = ['approved' => 'success', 'rejected' => 'danger', 'active' => 'success', 'inactive' => 'secondary'][$status] ?? 'warning'; @endphp
<span class="badge badge-{{ $tone }}">{{ ucfirst($status) }}</span>
