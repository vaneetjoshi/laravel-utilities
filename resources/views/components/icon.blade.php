@props(['name' => '', 'class' => 'w-6 h-6'])

@php
    // Convert colons to hyphens to create a valid view path.
    // e.g., "tabler:binary-tree" becomes "tabler-binary-tree"
    $safeName = str_replace(':', '-', (string) $name);
    
    // Check host app overrides first, then the package library
    $hostViewPath = 'components.icons.' . $safeName;
    $packageViewPath = 'utilities::components.icons.' . $safeName;
@endphp

@if(str_starts_with($name, 'http'))
    {{-- Render remote image URL --}}
    <img src="{{ $name }}" class="{{ $class }} object-contain" alt="Icon">
@elseif($safeName !== '' && view()->exists($hostViewPath))
    {{-- Render overridden local SVG from Host App --}}
    @include($hostViewPath, ['class' => $class])
@elseif($safeName !== '' && view()->exists($packageViewPath))
    {{-- Render core SVG from Laravel Utilities Engine --}}
    @include($packageViewPath, ['class' => $class])
@else
    {{-- Fallback to Iconify rendering engine --}}
    <span class="iconify {{ $class }}" data-icon="{{ $name }}"></span>
    
    {{-- Automatically push the Iconify JS exactly once if a fallback is triggered --}}
    @once
        @push('scripts')
            <script src="https://code.iconify.design/3/3.1.0/iconify.min.js"></script>
        @endpush
    @endonce
@endif