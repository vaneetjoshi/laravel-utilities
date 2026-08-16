<div class="form-control w-full mb-5">
    <label for="{{ $name }}" class="label pb-1">
        <span class="label-text font-semibold text-base-content whitespace-normal break-words">
            {{ $field->label }}
            @if(in_array('required', $field->rules))
                <span class="text-error ml-1">*</span>
            @endif
        </span>
    </label>
    
    @php $valueArray = is_array($value) ? $value : []; @endphp
    
    {{-- Responsive Grid: 1 col on mobile, 2 on sm, 3 on md+ --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2 mt-1 p-3 border border-base-300 rounded-xl bg-base-100/50 @error($name) border-error @enderror">
        @foreach($field->options as $optionValue => $optionLabel)
            <label class="cursor-pointer label justify-start gap-3 p-2 rounded-lg hover:bg-base-200 transition-colors">
                <input 
                    type="checkbox" 
                    name="{{ $name }}[]" 
                    value="{{ $optionValue }}"
                    @checked(in_array($optionValue, $valueArray))
                    class="checkbox checkbox-sm checkbox-primary shrink-0" 
                />
                <span class="label-text text-sm whitespace-normal break-words">{{ $optionLabel }}</span>
            </label>
        @endforeach
    </div>

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