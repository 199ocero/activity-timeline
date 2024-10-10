<?php

namespace JaOcero\ActivityTimeline\Components;

use Closure;
use Filament\Infolists\Components\IconEntry;
use JaOcero\ActivityTimeline\Enums\IconAnimation;

class ActivityIcon extends IconEntry
{
    protected string $viewIdentifier = 'activityIcon';

    protected IconAnimation|string|Closure|null $animation = null;

    protected string $view = 'activity-timeline::infolists.components.activity-icon';

    public function animation(IconAnimation|string|Closure|null $animation): static
    {
        $this->animation = $animation;

        return $this;
    }

    public function getAnimation(mixed $state): IconAnimation|string|null
    {
        return $this->evaluate($this->animation, [
            'state' => $state,
        ]);
    }

    public function getViewIdentifier(): string
    {
        return $this->viewIdentifier;
    }
}
