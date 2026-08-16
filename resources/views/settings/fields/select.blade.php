<div class="form-control w-full">
    <label for="{{ $name }}" class="label pb-1.5 px-1">
        <span class="label-text text-sm font-medium text-base-content/80 whitespace-normal break-words">
            {{ $field->label }}
            @if(in_array('required', $field->rules))
                <span class="text-error font-bold ml-0.5">*</span>
            @endif
        </span>
    </label>
    
    <select 
        id="{{ $name }}" 
        name="{{ $name }}" 
        class="select select-bordered w-full bg-base-100 focus:select-primary focus:ring-2 focus:ring-primary/20 transition-all shadow-sm @error($name) select-error @enderror"
    >
        @if($field->placeholder)
            <option value="" disabled @selected(!$value)>{{ $field->placeholder }}</option>
        @endif

        @foreach($field->options as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" @selected((string)$value === (string)$optionValue)>
                {{ $optionLabel }}
            </option>
        @endforeach
    </select>

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