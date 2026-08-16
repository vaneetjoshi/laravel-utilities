<div class="form-control w-full">
    <label for="{{ $name }}" class="label pb-1.5 px-1">
        <span class="label-text text-sm font-medium text-base-content/80 whitespace-normal break-words">
            {{ $field->label }}
            @if(in_array('required', $field->rules))
                <span class="text-error font-bold ml-0.5">*</span>
            @endif
        </span>
    </label>
    
    <input 
        type="number" 
        name="{{ $name }}" 
        id="{{ $name }}" 
        value="{{ $value }}"
        @if($field->min !== null) min="{{ $field->min }}" @endif
        @if($field->max !== null) max="{{ $field->max }}" @endif
        @if($field->step !== null) step="{{ $field->step }}" @endif
        @if($field->placeholder) placeholder="{{ $field->placeholder }}" @endif
        class="input input-bordered w-full bg-base-100 focus:input-primary focus:ring-2 focus:ring-primary/20 transition-all shadow-sm @error($name) input-error @enderror"
    />

    @if($field->helpText || $errors->has($name))
        <label class="label pt-1.5 pb-0 px-1">
            @error($name)
                <span class="label-text-alt text-error font-medium">{{ $message }}</span>
            @else
                <span class="label-text-alt text-base-content/60 whitespace-normal">{{ $field->helpText }}</span>
            @enderror
        </label>
    @endif
</div>