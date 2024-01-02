@php
    use Filament\Infolists\Components\IconEntry\IconEntrySize;
@endphp
<x-filament::section :aside="$isAside()">
    <x-slot name="heading">
        {{ $getLabel() ?? $getHeading() }}
    </x-slot>

    <x-slot name="description">
        {{ $getDescription() ?? '' }}
    </x-slot>

    @if (count($childComponentContainers = $getChildComponentContainers()) &&
            count($getChildComponentContainers()[0]->getComponents()) > 0)
        @if ($getDirection() == \App\Infolists\Enums\Direction::Vertical)
            <ol class="relative ml-3 border-gray-300 border-s dark:border-gray-700">
                @foreach ($childComponentContainers as $index => $container)
                    @php
                        $activityIcon = null;
                        $activityBadge = null;
                        $activityTitle = null;
                        $activityDate = null;
                        $activityDescription = null;

                        $notEmptyCount = 0;
                        $isActivityBadgeEmpty = true;

                        foreach ($container->getComponents() as $component) {
                            switch ($component->getViewIdentifier()) {
                                case 'activityIcon':
                                    $activityIcon = $component;
                                    break;
                                case 'activityBadge':
                                    $activityBadge = $component;
                                    if ($activityBadge->getState()) {
                                        $notEmptyCount++;
                                        $isActivityBadgeEmpty = false;
                                    }
                                    break;
                                case 'activityTitle':
                                    $activityTitle = $component;
                                    if ($activityTitle->getState()) {
                                        $notEmptyCount++;
                                    }
                                    break;
                                case 'activityDate':
                                    $activityDate = $component;
                                    if ($activityDate->getState()) {
                                        $notEmptyCount++;
                                    }
                                    break;
                                case 'activityDescription':
                                    $activityDescription = $component;
                                    if ($activityDescription->getState()) {
                                        $notEmptyCount++;
                                    }
                                    break;
                                default:
                                    # code...
                                    break;
                            }
                        }

                        $isComponentEmpty = false;
                        $isOnlyDate = false;

                        if ($notEmptyCount == 0 || $notEmptyCount == 1) {
                            $isComponentEmpty = true;
                        }

                        if ($notEmptyCount == 1 && $activityDate) {
                            $isOnlyDate = true;
                        }

                    @endphp
                    <li @class([
                        'ms-8',
                        'mb-0' => $loop->last,
                        'mb-8' => !$loop->last && !$isComponentEmpty && !$isOnlyDate,
                        'mb-14' => !$loop->last && $isComponentEmpty,
                        'mb-10' => !$loop->last && $isOnlyDate,
                    ])>
                        <div @class([
                            'flex',
                            'items-center' => $isComponentEmpty,
                            'items-start' => !$isComponentEmpty,
                        ])>
                            @php
                                $color = $activityIcon ? $activityIcon->getColor($activityIcon->getState()) : 'gray';
                                $size = $activityIcon ? $activityIcon->getSize($activityIcon->getState()) : IconEntrySize::Medium;
                                $icon = $activityIcon ? $activityIcon->getIcon($activityIcon->getState()) : null;
                            @endphp

                            @if ($icon)
                                <span @class([
                                    'absolute flex items-center justify-center rounded-full ring-4 ring-white dark:ring-gray-900 dark:bg-gray-800',
                                    match ($size) {
                                        IconEntrySize::ExtraSmall, 'xs' => 'w-5 h-5 -start-[10px] p-[2px]',
                                        IconEntrySize::Small, 'sm' => 'h-6 w-6 -start-[12px] p-[5px]',
                                        IconEntrySize::Medium, 'md' => 'h-8 w-8 -start-[16px] p-[7px]',
                                        IconEntrySize::Large, 'lg' => 'h-9 w-9 -start-[18px] p-[8px]',
                                        IconEntrySize::ExtraLarge, 'xl' => 'h-10 w-10 -start-[20px] p-[10px]',
                                        default => 'h-8 w-8 -start-[16px] p-[7px]',
                                    },
                                    match ($color) {
                                        'gray' => 'bg-gray-100',
                                        default => 'fi-color-custom bg-custom-100',
                                    },
                                ]) @style([
                                    \Filament\Support\get_color_css_variables($color, shades: [100, 800], alias: 'infolists::components.icon-entry.item') => $color !== 'gray',
                                ])>
                                    {{ $activityIcon }}
                                </span>
                            @else
                                <div @class([
                                    'absolute w-4 h-4 bg-gray-200 border border-white rounded-full -start-2 dark:border-gray-900 dark:bg-gray-700',
                                    match ($size) {
                                        IconEntrySize::ExtraSmall, 'xs' => 'mt-[1.5px]',
                                        IconEntrySize::Small, 'sm' => 'mt-[2px]',
                                        IconEntrySize::Medium, 'md' => 'mt-[2.5px]',
                                        IconEntrySize::Large, 'lg' => 'mt-[3px]',
                                        IconEntrySize::ExtraLarge, 'xl' => 'mt-[3.5px]',
                                        default => 'mt-[2.5px]',
                                    },
                                ])>
                                </div>
                            @endif
                            <div @class([
                                'flex flex-col',
                                'space-y-2' => !$isOnlyDate,
                                'space-y-0' => $isOnlyDate,
                            ])>
                                @if (!$isActivityBadgeEmpty)
                                    <div class="flex mt-1">
                                        {{ $activityBadge }}
                                    </div>
                                @endif
                                {{ $activityTitle }}
                                {{ $activityDate }}
                                {{ $activityDescription }}
                            </div>
                        </div>
                    </li>
                @endforeach
            </ol>
        @elseif ($getDirection() == \App\Infolists\Enums\Direction::Horizontal)
            <ol @class([
                'md:grid md:gap-y-10',
                match ($getHorizontalItems('md')) {
                    2 => 'md:grid-cols-2',
                    3 => 'md:grid-cols-3',
                    4 => 'md:grid-cols-4',
                    5 => 'md:grid-cols-5',
                    6 => 'md:grid-cols-6',
                    7 => 'md:grid-cols-7',
                    8 => 'md:grid-cols-8',
                    9 => 'md:grid-cols-9',
                    10 => 'md:grid-cols-10',
                    11 => 'md:grid-cols-11',
                    12 => 'md:grid-cols-12',
                    default => 'md:grid-cols-2',
                },
                match ($getHorizontalItems('lg')) {
                    2 => 'lg:grid-cols-2',
                    3 => 'lg:grid-cols-3',
                    4 => 'lg:grid-cols-4',
                    5 => 'lg:grid-cols-5',
                    6 => 'lg:grid-cols-6',
                    7 => 'lg:grid-cols-7',
                    8 => 'lg:grid-cols-8',
                    9 => 'lg:grid-cols-9',
                    10 => 'lg:grid-cols-10',
                    11 => 'lg:grid-cols-11',
                    12 => 'lg:grid-cols-12',
                    default => 'lg:grid-cols-3',
                },
                'relative ml-3 md:ml-0 md:border-0 border-gray-300 border-s dark:border-gray-700',
            ])>
                @foreach ($childComponentContainers as $index => $container)
                    @php
                        $activityIcon = null;
                        $activityBadge = null;
                        $activityTitle = null;
                        $activityDate = null;
                        $activityDescription = null;

                        $notEmptyCount = 0;
                        $isActivityBadgeEmpty = true;

                        foreach ($container->getComponents() as $component) {
                            switch ($component->getViewIdentifier()) {
                                case 'activityIcon':
                                    $activityIcon = $component;
                                    break;
                                case 'activityBadge':
                                    $activityBadge = $component;
                                    if ($activityBadge->getState()) {
                                        $notEmptyCount++;
                                        $isActivityBadgeEmpty = false;
                                    }
                                    break;
                                case 'activityTitle':
                                    $activityTitle = $component;
                                    if ($activityTitle->getState()) {
                                        $notEmptyCount++;
                                    }
                                    break;
                                case 'activityDate':
                                    $activityDate = $component;
                                    if ($activityDate->getState()) {
                                        $notEmptyCount++;
                                    }
                                    break;
                                case 'activityDescription':
                                    $activityDescription = $component;
                                    if ($activityDescription->getState()) {
                                        $notEmptyCount++;
                                    }
                                    break;
                                default:
                                    # code...
                                    break;
                            }
                        }

                        $isComponentEmpty = false;
                        $isOnlyDate = false;

                        if ($notEmptyCount == 0 || $notEmptyCount == 1) {
                            $isComponentEmpty = true;
                        }

                        if ($notEmptyCount == 1 && $activityDate) {
                            $isOnlyDate = true;
                        }
                    @endphp
                    <li @class([
                        'md:relative ms-8 md:ms-0',
                        'mb-8 md:mb-0' => !$loop->last && !$isComponentEmpty && !$isOnlyDate,
                        'mb-14 md:mb-0' => !$loop->last && $isComponentEmpty,
                        'mb-10 md:mb-0' => !$loop->last && $isOnlyDate,
                    ])>
                        <div @class([
                            'flex md:items-center md:mb-3',
                            'items-center' => $isComponentEmpty,
                            'items-start' => !$isComponentEmpty,
                        ])>
                            @php
                                $color = $activityIcon ? $activityIcon->getColor($activityIcon->getState()) : 'gray';
                                $size = $activityIcon ? $activityIcon->getSize($activityIcon->getState()) : IconEntrySize::Medium;
                                $icon = $activityIcon ? $activityIcon->getIcon($activityIcon->getState()) : null;
                            @endphp

                            @if ($icon)
                                <span @class([
                                    'md:z-10 md:p-[8px] flex items-center justify-center rounded-full ring-4 ring-white dark:ring-gray-900 dark:bg-gray-800',
                                    'absolute md:static',
                                    match ($size) {
                                        IconEntrySize::ExtraSmall, 'xs' => 'w-5 h-5 -start-[10px] p-[2px]',
                                        IconEntrySize::Small, 'sm' => 'h-6 w-6 -start-[12px] p-[5px]',
                                        IconEntrySize::Medium, 'md' => 'h-8 w-8 -start-[16px] p-[7px]',
                                        IconEntrySize::Large, 'lg' => 'h-9 w-9 -start-[18px] p-[8px]',
                                        IconEntrySize::ExtraLarge, 'xl' => 'h-10 w-10 -start-[20px] p-[10px]',
                                        default => 'h-8 w-8 -start-[16px] p-[7px]',
                                    },
                                    match ($color) {
                                        'gray' => 'bg-gray-100',
                                        default => 'fi-color-custom bg-custom-100',
                                    },
                                ]) @style([
                                    \Filament\Support\get_color_css_variables($color, shades: [100, 800], alias: 'infolists::components.icon-entry.item') => $color !== 'gray',
                                ])>
                                    {{ $activityIcon }}
                                </span>
                                @if (!$loop->last)
                                    <div class="hidden md:flex w-full bg-gray-200 h-[1px] dark:bg-gray-700"></div>
                                @endif
                            @else
                                <div @class([
                                    'absolute w-4 h-4 bg-gray-200 border border-white rounded-full -start-0.5 dark:border-gray-900 dark:bg-gray-700',
                                    match ($size) {
                                        IconEntrySize::ExtraSmall,
                                        'xs'
                                            => 'mt-[1.5px] md:mt-[10px] -start-[8px] md:-start-0',
                                        IconEntrySize::Small,
                                        'sm'
                                            => 'mt-[2px] md:mt-[12px] -start-[8px] md:-start-0',
                                        IconEntrySize::Medium,
                                        'md'
                                            => 'mt-[2.5px] md:mt-[16px] -start-[8px] md:-start-0',
                                        IconEntrySize::Large,
                                        'lg'
                                            => 'mt-[3px] md:mt-[18px] -start-[8px] md:-start-0',
                                        IconEntrySize::ExtraLarge,
                                        'xl'
                                            => 'mt-[2.5px] md:mt-[20px] -start-[8px] md:-start-0',
                                        default => 'mt-[2.5px] md:mt-[16px] -start-[8px] md:-start-0',
                                    },
                                ])>
                                </div>
                                <div @class([
                                    'hidden md:flex w-full bg-gray-200 h-[1px] dark:bg-gray-700',
                                    match ($size) {
                                        IconEntrySize::ExtraSmall, 'xs' => 'mt-[10px]',
                                        IconEntrySize::Small, 'sm' => 'mt-[12px]',
                                        IconEntrySize::Medium, 'md' => 'mt-[16px]',
                                        IconEntrySize::Large, 'lg' => 'mt-[18px]',
                                        IconEntrySize::ExtraLarge, 'xl' => 'mt-[20px]',
                                        default => 'mt-[16px]',
                                    },
                                ])></div>
                            @endif
                        </div>
                        <div @class([
                            'flex flex-col',
                            'space-y-2' => !$isOnlyDate,
                            'space-y-0' => $isOnlyDate,
                            'mr-5',
                        ])>
                            @if (!$isActivityBadgeEmpty)
                                <div class="flex mt-1">
                                    {{ $activityBadge }}
                                </div>
                            @endif
                            {{ $activityTitle }}
                            {{ $activityDate }}
                            {{ $activityDescription }}
                        </div>
                    </li>
                @endforeach
            </ol>
        @endif
    @endif
</x-filament::section>
