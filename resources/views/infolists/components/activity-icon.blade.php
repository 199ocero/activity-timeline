@if ($icon = $getIcon($getState()))
    @php
        $color = $getColor($getState()) ?? 'gray';
    @endphp

    <div class="relative z-10 w-8 h-8 flex justify-center items-center">
        <span @class([
                'flex flex-shrink-0 p-[5px] w-8 h-8 justify-center items-center dark:border rounded-full dark:bg-gray-800 dark:border-gray-700',
                match ($color) {
                    'gray' => 'bg-gray-100',
                    default => 'fi-color-custom bg-custom-100',
                },
            ]) @style([
                \Filament\Support\get_color_css_variables($color, shades: [100, 800], alias: 'infolists::components.icon-entry.item') => $color !== 'gray',
            ])>
            <x-filament::icon :icon="$icon" @class([
                    'fi-in-icon-item fi-in-icon-item-size-md h-4 w-4',
                    match ($color) {
                        'gray' => 'fi-color-gray text-gray-400 dark:text-gray-500',
                        default => 'fi-color-custom text-custom-500 dark:text-custom-400',
                    },
                ]) @style([
                    \Filament\Support\get_color_css_variables($color, shades: [400, 500], alias: 'infolists::components.icon-entry.item') => $color !== 'gray',
                ]) />
        </span>
    </div>
@else
    <div class="relative z-10 w-8 h-8 flex justify-center items-center">
        <div class="w-2 h-2 rounded-full bg-gray-400 dark:bg-gray-600"></div>
    </div>
@endif
