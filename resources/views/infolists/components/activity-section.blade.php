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
            $itemsCount = count($childComponentContainers);
        @endphp

        <div x-data="{ itemsCount: @js($itemsCount), itemsToShow: @js($getItemsToShow() ?? $itemsCount) }">
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
                <div x-show="@js($index) < itemsToShow" @class([
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
                        <div class="grow pt-0.5 mb-10 space-y-1">
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

            <!-- Item -->
            <div x-show="itemsToShow < itemsCount" class="ps-[7px] flex gap-x-3">
                <button x-on:click="itemsToShow += itemsToShow" type="button"
                    class="inline-flex items-center text-sm font-medium text-blue-600 hs-collapse-toggle hs-collapse-open:hidden text-start gap-x-1 decoration-2 hover:underline dark:text-blue-500 dark:focus:outline-none dark:focus:ring-1 dark:focus:ring-gray-600">
                    Show older
                </button>
            </div>
            <!-- End Item -->
        </div>
    @endif
</x-filament::section>
