<div class="form-control w-full mb-4">
    <label for="{{ $name }}" class="label">
        <span class="label-text font-medium">
            {{ $field->label }}
            @if(in_array('required', $field->rules))
                <span class="text-error">*</span>
            @endif
        </span>
    </label>

    @if($value && is_string($value))
        <div class="mb-4">
            <img src="{{ Storage::disk($field->disk)->url($value) }}" alt="{{ $field->label }}" class="h-24 w-auto object-cover rounded-box border border-base-300 shadow-sm">
        </div>
    @endif
    
    <input 
        type="file" 
        name="{{ $name }}" 
        id="{{ $name }}" 
        accept="image/*"
        class="file-input file-input-bordered w-full @error($name) file-input-error @enderror"
    />

    @if($field->helpText || $errors->has($name))
        <label class="label">
            @error($name)
                <span class="label-text-alt text-error">{{ $message }}</span>
            @else
                <span class="label-text-alt text-base-content/70">{{ $field->helpText }}</span>
            @enderror
        </label>
    @endif
</div>