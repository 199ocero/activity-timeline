<?php

namespace JaOcero\ActivityTimeline\Components;

use Closure;
use Filament\Infolists\ComponentContainer;
use Filament\Infolists\Components\Entry;
use Illuminate\Database\Eloquent\Model;
use JaOcero\ActivityTimeline\Concerns\HasEmptyState;

class ActivitySection extends Entry
{
    use HasEmptyState;

    protected string $view = 'activity-timeline::infolists.components.activity-section';

    protected string|Closure|null $description = null;

    protected int|Closure|null $showItemsCount = null;

    protected string|Closure|null $showItemsLabel = null;

    protected string|Closure|null $showItemsIcon = null;

    protected string|Closure|null $showItemsColor = null;

    protected bool|Closure|null $isAside = null;

    protected bool|Closure|null $isHeadingVisible = null;

    public function description(string|Closure|null $description = null): static
    {
        $this->description = $description;

        return $this;
    }

    public function aside(bool|Closure|null $condition = true): static
    {
        $this->isAside = $condition;

        return $this;
    }

    public function showItemsCount(int|Closure $items): static
    {
        $this->showItemsCount = $items;

        return $this;
    }

    public function showItemsLabel(string|Closure $label): static
    {
        $this->showItemsLabel = $label;

        return $this;
    }

    public function showItemsIcon(string|Closure|null $icon = null): static
    {
        $this->showItemsIcon = $icon;

        return $this;
    }

    public function showItemsColor(string|Closure $color): static
    {
        $this->showItemsColor = $color;

        return $this;
    }

    public function headingVisible(bool|Closure|null $condition = true): static
    {
        $this->isHeadingVisible = $condition;

        return $this;
    }

    public function isAside(): bool
    {
        return (bool) ($this->evaluate($this->isAside) ?? false);
    }

    public function getDescription(): ?string
    {
        return $this->evaluate($this->description);
    }

    public function getShowItemsCount(): ?int
    {
        $showItemsCount = $this->evaluate($this->showItemsCount);

        if ($showItemsCount == 0) {
            return null;
        }

        return $showItemsCount;
    }

    public function getShowItemsLabel(): string
    {
        return $this->evaluate($this->showItemsLabel) ?? 'Show More';
    }

    public function getShowItemsIcon(): ?string
    {
        return $this->evaluate($this->showItemsIcon);
    }

    public function getShowItemsColor(): string
    {
        return $this->evaluate($this->showItemsColor) ?? 'gray';
    }

    public function isHeadingVisible(): bool
    {
        return (bool) ($this->evaluate($this->isHeadingVisible) ?? true);
    }

    /**
     * @return array<ComponentContainer>
     */
    public function getChildComponentContainers(bool $withHidden = false): array
    {
        if ((! $withHidden) && $this->isHidden()) {
            return [];
        }

        $containers = [];

        foreach ($this->getState() ?? [] as $itemKey => $itemData) {
            $container = $this
                ->getChildComponentContainer()
                ->getClone()
                ->statePath($itemKey)
                ->inlineLabel(false);

            if ($itemData instanceof Model) {
                $container->record($itemData);
            }

            $containers[$itemKey] = $container;
        }

        return $containers;
    }
}
