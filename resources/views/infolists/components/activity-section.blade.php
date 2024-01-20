<x-activity-timeline::section :aside="$isAside()">
    <x-slot name="heading">
        {{ $getLabel() ?? $getHeading() }}
    </x-slot>

    <x-slot name="description">
        {{ $getDescription() }}
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


                <div x-show="@js($index) < totalShowItemsCount" x-collapse :key="@js(rand())"
                    @class(['flex flex-col'])>

                    <div class="flex gap-x-3">

                        <div @class([
                            'relative last:after:hidden',
                            'after:absolute after:top-8 after:bottom-0 after:start-4 after:w-px after:-translate-x-[0.5px] after:bg-gray-300 dark:after:bg-gray-700' => !$loop->last,
                        ])>
                            {{ $activityIcon }}
                        </div>

                        <div @class([
                            'fi-timeline-item',
                            'grow pt-1 space-y-1',
                            'mb-7' => !$loop->last,
                            'mb-0' => $loop->last,
                        ])>
                            <div @class([
                                'fi-timeline-item-heading flex',
                                'flex-col items-start space-y-1 md:space-y-0 md:items-center md:flex-row md:justify-between md:space-x-5' => !$isAside(),
                                'flex-col items-start space-y-1 lg:space-y-0 lg:items-center lg:flex-row lg:justify-between lg:space-x-5' => $isAside(),
                            ])>

                                {{ $activityTitle }}

                                {{ $activityDate }}

                            </div>

                            {{ $activityDescription }}

                        </div>

                    </div>
                </div>
            @endforeach

            <div x-show="totalShowItemsCount < childItemsCount">
                @php
                    $icon = $getShowItemsIcon();
                    $label = $getShowItemsLabel();
                    $color = $getShowItemsColor();
                @endphp
                <x-filament::link x-on:click="totalShowItemsCount += showItemsCount" :icon="$icon" :color="$color"
                    class="ms-1.5 cursor-pointer hover:underline">
                    {{ $label }}
                </x-filament::link>
            </div>

        </div>
    @else
        <x-activity-timeline::empty-state :description="$getEmptyStateDescription()" :heading="$getEmptyStateHeading()" :icon="$getEmptyStateIcon()" />
    @endif
</x-activity-timeline::section>
