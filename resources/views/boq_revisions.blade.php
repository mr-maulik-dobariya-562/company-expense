@php
    $badgeClass = function ($status) {
        $s = strtoupper((string)$status);
        if ($s === 'APPROVED') return 'badge-success';
        if ($s === 'REJECTED') return 'badge-danger';
        return 'badge-warning'; // PENDING/default
    };

    $fmt = function ($dt) {
        return $dt ? \Carbon\Carbon::parse($dt)->format('d/m/Y H:i:s') : '-';
    };
@endphp

@if($details->revisions && $details->revisions->count())
    <div class="mt-1">

        <ul class="nav nav-tabs" role="tablist">
            @foreach ($details->revisions as $k => $rev)
                <li class="nav-item">
                    <a class="nav-link {{ $k === 0 ? 'active' : '' }}"
                       data-toggle="tab"
                       href="#rev-{{ $rev->id }}"
                       role="tab">
                        Log {{ $rev->version ?? ($k+1) }}
                        {{-- <span class="badge {{ $badgeClass($rev->status) }}">
                            {{ strtoupper($rev->status ?? 'PENDING') }}
                        </span> --}}
                    </a>
                </li>
            @endforeach
        </ul>

        <div class="tab-content border-left border-right border-bottom p-3">
            @foreach ($details->revisions as $k => $rev)
                <div class="tab-pane fade {{ $k === 0 ? 'show active' : '' }}"
                     id="rev-{{ $rev->id }}"
                     role="tabpanel">

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div><b>Remark:</b> {{ $rev->remark ?? '-' }}</div>
                            <div><b>Submitted By:</b> {{ $rev->submittedBy?->displayName() ?? '-' }}</div>
                            <div><b>Approved By:</b> {{ $rev->approvedBy?->displayName() ?? '-' }}</div>
                            <div><b>Rejected By:</b> {{ $rev->rejectedBy?->displayName() ?? '-' }}</div>
                            <div><b>Rejection Reason:</b> {{ $rev->rejection_reason ?? '-' }}</div>
                        </div>

                        <div class="col-md-6">
                            <div><b>Status:</b>
                                <span class="badge {{ $badgeClass($rev->status) }}">
                                    {{ strtoupper($rev->status ?? 'PENDING') }}
                                </span>
                            </div>
                            <div><b>Submitted At:</b> {{ $fmt($rev->submitted_at) }}</div>
                            <div><b>Approved At:</b> {{ $fmt($rev->approved_at) }}</div>
                            <div><b>Rejected At:</b> {{ $fmt($rev->rejected_at) }}</div>
                        </div>
                    </div>

                    <h6 class="mb-2"><b>Revision Items</b></h6>

                    @if($rev->details && $rev->details->count())
                        <div class="table-responsive">
                            <table class="table table-sm table-striped table-bordered mb-0">
                                <thead>
                                    <tr>
                                        <th style="width:60px;">S No</th>
                                        <th>Item</th>
                                        <th style="width:120px;">Qty</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($rev->details as $i => $d)
                                        <tr>
                                            <td>{{ $i + 1 }}</td>
                                            <td>{{ $d->item_name ?? '-' }}</td>
                                            <td>{{ $d->qty ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info mb-0">No revision items found.</div>
                    @endif

                </div>
            @endforeach
        </div>
    </div>
@else
    <div class="mt-4 alert alert-info mb-0">No revisions found.</div>
@endif
