<div class="form-control w-full mb-5">
    <label for="{{ $name }}" class="label pb-1">
        <span class="label-text font-semibold text-base-content whitespace-normal break-words">
            {{ $field->label }}
            @if(in_array('required', $field->rules))
                <span class="text-error ml-1">*</span>
            @endif
        </span>
    </label>
    
    <textarea 
        name="{{ $name }}" 
        id="{{ $name }}" 
        rows="4"
        @if($field->placeholder) placeholder="{{ $field->placeholder }}" @endif
        class="textarea textarea-bordered w-full focus:textarea-primary transition-colors text-base @error($name) textarea-error @enderror"
    >{{ $value }}</textarea>

    @if($field->helpText || $errors->has($name))
        <label class="label pt-1 pb-0">
            @error($name)
                <span class="label-text-alt text-error font-medium">{{ $message }}</span>
            @else
                <span class="label-text-alt text-base-content/70 whitespace-normal">{{ $field->helpText }}</span>
            @enderror
        </label>
    @endif
</div>