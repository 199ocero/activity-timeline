<?php

namespace JaOcero\ActivityTimeline\Components;

use BackedEnum;
use Carbon\CarbonInterface;
use Closure;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Schemas\Schema;
use Filament\Support\Enums\IconSize;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\View\ComponentAttributeBag;
use JaOcero\ActivityTimeline\Concerns\HasEmptyState;
use JaOcero\ActivityTimeline\Enums\IconAnimation;

use function Filament\Support\generate_icon_html;

class ActivitySection extends RepeatableEntry
{
    use HasEmptyState;

    protected string|Htmlable|Closure|null $description = null;

    protected int|Closure|null $showItemsCount = null;

    protected string|Closure|null $showItemsLabel = null;

    protected string|BackedEnum|Closure|null $showItemsIcon = null;

    protected string|Closure|null $showItemsColor = null;

    protected bool|Closure $shouldScrollOnShowMore = true;

    protected bool|Closure $isAside = false;

    protected bool|Closure $isHeadingVisible = true;

    protected bool|Closure $isCompact = false;

    protected bool|Closure $isReversed = false;

    protected bool|Closure $isGroupedByDate = false;

    protected bool|Closure $hasMoreItems = false;

    protected string|Closure|null $loadMoreMethod = null;

    protected string|Closure|null $loadMoreLabel = null;

    protected string|BackedEnum|Htmlable|Closure|null $sectionIcon = null;

    public function description(string|Htmlable|Closure|null $description = null): static
    {
        $this->description = $description;

        return $this;
    }

    public function aside(bool|Closure $condition = true): static
    {
        $this->isAside = $condition;

        return $this;
    }

    public function headingVisible(bool|Closure $condition = true): static
    {
        $this->isHeadingVisible = $condition;

        return $this;
    }

    /**
     * Use smaller icons and tighter spacing, e.g. inside a slide-over.
     */
    public function compact(bool|Closure $condition = true): static
    {
        $this->isCompact = $condition;

        return $this;
    }

    /**
     * Render the items in the opposite order to the state.
     */
    public function reverse(bool|Closure $condition = true): static
    {
        $this->isReversed = $condition;

        return $this;
    }

    /**
     * Separate items with "Today", "Yesterday" and date labels, based on the `ActivityDate` entry.
     */
    public function groupByDate(bool|Closure $condition = true): static
    {
        $this->isGroupedByDate = $condition;

        return $this;
    }

    public function sectionIcon(string|BackedEnum|Htmlable|Closure|null $icon): static
    {
        $this->sectionIcon = $icon;

        return $this;
    }

    /**
     * Only show the first `$count` items, and reveal `$count` more each time "Show more" is clicked.
     * Revealing happens in the browser without a request.
     */
    public function showItemsCount(int|Closure|null $count): static
    {
        $this->showItemsCount = $count;

        return $this;
    }

    public function showItemsLabel(string|Closure|null $label): static
    {
        $this->showItemsLabel = $label;

        return $this;
    }

    public function showItemsIcon(string|BackedEnum|Closure|null $icon = null): static
    {
        $this->showItemsIcon = $icon;

        return $this;
    }

    public function showItemsColor(string|Closure|null $color): static
    {
        $this->showItemsColor = $color;

        return $this;
    }

    /**
     * Scroll the newly revealed items into view after "Show more" is clicked. Enabled by default.
     */
    public function scrollOnShowMore(bool|Closure $condition = true): static
    {
        $this->shouldScrollOnShowMore = $condition;

        return $this;
    }

    /**
     * Render a "Load more" button that calls a method on the Livewire component,
     * so only the visible slice of a long timeline is ever queried and rendered.
     */
    public function loadMore(string|Closure|null $livewireMethod, bool|Closure $hasMoreItems = true, string|Closure|null $label = null): static
    {
        $this->loadMoreMethod = $livewireMethod;
        $this->hasMoreItems = $hasMoreItems;
        $this->loadMoreLabel = $label;

        return $this;
    }

    public function getDescription(): string|Htmlable|null
    {
        return $this->evaluate($this->description);
    }

    public function isAside(): bool
    {
        return (bool) $this->evaluate($this->isAside);
    }

    public function isHeadingVisible(): bool
    {
        return (bool) $this->evaluate($this->isHeadingVisible);
    }

    public function isCompact(): bool
    {
        return (bool) $this->evaluate($this->isCompact);
    }

    public function isReversed(): bool
    {
        return (bool) $this->evaluate($this->isReversed);
    }

    public function isGroupedByDate(): bool
    {
        return (bool) $this->evaluate($this->isGroupedByDate);
    }

    public function getSectionIcon(): string|BackedEnum|Htmlable|null
    {
        return $this->evaluate($this->sectionIcon);
    }

    public function getShowItemsCount(): ?int
    {
        $count = $this->evaluate($this->showItemsCount);

        return filled($count) && ($count > 0) ? (int) $count : null;
    }

    public function getShowItemsLabel(): ?string
    {
        return $this->evaluate($this->showItemsLabel);
    }

    public function getShowItemsIcon(): string|BackedEnum|null
    {
        return $this->evaluate($this->showItemsIcon) ?? Heroicon::ChevronDown;
    }

    public function getShowItemsColor(): string
    {
        return $this->evaluate($this->showItemsColor) ?? 'gray';
    }

    public function shouldScrollOnShowMore(): bool
    {
        return (bool) $this->evaluate($this->shouldScrollOnShowMore);
    }

    public function getLoadMoreMethod(): ?string
    {
        return $this->evaluate($this->loadMoreMethod);
    }

    public function hasMoreItems(): bool
    {
        return filled($this->getLoadMoreMethod()) && $this->evaluate($this->hasMoreItems);
    }

    public function getLoadMoreLabel(): string
    {
        return $this->evaluate($this->loadMoreLabel) ?? __('activity-timeline::timeline.load_more.label');
    }

    public function toEmbeddedHtml(): string
    {
        $items = $this->getItems();

        if ($this->isReversed()) {
            $items = array_reverse($items, preserve_keys: true);
        }

        return view('activity-timeline::components.timeline', [
            'section' => $this,
            'items' => $this->getTimelineItems($items),
        ])->render();
    }

    /**
     * Resolve every item into plain data once, so the view only has to print it.
     *
     * @param  array<array-key, Schema>  $items
     * @return array<int, array<string, mixed>>
     */
    protected function getTimelineItems(array $items): array
    {
        $isGroupedByDate = $this->isGroupedByDate();
        $isCompact = $this->isCompact();
        $previousGroup = null;
        $timelineItems = [];

        foreach ($items as $key => $item) {
            $timelineItem = $this->getTimelineItem($item, $key, $isCompact);

            if ($isGroupedByDate) {
                $group = $this->getDateGroupLabel($timelineItem['date']);

                $timelineItem['group'] = ($group !== $previousGroup) ? $group : null;

                $previousGroup = $group;
            }

            unset($timelineItem['date']);

            $timelineItems[] = $timelineItem;
        }

        return $timelineItems;
    }

    /**
     * @return array<string, mixed>
     */
    protected function getTimelineItem(Schema $item, string|int $key, bool $isCompact): array
    {
        $timelineItem = [
            'key' => $key,
            'node' => null,
            'causer' => null,
            'title' => null,
            'badges' => [],
            'dateHtml' => null,
            'dateTime' => null,
            'dateTooltip' => null,
            'date' => null,
            'description' => null,
            'changes' => [],
            'extras' => [],
            'group' => null,
        ];

        foreach ($item->getComponents() as $component) {
            match (true) {
                $component instanceof ActivityIcon => $timelineItem['node'] ??= $this->getNodeData($component, $isCompact),
                $component instanceof ActivityCauser => $timelineItem['causer'] ??= $this->getCauserData($component),
                $component instanceof ActivityTitle => $timelineItem['title'] ??= $component->getTimelineHtml(),
                $component instanceof ActivityBadge => $timelineItem['badges'][] = $this->getBadgeData($component),
                $component instanceof ActivityDate => $timelineItem = $this->fillDateData($timelineItem, $component),
                $component instanceof ActivityDescription => $timelineItem['description'] ??= $component->getTimelineHtml(),
                $component instanceof ActivityChanges => $timelineItem['changes'] = [...$timelineItem['changes'], ...$component->getChangeRows()],
                $component instanceof Htmlable => $timelineItem['extras'][] = $component->toHtml(),
                default => null,
            };
        }

        $timelineItem['badges'] = array_values(array_filter($timelineItem['badges']));

        return $timelineItem;
    }

    /**
     * @return array{icon: ?string, color: string|array<int|string, string>, animation: IconAnimation|string|null}
     */
    protected function getNodeData(ActivityIcon $component, bool $isCompact): array
    {
        $state = $component->getState();
        $icon = $component->getIcon($state);

        $iconHtml = filled($icon)
            ? generate_icon_html(
                $icon,
                attributes: (new ComponentAttributeBag)->class(['fi-timeline-item-icon']),
                size: $isCompact ? IconSize::ExtraSmall : IconSize::Small,
            )?->toHtml()
            : null;

        return [
            'icon' => $iconHtml,
            'color' => $component->getColor($state) ?? 'gray',
            'animation' => $component->getAnimation($state),
        ];
    }

    /**
     * @return array{name: ?string, avatarUrl: ?string, initials: ?string, isNameVisible: bool}|null
     */
    protected function getCauserData(ActivityCauser $component): ?array
    {
        $name = $component->getCauserName();
        $avatarUrl = $component->getAvatarUrl();

        if (blank($name) && blank($avatarUrl)) {
            return null;
        }

        return [
            'name' => $name,
            'avatarUrl' => $avatarUrl,
            'initials' => $component->getInitials(),
            'isNameVisible' => $component->isNameVisible(),
        ];
    }

    /**
     * @return array{html: string, color: string|array<int|string, string>}|null
     */
    protected function getBadgeData(ActivityBadge $component): ?array
    {
        $html = $component->getTimelineHtml();

        if (blank($html)) {
            return null;
        }

        return [
            'html' => $html,
            'color' => $component->getColor($component->getState()) ?? 'gray',
        ];
    }

    /**
     * @param  array<string, mixed>  $timelineItem
     * @return array<string, mixed>
     */
    protected function fillDateData(array $timelineItem, ActivityDate $component): array
    {
        if (filled($timelineItem['dateHtml'])) {
            return $timelineItem;
        }

        $date = $component->getTimelineDate();
        $tooltip = $component->getTooltip($component->getState());

        $timelineItem['date'] = $date;
        $timelineItem['dateHtml'] = $component->getTimelineHtml();
        $timelineItem['dateTime'] = $date?->toIso8601String();
        $timelineItem['dateTooltip'] = $tooltip instanceof Htmlable ? $tooltip->toHtml() : $tooltip;

        return $timelineItem;
    }

    protected function getDateGroupLabel(?CarbonInterface $date): ?string
    {
        if (! $date) {
            return null;
        }

        $now = now($date->getTimezone());

        return match (true) {
            $date->isSameDay($now) => __('activity-timeline::timeline.groups.today'),
            $date->isSameDay($now->copy()->subDay()) => __('activity-timeline::timeline.groups.yesterday'),
            $date->isSameYear($now) => $date->translatedFormat('F j'),
            default => $date->translatedFormat('F j, Y'),
        };
    }
}
