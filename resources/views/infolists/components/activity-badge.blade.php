@php
    $color = $getColor($getState()) ?? 'gray';
    $size = $getSize($getState())->value ?? 'md';
    $icon = $getIcon($getState());
@endphp

@if (!empty($getState()) || !empty($getPlaceholder()))
    <x-filament::badge :color=$color :size=$size :icon=$icon>
        {{ $getState() ?? $getPlaceholder() }}
    </x-filament::badge>
@endif
