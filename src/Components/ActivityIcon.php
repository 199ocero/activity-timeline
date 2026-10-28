<?php

namespace JaOcero\ActivityTimeline\Components;

use Closure;
use Filament\Infolists\Components\IconEntry;
use JaOcero\ActivityTimeline\Enums\IconAnimation;

class ActivityIcon extends IconEntry
{
    protected IconAnimation|string|Closure|null $animation = null;

    public function animation(IconAnimation|string|Closure|null $animation): static
    {
        $this->animation = $animation;

        return $this;
    }

    public function getAnimation(mixed $state): IconAnimation|string|null
    {
        $animation = $this->evaluate($this->animation, [
            'state' => $state,
        ]);

        if (is_string($animation)) {
            return IconAnimation::tryFrom($animation) ?? $animation;
        }

        return $animation;
    }
}
