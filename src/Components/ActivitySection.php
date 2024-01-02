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

    protected Direction|string $direction = Direction::Vertical;

    protected bool|Closure|null $isAside = null;

    protected array|int|null $horizontalItems = null;

    public function description(string|Closure|null $description = null): static
    {
        $this->description = $description;

        return $this;
    }

    public function direction(Direction|string $direction = Direction::Vertical): static
    {
        if (in_array($direction, [Direction::Horizontal, Direction::Vertical])) {
            $this->direction = $direction;
        } else {
            $this->direction = Direction::Vertical;
        }

        return $this;
    }

    public function aside(bool|Closure|null $condition = true): static
    {
        $this->isAside = $condition;

        return $this;
    }

    public function horizontalItems(array|int|null $items = 3): static
    {
        if (! is_array($items)) {
            if ($items === 0 || $items === 1) {
                throw new \InvalidArgumentException('Invalid value provided for horizontal items. Please use a value other than 0 or 1.');
            }
            $this->horizontalItems = ['lg' => $items];
        } else {
            $this->horizontalItems = $items;
        }

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

    public function getDirection(): Direction|string
    {
        return $this->evaluate($this->direction);
    }

    public function getHorizontalItems(?string $breakpoint = null): ?int
    {
        return $this->evaluate($this->horizontalItems[$breakpoint] ?? null);
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
