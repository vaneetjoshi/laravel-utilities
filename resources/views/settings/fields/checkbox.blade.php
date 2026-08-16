<div class="form-control w-full mb-5">
    <div class="flex items-start gap-4 p-4 border border-base-300 rounded-xl bg-base-100/50 @error($name) border-error @enderror">
        <div class="flex items-center h-6 shrink-0">
            <input type="hidden" name="{{ $name }}" value="0">
            <input 
                type="checkbox" 
                id="{{ $name }}" 
                name="{{ $name }}" 
                value="1" 
                @checked($value)
                class="checkbox checkbox-primary"
            />
        </div>
        <div class="flex flex-col">
            <label for="{{ $name }}" class="label-text font-semibold text-base-content cursor-pointer whitespace-normal break-words">
                {{ $field->label }}
                @if(in_array('required', $field->rules))
                    <span class="text-error ml-1">*</span>
                @endif
            </label>
            
            @if($field->helpText || $errors->has($name))
                <div class="mt-1">
                    @error($name)
                        <span class="text-xs text-error font-medium">{{ $message }}</span>
                    @else
                        <span class="text-xs text-base-content/70 whitespace-normal">{{ $field->helpText }}</span>
                    @enderror
                </div>
            @endif
        </div>
    </div>
</div>