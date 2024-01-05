@php
    use Filament\Infolists\Components\IconEntry\IconEntrySize;
    use JaOcero\ActivityTimeline\Enums\Direction;
@endphp
<x-filament::section :aside="$isAside()">
    <x-slot name="heading">
        {{ $getLabel() ?? $getHeading() }}
    </x-slot>

    <x-slot name="description">
        {{ $getDescription() ?? '' }}
    </x-slot>

    @if (count($childComponentContainers = $getChildComponentContainers()) &&
            count($childComponentContainers[0]->getComponents()) > 0)
        @php
            $childItemsCount = count($childComponentContainers);
            $showItemsCount = $getShowItemsCount() ?? $childItemsCount;
        @endphp

        <div x-data="{ childItemsCount: @js($childItemsCount), showItemsCount: @js($showItemsCount), totalShowItemsCount: @js($showItemsCount) }">
            @foreach ($childComponentContainers as $index => $container)
                @php
                    $activityComponents = [
                        'activityIcon' => null,
                        'activityBadge' => null,
                        'activityTitle' => null,
                        'activityDate' => null,
                        'activityDescription' => null,
                    ];

                    foreach ($container->getComponents() as $component) {
                        $viewIdentifier = $component->getViewIdentifier();
                        if (array_key_exists($viewIdentifier, $activityComponents)) {
                            $activityComponents[$viewIdentifier] = $component;
                        }
                    }

                    extract($activityComponents);
                @endphp

                <!-- Timeline -->
                <div x-show="@js($index) < totalShowItemsCount" :key="@js(rand())"
                    @class([
                        'flex flex-col',
                        'mb-2' => !$loop->last,
                        'mb-0' => $loop->last,
                    ])>
                    <!-- Date -->
                    {{ $activityDate }}
                    <!-- End Date -->

                    <!-- Item -->
                    <div class="flex gap-x-3">
                        <!-- Icon -->
                        @if($activityIcon)
                            {{ $activityIcon }}
                        @else
                            <div
                                class="relative last:after:hidden after:absolute after:top-7 after:bottom-0 after:start-4 after:w-px after:-translate-x-[0.5px] after:bg-gray-200 dark:after:bg-gray-700">
                                <div @class([
                                    'relative z-10 flex items-center justify-center',
                                    match (IconEntrySize::Medium) {
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
                        <!-- End Icon -->

                        <!-- Right Content -->
                        <div class='mb-5 space-y-1 grow'>
                            {{-- Bagde --}}
                            @if ($activityBadge)
                                <div class="flex">
                                    {{ $activityBadge }}
                                </div>
                            @endif
                            {{-- End Badge --}}
                            {{-- Title --}}
                            {{ $activityTitle }}
                            {{-- End Title --}}
                            {{-- Description --}}
                            {{ $activityDescription }}
                            {{-- End Description --}}
                        </div>
                        <!-- End Right Content -->
                    </div>
                    <!-- End Item -->
                </div>
                <!-- End Timeline -->
            @endforeach

            <!-- Show More -->
            <div x-show="totalShowItemsCount < childItemsCount">
                @php
                    $icon = $getShowItemsIcon();
                    $label = $getShowItemsLabel();
                    $color = $getShowItemsColor();
                @endphp
                <x-filament::link x-on:click="totalShowItemsCount += showItemsCount" :icon="$icon" :color="$color"
                    class="cursor-pointer hover:underline">
                    {{ $label }}
                </x-filament::link>
            </div>
            <!-- End Show More -->
        </div>
    @endif
</x-filament::section>
