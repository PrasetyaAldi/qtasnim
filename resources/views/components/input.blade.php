@props([
    'type' => 'text',
    'name' => null,
    'label' => null,
    'value' => null,
    'placeholder' => null,
    'required' => false,
    'disabled' => false,
    'readonly' => false,
    'edit' => true,
])

<div class="mb-3">
    <label for="{{ $name }}" class="form-label fw-medium">{{ $label }} @if ($required && $edit)
            <span class="text-danger">*</span>
        @endif </label>
    @if ($edit)
        <input type="{{ $type }}" name="{{ $name }}" id="{{ $name }}" class="form-control"
            value="{{ $value }}" placeholder="{{ $placeholder }}"
            @if ($required) required @endif @if ($disabled) disabled @endif
            @if ($readonly) readonly @endif>
        @if ($errors->has($name))
            <span class="text-danger">{{ $errors->first($name) }}</span>
        @endif
    @else
        @php
            $value = $value;
            if (isset($type) && $type === 'date') {
                $value = Carbon::parse($value)->format('d F Y');
            }
        @endphp
        <p>{{ $value }}</p>
    @endif
</div>
