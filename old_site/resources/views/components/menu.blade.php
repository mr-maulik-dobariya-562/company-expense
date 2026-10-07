@php
$isActive = $item["active"] ?? false;
@endphp

@if (!isset($item["children"]))
<li class="nav-item">
    <a href="{{ $item['url'] }}" class="nav-link {{ $isActive ? 'active' : '' }}">
        <i class="nav-icon {{ $item['icon'] ?? '' }}"></i>
        <p>{{ $item['name'] }}</p>
    </a>
</li>
@else
<li class="nav-item {{ $isActive ? 'menu-open' : '' }}">
    <a href="#" class="nav-link {{ $isActive ? 'active' : '' }}">
        <i class="nav-icon {{ $item['icon'] ?? '' }}"></i>
        <p>
            {{ $item['name'] }}
            <i class="right fas fa-angle-left"></i>
        </p>
    </a>
    <ul class="nav nav-treeview">
        @foreach($item['children'] as $child)
        @if (!isset($child['children']))
        <li class="nav-item">
            <a href="{{ $child['url'] }}" class="nav-link {{ $child['active'] ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>{{ $child['name'] }}</p>
            </a>
        </li>
        @else
        <li class="nav-item">
            <a href="#" class="nav-link {{ $child['active'] ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>
                    {{ $child['name'] }}
                    <i class="right fas fa-angle-left"></i>
                </p>
            </a>
            <ul class="nav nav-treeview">
                @foreach($child['children'] as $subchild)
                <li class="nav-item">
                    <a href="{{ $subchild['url'] }}" class="nav-link {{ $subchild['active'] ? 'active' : '' }}">
                        <i class="far fa-dot-circle nav-icon"></i>
                        <p>{{ $subchild['name'] }}</p>
                    </a>
                </li>
                @endforeach
            </ul>
        </li>
        @endif
        @endforeach
    </ul>
</li>
@endif
