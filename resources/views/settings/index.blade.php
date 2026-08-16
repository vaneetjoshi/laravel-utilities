@extends('laravel-auth::layouts.admin')

@section('title', 'System Settings')

@section('content')
    <div class="w-full">
        
        {{-- Clean, Unopinionated Header --}}
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-base-content tracking-tight">Settings</h1>
            <p class="text-base text-base-content/60 mt-1">Manage your platform configuration, integrations, and application preferences.</p>
        </div>

        {{-- Sleek Settings Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($groups as $key => $group)
                <a href="{{ route('utilities.settings.edit', $key) }}" class="group block h-full">
                    <div class="bg-base-100 border border-base-200 rounded-xl p-5 transition-all duration-200 hover:border-primary/40 hover:shadow-sm h-full">
                        <div class="flex items-start gap-4">
                            
                            {{-- Dynamic Icon Rendering (Now guaranteed to always return an SVG from the PHP schema) --}}
                            <div class="bg-base-200/50 p-3 rounded-lg text-base-content/70 group-hover:text-primary transition-colors flex-shrink-0">
                                {!! $group->getIcon() !!}
                            </div>
                            
                            <div>
                                <h2 class="text-lg font-semibold text-base-content group-hover:text-primary transition-colors">{{ $group->label }}</h2>
                                @if ($group->description)
                                    <p class="text-sm text-base-content/60 mt-1 line-clamp-2 leading-relaxed">{{ $group->description }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

    </div>
@endsection