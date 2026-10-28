<?php

namespace JaOcero\ActivityTimeline\Concerns;

use BackedEnum;
use Closure;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

trait HasEmptyState
{
    protected string|Htmlable|Closure|null $emptyStateDescription = null;

    protected string|Htmlable|Closure|null $emptyStateHeading = null;

    protected string|BackedEnum|Htmlable|Closure|null $emptyStateIcon = null;

    public function emptyStateDescription(string|Htmlable|Closure|null $description): static
    {
        $this->emptyStateDescription = $description;

        return $this;
    }

    public function emptyStateHeading(string|Htmlable|Closure|null $heading): static
    {
        $this->emptyStateHeading = $heading;

        return $this;
    }

    public function emptyStateIcon(string|BackedEnum|Htmlable|Closure|null $icon): static
    {
        $this->emptyStateIcon = $icon;

        return $this;
    }

    public function getEmptyStateDescription(): string|Htmlable|null
    {
        return $this->evaluate($this->emptyStateDescription) ?? __('activity-timeline::timeline.empty_state.description');
    }

    public function getEmptyStateHeading(): string|Htmlable
    {
        return $this->evaluate($this->emptyStateHeading) ?? __('activity-timeline::timeline.empty_state.heading');
    }

    public function getEmptyStateIcon(): string|BackedEnum|Htmlable
    {
        return $this->evaluate($this->emptyStateIcon) ?? Heroicon::OutlinedBoltSlash;
    }
}
