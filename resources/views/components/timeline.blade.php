@php
    use Filament\Support\Enums\IconSize;
    use Filament\Support\Icons\Heroicon;
    use Filament\Support\View\Components\BadgeComponent;
    use Illuminate\View\ComponentAttributeBag;
    use JaOcero\ActivityTimeline\Enums\IconAnimation;

    use function Filament\Support\generate_icon_html;
    use function Filament\Support\get_color_css_variables;

    /** @var \JaOcero\ActivityTimeline\Components\ActivitySection $section */
    $isCompact = $section->isCompact();
    $isHeadingVisible = $section->isHeadingVisible() && (! $section->isLabelHidden());
    $itemsCount = count($items);
    $showItemsCount = $section->getShowItemsCount();
    $isRevealable = filled($showItemsCount) && ($itemsCount > $showItemsCount);
    $showItemsLabel = $section->getShowItemsLabel();
    $loadMoreMethod = $section->hasMoreItems() ? $section->getLoadMoreMethod() : null;
    $livewireKey = $section->getLivewireKey();

    // Items are revealed in chunks, so a hidden chunk costs one Alpine binding instead of one per item.
    $chunks = array_chunk($items, $isRevealable ? $showItemsCount : max($itemsCount, 1), preserve_keys: true);
    $chunksCount = count($chunks);
    $lastIndex = $itemsCount - 1;

    $nodeColorShades = [50, 400, 500, 600];
@endphp

<div
    {{
        $section->getExtraAttributeBag()->class([
            'fi-timeline',
            'fi-timeline-compact' => $isCompact,
        ])
    }}
>
    <x-filament::section
        :aside="$section->isAside() && $isHeadingVisible"
        :compact="$isCompact"
        :contained="$section->isContained()"
        :description="$isHeadingVisible ? $section->getDescription() : null"
        :heading="$isHeadingVisible ? $section->getLabel() : null"
        :icon="$isHeadingVisible ? $section->getSectionIcon() : null"
        class="fi-timeline-section"
    >
        @if ($itemsCount)
            <div
                @if ($isRevealable)
                    x-data="{
                        page: 1,
                        pages: @js($chunksCount),
                        step: @js($showItemsCount),
                        total: @js($itemsCount),
                        reveal() {
                            this.page++

                            if (! @js($section->shouldScrollOnShowMore())) {
                                return
                            }

                            // Wait for the collapse animation, then bring the new items into view.
                            setTimeout(() => {
                                const target = (this.page < this.pages) ? this.$refs.showMore : this.$refs.lastItem

                                target?.scrollIntoView({
                                    behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth',
                                    block: 'nearest',
                                })
                            }, 320)
                        },
                    }"
                @endif
            >
                @foreach ($chunks as $chunkIndex => $chunk)
                    <ol
                        role="list"
                        @if ($chunkIndex > 0)
                            x-show="page > @js($chunkIndex)"
                            x-collapse.duration.300ms
                            style="display: none"
                        @endif
                        @class([
                            'fi-timeline-list',
                            'fi-timeline-chunk transition duration-500 ease-out starting:-translate-y-2 starting:opacity-0 motion-reduce:transition-none' => $chunkIndex > 0,
                        ])
                    >
                        @foreach ($chunk as $index => $item)
                            @php
                                $isLast = ($index === $lastIndex) && blank($loadMoreMethod);
                                $node = $item['node'];
                            @endphp

                            @if (filled($item['group']))
                                <li class="fi-timeline-group-label relative flex gap-x-3">
                                    @unless ($index === 0)
                                        <span
                                            aria-hidden="true"
                                            @class([
                                                'fi-timeline-item-rail absolute inset-y-0 w-px bg-gray-200 dark:bg-white/10',
                                                'start-3' => $isCompact,
                                                'start-4' => ! $isCompact,
                                            ])
                                        ></span>
                                    @endunless

                                    <div @class(['shrink-0', 'w-6' => $isCompact, 'w-8' => ! $isCompact])></div>

                                    <p
                                        @class([
                                            'pb-3 text-xs font-semibold tracking-wide text-gray-500 uppercase dark:text-gray-400',
                                            'pt-2' => $index !== 0,
                                        ])
                                    >
                                        {{ $item['group'] }}
                                    </p>
                                </li>
                            @endif

                            <li
                                wire:key="{{ $livewireKey }}.items.{{ $item['key'] }}"
                            @if ($isRevealable && ($index === $lastIndex))
                                x-ref="lastItem"
                            @endif
                                @class([
                                    'fi-timeline-item relative flex gap-x-3',
                                'scroll-mb-32' => $isRevealable && ($index === $lastIndex),
                                    'pb-4' => $isCompact && (! $isLast),
                                    'pb-7' => (! $isCompact) && (! $isLast),
                                ])
                            >
                                @unless ($isLast)
                                    <span
                                        aria-hidden="true"
                                        @class([
                                            'fi-timeline-item-rail absolute bottom-0 w-px bg-gray-200 dark:bg-white/10',
                                            'start-3 top-6' => $isCompact,
                                            'start-4 top-8' => ! $isCompact,
                                        ])
                                    ></span>
                                @endunless

                                <div
                                    @class([
                                        'relative flex shrink-0 items-center justify-center',
                                        'size-6' => $isCompact,
                                        'size-8' => ! $isCompact,
                                    ])
                                >
                                    @if (filled($node['icon'] ?? null))
                                        @php
                                            $animation = $node['animation'];
                                        @endphp

                                        <span
                                            style="{{ get_color_css_variables($node['color'], shades: $nodeColorShades) }}"
                                            @class([
                                                'fi-timeline-item-node relative flex items-center justify-center rounded-full bg-custom-50 text-custom-600 ring-1 ring-custom-600/20 ring-inset dark:bg-custom-400/10 dark:text-custom-400 dark:ring-custom-400/30',
                                                'size-6' => $isCompact,
                                                'size-8' => ! $isCompact,
                                            ])
                                        >
                                            @if ($animation === IconAnimation::Ping)
                                                <span aria-hidden="true" class="absolute inset-0 animate-ping rounded-full bg-custom-400 opacity-30"></span>
                                            @endif

                                            <span
                                                @class([
                                                    'relative flex',
                                                    'animate-spin' => $animation === IconAnimation::Spin,
                                                    'animate-pulse' => $animation === IconAnimation::Pulse,
                                                    'animate-bounce' => $animation === IconAnimation::Bounce,
                                                    is_string($animation) ? $animation : null,
                                                ])
                                            >
                                                {!! $node['icon'] !!}
                                            </span>
                                        </span>
                                    @else
                                        <span
                                            style="{{ get_color_css_variables($node['color'] ?? 'gray', shades: $nodeColorShades) }}"
                                            class="fi-timeline-item-dot size-2.5 rounded-full bg-custom-400 ring-4 ring-custom-50 dark:bg-custom-500 dark:ring-custom-400/10"
                                        ></span>
                                    @endif
                                </div>

                                <div
                                    @class([
                                        'fi-timeline-item-content min-w-0 flex-1',
                                        'pt-0.5' => $isCompact,
                                        'pt-1.5' => ! $isCompact,
                                    ])
                                >
                                    <div class="fi-timeline-item-header flex flex-col gap-1 sm:flex-row sm:items-start sm:justify-between sm:gap-4">
                                        <div class="flex min-w-0 flex-wrap items-center gap-x-2 gap-y-1">
                                            @if ($causer = $item['causer'])
                                                <span class="fi-timeline-item-causer inline-flex items-center gap-1.5" title="{{ $causer['name'] }}">
                                                    @if (filled($causer['avatarUrl']))
                                                        <img
                                                            src="{{ $causer['avatarUrl'] }}"
                                                            alt="{{ $causer['name'] }}"
                                                            loading="lazy"
                                                            class="fi-avatar fi-circular size-5 rounded-full object-cover"
                                                        />
                                                    @else
                                                        <span class="flex size-5 items-center justify-center rounded-full bg-gray-100 text-[0.625rem] font-semibold text-gray-600 dark:bg-white/10 dark:text-gray-300">
                                                            {{ $causer['initials'] }}
                                                        </span>
                                                    @endif

                                                    @if ($causer['isNameVisible'] && filled($causer['name']))
                                                        <span class="text-sm font-medium text-gray-950 dark:text-white">
                                                            {{ $causer['name'] }}
                                                        </span>
                                                    @endif
                                                </span>
                                            @endif

                                            @if (filled($item['title']))
                                                <h3
                                                    @class([
                                                        'fi-timeline-item-title text-gray-950 dark:text-white',
                                                        'text-sm' => $isCompact,
                                                        'text-sm sm:text-base' => ! $isCompact,
                                                    ])
                                                >
                                                    {!! $item['title'] !!}
                                                </h3>
                                            @endif

                                            @foreach ($item['badges'] as $badge)
                                                <span
                                                    {{
                                                        (new ComponentAttributeBag)
                                                            ->class(['fi-badge fi-size-sm fi-timeline-item-badge'])
                                                            ->color(BadgeComponent::class, $badge['color'])
                                                    }}
                                                >
                                                    <span class="fi-badge-label-ctn">
                                                        <span class="fi-badge-label">{!! $badge['html'] !!}</span>
                                                    </span>
                                                </span>
                                            @endforeach
                                        </div>

                                        @if (filled($item['dateHtml']))
                                            <time
                                                @if (filled($item['dateTime']))
                                                    datetime="{{ $item['dateTime'] }}"
                                                @endif
                                                @if (filled($item['dateTooltip']))
                                                    x-tooltip="{ content: @js($item['dateTooltip']), theme: $store.theme }"
                                                @endif
                                                class="fi-timeline-item-date shrink-0 text-xs font-medium text-gray-500 sm:pt-0.5 dark:text-gray-400"
                                            >
                                                {!! $item['dateHtml'] !!}
                                            </time>
                                        @endif
                                    </div>

                                    @if (filled($item['description']))
                                        <div class="fi-timeline-item-description mt-1 text-sm text-gray-600 dark:text-gray-400">
                                            {!! $item['description'] !!}
                                        </div>
                                    @endif

                                    @if (count($item['changes']))
                                        <dl class="fi-timeline-item-changes mt-2 divide-y divide-gray-950/5 overflow-hidden rounded-lg bg-gray-50 text-xs ring-1 ring-gray-950/5 dark:divide-white/5 dark:bg-white/5 dark:ring-white/10">
                                            @foreach ($item['changes'] as $change)
                                                <div class="fi-timeline-item-change grid gap-1 px-3 py-2 sm:grid-cols-[minmax(0,10rem)_1fr] sm:gap-3">
                                                    <dt class="truncate font-medium text-gray-700 dark:text-gray-200">
                                                        {{ $change['label'] }}
                                                    </dt>

                                                    <dd class="flex min-w-0 flex-wrap items-center gap-1.5">
                                                        @if ($change['hasOld'])
                                                            <span
                                                                @class([
                                                                    'fi-timeline-item-change-old rounded-md px-1.5 py-0.5 break-all',
                                                                    'bg-danger-50 text-danger-700 line-through decoration-danger-700/40 dark:bg-danger-400/10 dark:text-danger-400' => $change['hasNew'],
                                                                    'bg-gray-100 text-gray-700 dark:bg-white/10 dark:text-gray-300' => ! $change['hasNew'],
                                                                ])
                                                            >
                                                                {{ $change['old'] ?? __('activity-timeline::timeline.changes.empty') }}
                                                            </span>
                                                        @endif

                                                        @if ($change['hasOld'] && $change['hasNew'])
                                                            {{ generate_icon_html(Heroicon::ArrowLongRight, attributes: (new ComponentAttributeBag)->class(['shrink-0 text-gray-400 rtl:rotate-180 dark:text-gray-500']), size: IconSize::Small) }}
                                                        @endif

                                                        @if ($change['hasNew'])
                                                            <span class="fi-timeline-item-change-new rounded-md bg-success-50 px-1.5 py-0.5 break-all text-success-700 dark:bg-success-400/10 dark:text-success-400">
                                                                {{ $change['new'] ?? __('activity-timeline::timeline.changes.empty') }}
                                                            </span>
                                                        @endif
                                                    </dd>
                                                </div>
                                            @endforeach
                                        </dl>
                                    @endif

                                    @if (count($item['extras']))
                                        <div class="fi-timeline-item-extras mt-2 grid gap-2">
                                            @foreach ($item['extras'] as $extra)
                                                {!! $extra !!}
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ol>
                @endforeach

                @if ($isRevealable)
                    <div
                        x-show="page < pages"
                        x-transition.opacity.duration.200ms
                        x-ref="showMore"
                        class="fi-timeline-show-more relative flex scroll-mb-32 items-center gap-x-3"
                    >
                        <span
                            aria-hidden="true"
                            @class([
                                'absolute top-0 h-3 border-s border-dashed border-gray-300 dark:border-white/15',
                                'start-3' => $isCompact,
                                'start-4' => ! $isCompact,
                            ])
                        ></span>

                        <div @class(['shrink-0', 'w-6' => $isCompact, 'w-8' => ! $isCompact])></div>

                        <div class="pt-4">
                            <x-filament::link
                                tag="button"
                                type="button"
                                :color="$section->getShowItemsColor()"
                                :icon="$section->getShowItemsIcon()"
                                x-on:click="reveal()"
                                size="sm"
                            >
                                @if (filled($showItemsLabel))
                                    {{ $showItemsLabel }}
                                @else
                                    <span
                                        x-text="@js(__('activity-timeline::timeline.show_more.label_with_count')).replace(':count', Math.min(step, total - (page * step)))"
                                    >
                                        {{ __('activity-timeline::timeline.show_more.label') }}
                                    </span>
                                @endif
                            </x-filament::link>
                        </div>
                    </div>
                @endif

                @if (filled($loadMoreMethod))
                    <div
                        @if ($isRevealable)
                            x-show="page >= pages"
                            x-transition.opacity.duration.200ms
                            style="display: none"
                        @endif
                        class="fi-timeline-load-more relative flex items-center gap-x-3"
                    >
                        <span
                            aria-hidden="true"
                            @class([
                                'absolute top-0 h-3 border-s border-dashed border-gray-300 dark:border-white/15',
                                'start-3' => $isCompact,
                                'start-4' => ! $isCompact,
                            ])
                        ></span>

                        <div @class(['shrink-0', 'w-6' => $isCompact, 'w-8' => ! $isCompact])></div>

                        <div class="flex items-center gap-2 pt-4">
                            <x-filament::link
                                tag="button"
                                type="button"
                                color="gray"
                                :icon="Heroicon::ArrowDown"
                                wire:click="{{ $loadMoreMethod }}"
                                wire:loading.attr="disabled"
                                wire:target="{{ $loadMoreMethod }}"
                                size="sm"
                            >
                                {{ $section->getLoadMoreLabel() }}
                            </x-filament::link>

                            <x-filament::loading-indicator
                                wire:loading
                                wire:target="{{ $loadMoreMethod }}"
                                class="size-4 text-gray-400"
                            />
                        </div>
                    </div>
                @endif
            </div>
        @else
            <div class="fi-timeline-empty-state flex flex-col items-center px-6 py-10 text-center">
                <div class="fi-timeline-empty-state-icon-ctn mb-4 rounded-full bg-gray-100 p-3 dark:bg-gray-500/20">
                    {{ generate_icon_html($section->getEmptyStateIcon(), attributes: (new ComponentAttributeBag)->class(['fi-timeline-empty-state-icon text-gray-500 dark:text-gray-400']), size: IconSize::Large) }}
                </div>

                <h4 class="fi-timeline-empty-state-heading text-base font-semibold text-gray-950 dark:text-white">
                    {{ $section->getEmptyStateHeading() }}
                </h4>

                @if (filled($emptyStateDescription = $section->getEmptyStateDescription()))
                    <p class="fi-timeline-empty-state-description mt-1 max-w-md text-sm text-gray-500 dark:text-gray-400">
                        {{ $emptyStateDescription }}
                    </p>
                @endif
            </div>
        @endif
    </x-filament::section>
</div>
