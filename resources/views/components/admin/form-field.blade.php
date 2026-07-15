@props([
    'type' => 'text',
    'name',
    'label',
    'value' => null,
    'required' => false,
    'placeholder' => null,
    'help' => null,
    'rows' => 2,
])

<div class="mb-3">
    <label for="{{ $name }}" class="form-label">
        {{ $label }}
        @if($required)<span class="text-danger">*</span>@endif
    </label>

    @if($type === 'select')
        <select {{ $attributes->merge(['class' => 'form-select'.($errors->has($name) ? ' is-invalid' : '')]) }}
                id="{{ $name }}" name="{{ $name }}" @if($required) required @endif>
            {{ $slot }}
        </select>
    @elseif($type === 'textarea')
        <textarea {{ $attributes->merge(['class' => 'form-control'.($errors->has($name) ? ' is-invalid' : '')]) }}
                  id="{{ $name }}" name="{{ $name }}" rows="{{ $rows }}"
                  placeholder="{{ $placeholder }}">{{ old($name, $value) }}</textarea>
    @elseif($type === 'file')
        {{ $slot }}
        <input type="file" {{ $attributes->merge(['class' => 'form-control'.($errors->has($name) ? ' is-invalid' : '')]) }}
               id="{{ $name }}" name="{{ $name }}">
    @else
        <input type="{{ $type }}" {{ $attributes->merge(['class' => 'form-control'.($errors->has($name) ? ' is-invalid' : '')]) }}
               id="{{ $name }}" name="{{ $name }}" value="{{ old($name, $value) }}"
               placeholder="{{ $placeholder }}" @if($required) required @endif>
    @endif

    @if($help)
        <div class="form-text">{{ $help }}</div>
    @endif

    @error($name)
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
