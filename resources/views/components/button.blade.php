@props([
    'type' => 'submit', // ['button', 'anchor', 'submit']
    'state' => 'primary',
    'icon' => null,
    'size' => 'md',
    'disabled' => false,
    'tooltip' => null,
    'id' => null,
    'action' => null,
    'resource' => null,
    'href' => null,
    'hreftarget' => '_self',
    'right' => false,
    'class' => null,
])

@if ($type != 'anchor')
    <button {{ $attributes }} type="{{ $type }}" @class(['btn', "btn-{$state}", "btn-{$size}", 'fw-medium', $class])
        @if ($tooltip) data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="{{ $tooltip }}" @endif
        data-id="{{ $id }}" data-action="{{ $action }}" data-resource="{{ $resource }}">
        @if ($icon)
            <i class="{{ $icon }}"></i>
        @endif
        {{ $slot }}
    </button>
@else
    <a {{ $attributes }} target="{{ $hreftarget }}" href="{{ $href }}" @class(['btn', "btn-{$state}", "btn-{$size}", 'fw-medium'])
        @if ($tooltip) data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="{{ $tooltip }}" @endif
        data-id="{{ $id }}" data-action="{{ $action }}" data-resource="{{ $resource }}">
        @if ($icon)
            <i class="{{ $icon }}"></i>
        @endif
        {{ $slot }}
    </a>
@endif
