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
                    @class(['flex flex-col'])>
                    <!-- Item -->
                    <div class="flex gap-x-3">
                        <!-- Icon -->
                        <div @class([
                            'relative last:after:hidden',
                            'after:absolute after:top-7 after:bottom-0 after:start-4 after:w-px after:-translate-x-[0.5px] after:bg-gray-300 dark:after:bg-gray-700' => !$loop->last,
                        ])>
                            {{ $activityIcon }}
                        </div>
                        <!-- End Icon -->

                        <!-- Right Content -->
                        <div @class([
                            'grow pt-1 space-y-1',
                            'mb-7' => !$loop->last,
                            'mb-0' => $loop->last,
                        ])>
                            <div class="flex items-center justify-between space-x-5">
                                <!-- Title -->
                                {{ $activityTitle }}
                                <!-- End Title -->
                                {{-- Date --}}
                                {{ $activityDate }}
                                {{-- End Date --}}
                            </div>
                            {{-- Description --}}
                            {{ $activityDescription }}
                            {{-- End Description --}}
                        </div>
                        <!-- End Right Content -->
                    </div>
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
    @else
        <x-activity-timeline::empty-state :description="$getEmptyStateDescription()" :heading="$getEmptyStateHeading()" :icon="$getEmptyStateIcon()" />
    @endif
</x-filament::section>
