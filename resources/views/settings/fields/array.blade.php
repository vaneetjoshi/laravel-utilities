@php
    $items = is_array($value) ? $value : [];
    $schemaFields = $field->getSchema();
    
    // Generate a safe JavaScript identifier replacing array brackets with underscores
    $jsSafeId = preg_replace('/[^a-zA-Z0-9_]/', '_', $name);
@endphp

<div class="bg-base-200/40 rounded-2xl border border-base-200/80 p-5 sm:p-7 overflow-hidden mt-2">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6">
        <div>
            <h3 class="font-bold text-lg text-base-content flex items-center gap-2">
                {{ $field->label }}
                @if(in_array('required', $field->rules))
                    <span class="text-error mt-1">*</span>
                @endif
            </h3>
            @if($field->helpText)
                <p class="text-sm text-base-content/60 mt-0.5">{{ $field->helpText }}</p>
            @endif
        </div>
        
        <button type="button" onclick="addRepeaterRow_{{ $jsSafeId }}()" class="btn btn-primary btn-sm rounded-lg shadow-sm shrink-0">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Add Row
        </button>
    </div>

    <div id="repeater-container-{{ $jsSafeId }}" class="space-y-4">
        @foreach($items as $index => $itemData)
            <div class="repeater-row group bg-base-100 rounded-xl border border-base-200 shadow-sm overflow-hidden relative transition-all hover:border-primary/40 hover:shadow-md">
                
                <div class="bg-base-200/50 px-4 py-2 flex items-center justify-between border-b border-base-200">
                    <div class="flex items-center gap-3">
                        <div class="drag-handle-{{ $jsSafeId }} cursor-move text-base-content/30 hover:text-base-content/70 transition-colors" title="Drag to reorder">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 9h16.5m-16.5 6.75h16.5" />
                            </svg>
                        </div>
                        <span class="text-xs font-bold text-base-content/60 uppercase tracking-wider row-number-display">Row #{{ $loop->iteration }}</span>
                    </div>
                    
                    <button type="button" onclick="removeRepeaterRow_{{ $jsSafeId }}(this)" class="text-base-content/40 hover:text-error transition-colors p-1 rounded-md hover:bg-error/10" title="Remove row">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                        </svg>
                    </button>
                </div>

                <div class="p-4 sm:p-5 grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                    @foreach($schemaFields as $subField)
                        @php
                            $subName = $subField->getName();
                            $subValue = $itemData[$subName] ?? '';
                            
                            // Dynamically construct the nested name override
                            $inputName = "{$name}[{$index}][{$subName}]";
                        @endphp
                        
                        <div class="w-full">
                            {!! $subField->render($subValue, $inputName) !!}
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>

<template id="repeater-template-{{ $jsSafeId }}">
    <div class="repeater-row group bg-base-100 rounded-xl border border-base-200 shadow-sm overflow-hidden relative transition-all hover:border-primary/40 hover:shadow-md">
        
        <div class="bg-base-200/50 px-4 py-2 flex items-center justify-between border-b border-base-200">
            <div class="flex items-center gap-3">
                <div class="drag-handle-{{ $jsSafeId }} cursor-move text-base-content/30 hover:text-base-content/70 transition-colors" title="Drag to reorder">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 9h16.5m-16.5 6.75h16.5" />
                    </svg>
                </div>
                <span class="text-xs font-bold text-base-content/60 uppercase tracking-wider row-number-display">Row #__ROW_NUM__</span>
            </div>
            
            <button type="button" onclick="removeRepeaterRow_{{ $jsSafeId }}(this)" class="text-base-content/40 hover:text-error transition-colors p-1 rounded-md hover:bg-error/10" title="Remove row">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                </svg>
            </button>
        </div>

        <div class="p-4 sm:p-5 grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
            @foreach($schemaFields as $subField)
                @php
                    $subName = $subField->getName();
                    // Generate a unique index placeholder scoped to this specific array depth
                    $inputName = "{$name}[__INDEX_{{ $jsSafeId }}__][{$subName}]";
                @endphp
                <div class="w-full">
                    {!! $subField->render(null, $inputName) !!}
                </div>
            @endforeach
        </div>
    </div>
</template>

@push('scripts')
<script>
    if (typeof updateRowNumbers_{{ $jsSafeId }} !== 'function') {
        
        function updateRowNumbers_{{ $jsSafeId }}() {
            const container = document.getElementById('repeater-container-{{ $jsSafeId }}');
            if (!container) return;
            
            // Scope query to direct children to prevent overwriting nested arrays
            const rows = Array.from(container.children).filter(el => el.classList.contains('repeater-row'));
            
            rows.forEach((row, index) => {
                const label = row.querySelector('.row-number-display');
                if (label) {
                    label.textContent = 'Row #' + (index + 1);
                }
            });
        }

        function removeRepeaterRow_{{ $jsSafeId }}(button) {
            button.closest('.repeater-row').remove();
            updateRowNumbers_{{ $jsSafeId }}();
        }

        function addRepeaterRow_{{ $jsSafeId }}() {
            const container = document.getElementById('repeater-container-{{ $jsSafeId }}');
            const template = document.getElementById('repeater-template-{{ $jsSafeId }}').innerHTML;
            
            const uniqueIndex = Date.now(); 
            const newRowNum = container.children.length + 1;
            
            // Use safe ID replacing to prevent overriding child templates
            const regex = new RegExp('__INDEX_{{ $jsSafeId }}__', 'g');
            let html = template.replace(regex, uniqueIndex);
            html = html.replace(/__ROW_NUM__/g, newRowNum);
            
            container.insertAdjacentHTML('beforeend', html);
        }

        document.addEventListener('DOMContentLoaded', function() {
            const container = document.getElementById('repeater-container-{{ $jsSafeId }}');
            if (container && typeof Sortable !== 'undefined') {
                Sortable.create(container, {
                    handle: '.drag-handle-{{ $jsSafeId }}',
                    animation: 150,
                    ghostClass: 'bg-base-200/50',
                    onEnd: function() {
                        updateRowNumbers_{{ $jsSafeId }}();
                    }
                });
            }
        });
    }
</script>
@endpush