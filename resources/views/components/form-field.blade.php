<form class="form-group" {{ $attributes }}>
    <div class="form-field">
        <label for="{{ $id }}" class="form-label">
            {{ $label }}
            @if ($required ?? false)<span class="text-danger">*</span>@endif
        </label>

        @switch($type ?? 'text')
            @case('textarea')
                <textarea
                    id="{{ $id }}"
                    name="{{ $name }}"
                    class="form-input form-textarea @error($name) is-invalid @enderror"
                    rows="{{ $rows ?? 4 }}"
                    placeholder="{{ $placeholder ?? '' }}"
                    {{ $attributes->filter(fn($value, $key) => !in_array($key, ['class', 'type', 'name', 'id', 'label', 'placeholder', 'required', 'rows'])) }}
                ></textarea>
                @break
            @case('select')
                <select
                    id="{{ $id }}"
                    name="{{ $name }}"
                    class="form-input form-select @error($name) is-invalid @enderror"
                    {{ $attributes->filter(fn($value, $key) => !in_array($key, ['class', 'type', 'name', 'id', 'label', 'placeholder', 'required'])) }}
                >
                    <option value="">{{ $placeholder ?? 'Choose an option' }}</option>
                    {{ $slot }}
                </select>
                @break
            @default
                <input
                    type="{{ $type ?? 'text' }}"
                    id="{{ $id }}"
                    name="{{ $name }}"
                    class="form-input @error($name) is-invalid @enderror"
                    placeholder="{{ $placeholder ?? '' }}"
                    {{ $attributes->filter(fn($value, $key) => !in_array($key, ['class', 'type', 'name', 'id', 'label', 'placeholder', 'required'])) }}
                />
        @endswitch

        @error($name)
            <span class="form-error">{{ $message }}</span>
        @enderror
    </div>
</form>
