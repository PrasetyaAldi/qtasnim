@props([
    'name' => null,
    'label' => null,
    'options' => [],
    'value' => null,
    'required' => false,
    'disabled' => false,
    'readonly' => false,
    'edit' => true,
])

<div class="mb-3">
    <label for="{{ $name }}" class="form-label fw-medium">{{ $label }}@if ($required && $edit)
            <span class="text-danger">*</span>
        @endif
    </label>
    @if ($edit)
        <select name="{{ $name }}" id="{{ $name }}" class="form-select"
            @if ($required) required @endif @if ($disabled) disabled @endif
            @if ($readonly) readonly @endif>
            @foreach ($options as $key => $option)
                <option value="{{ $key }}" @if ($value == $key) selected @endif>
                    {{ $option }}
                </option>
            @endforeach
        </select>
    @else
        <p>{{ $options[$value] }}</p>
    @endif
</div>
