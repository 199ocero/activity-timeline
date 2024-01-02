<?php

namespace JaOcero\ActivityTimeline\Components;

use Closure;
use Filament\Infolists\Components\Entry;
use JaOcero\ActivityTimeline\Enums\BadgeSize;
use Filament\Infolists\Components\Concerns\HasIcon;
use Filament\Infolists\Components\Concerns\HasColor;

class ActivityBadge extends Entry
{
    use HasColor;
    use HasIcon;

    protected string $viewIdentifier = 'activityBadge';

    protected string $view = 'activity-timeline::infolists.components.activity-badge';

    protected BadgeSize | string | Closure | null $size = null;

    public function size(BadgeSize | string | Closure | null $size): static
    {
        $this->size = $size;

        return $this;
    }

    public function getSize(mixed $state): BadgeSize | string | null
    {
        return $this->evaluate($this->size, [
            'state' => $state,
        ]);
    }

    public function getViewIdentifier(): string
    {
        return $this->viewIdentifier;
    }
}
