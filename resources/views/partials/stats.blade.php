{{-- $stats: array of [label, displayValue, tone, icon] --}}
<div class="stat-grid">
    @foreach ($stats as [$label, $value, $tone, $icon])
        <div class="stat-card tone-{{ $tone }}">
            <div class="stat-icon"><i class="{{ $icon }}"></i></div>
            <div class="stat-value">{{ $value }}</div>
            <div class="stat-label">{{ $label }}</div>
        </div>
    @endforeach
</div>
