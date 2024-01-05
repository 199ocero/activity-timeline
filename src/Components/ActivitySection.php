<?php

namespace JaOcero\ActivityTimeline\Components;

use Closure;
use Filament\Infolists\ComponentContainer;
use Filament\Infolists\Components\Entry;
use Illuminate\Database\Eloquent\Model;
use JaOcero\ActivityTimeline\Enums\Direction;

class ActivitySection extends Entry
{
    protected string $view = 'activity-timeline::infolists.components.activity-section';

    protected string|Closure|null $description = null;

    protected int|Closure|null $showItemsCount = null;

    protected string|Closure|null $showItemsLabel = null;

    protected string|Closure|null $showItemsIcon = null;

    protected string|Closure|null $showItemsColor = null;

    // protected Direction|string $direction = Direction::Vertical;

    // protected array|int|null $horizontalItems = null;

    protected bool|Closure|null $isAside = null;

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

    public function isAside(): bool
    {
        return (bool) ($this->evaluate($this->isAside) ?? false);
    }

    public function getDescription(): string
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

    // @todo: for now we're not using this
    // public function direction(Direction|string $direction = Direction::Vertical): static
    // {
    //     if (in_array($direction, [Direction::Horizontal, Direction::Vertical])) {
    //         $this->direction = $direction;
    //     } else {
    //         $this->direction = Direction::Vertical;
    //     }

    //     return $this;
    // }

    // @todo: for now we're not using this
    // public function horizontalItems(array|int|null $items = 3): static
    // {
    //     if (! is_array($items)) {
    //         if ($items === 0 || $items === 1) {
    //             throw new \InvalidArgumentException('Invalid value provided for horizontal items. Please use a value other than 0 or 1.');
    //         }
    //         $this->horizontalItems = ['lg' => $items];
    //     } else {
    //         $this->horizontalItems = $items;
    //     }

    //     return $this;
    // }

    // @todo: for now we're not using this
    // public function getDirection(): Direction|string
    // {
    //     return $this->evaluate($this->direction);
    // }

    // @todo: for now we're not using this
    // public function getHorizontalItems(?string $breakpoint = null): ?int
    // {
    //     return $this->evaluate($this->horizontalItems[$breakpoint] ?? null);
    // }
}
