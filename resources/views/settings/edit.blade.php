@extends('laravel-auth::layouts.admin')

@section('title', $group->label . ' - Settings')

@section('content')
    <div class="w-full max-w-7xl mx-auto pb-12">

        @if ($errors->any())
            <div class="bg-error/10 border border-error/20 text-error p-4 rounded-xl mb-6 shadow-sm">
                <div class="flex items-center gap-2 mb-2 font-semibold">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    Please fix the following errors:
                </div>
                <ul class="list-disc pl-7 text-sm space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Header Section --}}
        <div class="mb-8">
            <a href="{{ route('utilities.settings.index') }}"
                class="inline-flex items-center text-sm font-medium text-base-content/60 hover:text-primary transition-colors mb-4 -ml-1">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 mr-1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
                Back to Settings
            </a>

            <h1 class="text-3xl font-extrabold text-base-content tracking-tight">{{ $group->label }}</h1>
            @if ($group->description)
                <p class="text-base text-base-content/60 mt-2">{{ $group->description }}</p>
            @endif
        </div>

        {{-- Form Container - Modern Card Design --}}
        <div class="card bg-base-100 shadow-xl shadow-base-200/50 border border-base-200/60 rounded-2xl overflow-hidden">
            <form action="{{ route('utilities.settings.update', $activeGroupKey) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="p-6 sm:p-10 space-y-8">
                    {{-- Dynamically render authorized fields --}}
                    @foreach ($group->getFields($user ?? auth()->user()) as $field)
                        @php
                            $dependsField = $field->getDependsOnField();
                            $dependsValue = $field->getDependsOnValue();
                            $encodedDependsValue = is_array($dependsValue) ? json_encode($dependsValue) : $dependsValue;
                        @endphp

                        <div class="settings-field-wrapper transition-all duration-300 ease-in-out origin-top"
                            data-field-name="{{ $field->getName() }}"
                            @if ($dependsField) 
                                data-depends-on="{{ $dependsField }}" 
                                data-depends-value="{{ $encodedDependsValue }}" 
                            @endif>

                            {!! $field->render() !!}

                        </div>
                    @endforeach
                </div>

                {{-- Clean Sticky Action Footer --}}
                <div class="bg-base-200/30 px-6 py-5 sm:px-10 border-t border-base-200/60 flex items-center justify-end sticky bottom-0 backdrop-blur-md z-10">
                    <button type="submit" class="btn btn-primary px-10 rounded-xl shadow-sm shadow-primary/30">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 mr-1">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                        </svg>
                        Save Changes
                    </button>
                </div>
            </form>
        </div>

    </div>
@endsection

@push('scripts')
    {{-- Include SortableJS globally for array fields --}}
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const wrappers = document.querySelectorAll('.settings-field-wrapper[data-depends-on]');

            function getInputValue(fieldName) {
                const inputs = document.querySelectorAll(`[name="${fieldName}"], [name="${fieldName}[]"]`);
                if (inputs.length === 0) return null;

                if (inputs.length > 1 && inputs[0].type === 'checkbox') {
                    return Array.from(inputs).filter(i => i.checked).map(i => i.value);
                }

                for (let input of inputs) {
                    if (input.type === 'checkbox' && input.name === fieldName) {
                        return input.checked ? "1" : "0";
                    }
                    if (input.type === 'radio' && input.checked) {
                        return input.value;
                    }
                }

                return inputs[inputs.length - 1].value;
            }

            function isElementVisible(el) {
                return !el.classList.contains('hidden');
            }

            function evaluateDependencies() {
                let changed;
                let iterations = 0;
                const maxIterations = 5; 

                do {
                    changed = false;
                    iterations++;

                    wrappers.forEach(wrapper => {
                        const dependsOnField = wrapper.getAttribute('data-depends-on');
                        let requiredValue = wrapper.getAttribute('data-depends-value');
                        const currentlyHidden = wrapper.classList.contains('hidden');

                        const parentWrapper = document.querySelector(`.settings-field-wrapper[data-field-name="${dependsOnField}"]`);
                        
                        if (parentWrapper && !isElementVisible(parentWrapper)) {
                            if (!currentlyHidden) {
                                toggleVisibility(wrapper, false);
                                changed = true;
                            }
                            return; 
                        }

                        try {
                            const parsed = JSON.parse(requiredValue);
                            if (typeof parsed === 'object' && parsed !== null) {
                                requiredValue = parsed;
                            }
                        } catch (e) {}

                        const currentValue = getInputValue(dependsOnField);
                        let isMatch = false;

                        const isRequiredArray = Array.isArray(requiredValue);
                        const isCurrentArray = Array.isArray(currentValue);

                        if (currentValue !== null) {
                            if (isRequiredArray && isCurrentArray) {
                                isMatch = requiredValue.some(r => currentValue.includes(String(r)));
                            } else if (isRequiredArray && !isCurrentArray) {
                                isMatch = requiredValue.map(String).includes(String(currentValue));
                            } else if (!isRequiredArray && isCurrentArray) {
                                isMatch = currentValue.includes(String(requiredValue));
                            } else {
                                isMatch = String(currentValue) === String(requiredValue);
                            }
                        }

                        if (isMatch !== !currentlyHidden) {
                            toggleVisibility(wrapper, isMatch);
                            changed = true;
                        }
                    });
                } while (changed && iterations < maxIterations);
            }

            function toggleVisibility(wrapper, show) {
                if (show) {
                    wrapper.classList.remove('hidden', 'opacity-0', 'h-0', 'overflow-hidden', 'mt-0', 'mb-0');
                } else {
                    wrapper.classList.add('hidden', 'opacity-0', 'h-0', 'overflow-hidden', 'mt-0', 'mb-0');
                }
            }

            document.querySelectorAll('input, select, textarea').forEach(el => {
                el.addEventListener('change', evaluateDependencies);
                el.addEventListener('input', evaluateDependencies);
            });

            evaluateDependencies();

            const toast = document.querySelector('.toast');
            if (toast) {
                setTimeout(() => {
                    toast.style.transition = 'opacity 0.5s ease';
                    toast.style.opacity = '0';
                    setTimeout(() => toast.remove(), 500);
                }, 4000);
            }
        });
    </script>
@endpush