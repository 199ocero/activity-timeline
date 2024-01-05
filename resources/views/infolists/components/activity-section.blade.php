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
                        {{ $activityIcon }}
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
