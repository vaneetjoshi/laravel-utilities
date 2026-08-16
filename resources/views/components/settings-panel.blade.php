@props([
    'group' => null,
    'routeName' // The host application's named route (e.g., 'admin.settings')
])

@php
    use Vaneetjoshi\LaravelUtilities\Settings\SettingsManager;

    // Resolve the active user
    $user = null;
    if (function_exists('tenant_auth') && function_exists('is_tenant_initialized') && is_tenant_initialized()) {
        $user = tenant_auth()->user();
    } elseif (function_exists('auth')) {
        $user = auth()->user();
    }

    // 🚀 Fetch from the Manager instead of config
    $allGroups = SettingsManager::getGroups();
    $authorizedGroups = array_filter($allGroups, fn($g) => $g->isAuthorized($user));
    
    // Determine active state
    $activeGroup = $group && array_key_exists($group, $authorizedGroups) ? $authorizedGroups[$group] : null;
@endphp

<div class="utilities-settings-panel w-full max-w-7xl mx-auto">

    {{-- Global Success/Error Messages --}}
    @if (session('success'))
        <div class="bg-success/10 border border-success/20 text-success p-4 rounded-xl mb-6 shadow-sm flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            {{ session('success') }}
        </div>
    @endif

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

    @if (!$activeGroup)
        {{-- ========================================================== --}}
        {{-- STATE 1: THE GRID VIEW                                     --}}
        {{-- ========================================================== --}}
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-base-content tracking-tight">Settings</h1>
            <p class="text-base text-base-content/60 mt-1">Manage your platform configuration, integrations, and application preferences.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($authorizedGroups as $key => $groupObj)
                {{-- Dynamically generate the route passing the group key --}}
                <a href="{{ route($routeName, ['group' => $key]) }}" class="group block h-full">
                    <div class="bg-base-100 border border-base-200 rounded-xl p-5 transition-all duration-200 hover:border-primary/40 hover:shadow-sm h-full">
                        <div class="flex items-start gap-4">
                            <div class="bg-base-200/50 p-3 rounded-lg text-base-content/70 group-hover:text-primary transition-colors flex-shrink-0">
                                {!! $groupObj->getIcon() !!}
                            </div>
                            <div>
                                <h2 class="text-lg font-semibold text-base-content group-hover:text-primary transition-colors">{{ $groupObj->label }}</h2>
                                @if ($groupObj->description)
                                    <p class="text-sm text-base-content/60 mt-1 line-clamp-2 leading-relaxed">{{ $groupObj->description }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

    @else
        {{-- ========================================================== --}}
        {{-- STATE 2: THE FORM VIEW                                     --}}
        {{-- ========================================================== --}}
        <div class="mb-8">
            {{-- Back button links back to the base route without the group parameter --}}
            <a href="{{ route($routeName) }}" class="inline-flex items-center text-sm font-medium text-base-content/60 hover:text-primary transition-colors mb-4 -ml-1">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 mr-1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
                Back to Settings
            </a>

            <h1 class="text-3xl font-extrabold text-base-content tracking-tight">{{ $activeGroup->label }}</h1>
            @if ($activeGroup->description)
                <p class="text-base text-base-content/60 mt-2">{{ $activeGroup->description }}</p>
            @endif
        </div>

        <div class="card bg-base-100 shadow-xl shadow-base-200/50 border border-base-200/60 rounded-2xl overflow-hidden">
            <form action="{{ route('utilities.settings.update', $group) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                {{-- Pass the current URL so the controller can redirect back cleanly --}}
                <input type="hidden" name="_redirect_url" value="{{ request()->fullUrl() }}">

                <div class="p-6 sm:p-10 space-y-8">
                    @foreach ($activeGroup->getFields($user) as $field)
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

        @once
            @push('scripts')
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
                    });
                </script>
            @endpush
        @endonce
    @endif
</div>