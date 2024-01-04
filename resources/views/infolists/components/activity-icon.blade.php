@php
    use Filament\Infolists\Components\IconEntry\IconEntrySize;
@endphp
@if ($icon = $getIcon($getState()))
    @php
        $color = $getColor($getState()) ?? 'gray';
        $size = $getSize($getState()) ?? IconEntrySize::Medium;
    @endphp
    <div
        class="relative last:after:hidden after:absolute after:top-7 after:bottom-0 after:start-4 after:w-px after:-translate-x-[0.5px] after:bg-gray-200 dark:after:bg-gray-700">
        <div class="relative z-10 flex items-center justify-center">
            <span @class([
                'flex flex-shrink-0 p-1 justify-center items-center dark:border rounded-full dark:bg-gray-800 dark:border-gray-700',
                match ($size) {
                    IconEntrySize::Small, 'sm' => 'w-7 h-7',
                    IconEntrySize::Medium, 'md' => 'w-8 h-8',
                    IconEntrySize::Large, 'lg' => 'w-9 h-9',
                    default => 'w-8 h-8',
                },
                match ($color) {
                    'gray' => 'bg-gray-100',
                    default => 'fi-color-custom bg-custom-100',
                },
            ]) @style([
                \Filament\Support\get_color_css_variables($color, shades: [100, 800], alias: 'infolists::components.icon-entry.item') => $color !== 'gray',
            ])>
                <x-filament::icon :icon="$icon" @class([
                    'fi-in-icon-item',
                    match ($size) {
                        IconEntrySize::Small, 'sm' => 'fi-in-icon-item-size-sm h-4 w-4',
                        IconEntrySize::Medium, 'md' => 'fi-in-icon-item-size-md h-5 w-5',
                        IconEntrySize::Large, 'lg' => 'fi-in-icon-item-size-lg h-6 w-6',
                        default => 'fi-in-icon-item-size-md h-5 w-5',
                    },
                    match ($color) {
                        'gray' => 'fi-color-gray text-gray-400 dark:text-gray-500',
                        default => 'fi-color-custom text-custom-500 dark:text-custom-400',
                    },
                ]) @style([
                    \Filament\Support\get_color_css_variables($color, shades: [400, 500], alias: 'infolists::components.icon-entry.item') => $color !== 'gray',
                ]) />
        </div>
    </div>
@else
    <div
        class="relative last:after:hidden after:absolute after:top-7 after:bottom-0 after:start-4 after:w-px after:-translate-x-[0.5px] after:bg-gray-200 dark:after:bg-gray-700">
        <div @class([
            'relative z-10 flex items-center justify-center',
            match ($size) {
                IconEntrySize::Small, 'sm' => 'w-7 h-7',
                IconEntrySize::Medium, 'md' => 'w-8 h-8',
                IconEntrySize::Large, 'lg' => 'w-9 h-9',
                default => 'w-8 h-8',
            },
        ])>
            <div class="w-2 h-2 bg-gray-400 rounded-full dark:bg-gray-600"></div>
        </div>
    </div>
@endif
