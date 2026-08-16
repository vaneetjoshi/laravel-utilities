@php
    $color = $widget->color ?? 'primary';
@endphp

<div class="bg-base-100 rounded-3xl p-6 border border-base-content/5 shadow-[0_4px_20px_rgb(0,0,0,0.02)] relative overflow-hidden group hover:border-{{ $color }}/20 transition-colors h-full">
    
    <div class="absolute top-0 right-0 w-24 h-24 bg-{{ $color }}/10 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
    
    <div class="flex justify-between items-start relative z-10">
        <div class="w-12 h-12 rounded-2xl bg-{{ $color }}/10 flex items-center justify-center text-{{ $color }} shadow-inner">
            @if($widget->icon)
                {{-- Use strict package component syntax to avoid host app conflicts --}}
                <x-utilities::icon :name="$widget->icon" class="w-6 h-6" />
            @endif
        </div>
    </div>

    <div class="mt-4 relative z-10">
        <p class="text-base-content/60 text-sm font-semibold tracking-wide uppercase">{{ $widget->title }}</p>
        <h3 class="text-3xl font-extrabold text-base-content mt-1">{!! $widget->value !!}</h3>
    </div>
</div>