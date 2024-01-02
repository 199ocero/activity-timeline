<?php

namespace JaOcero\ActivityTimeline\Components;

use Filament\Infolists\Components\Entry;

class ActivityTitle extends Entry
{
    protected string $viewIdentifier = 'activityTitle';

    protected string $view = 'activity-timeline::infolists.components.activity-title';

    public function getViewIdentifier(): string
    {
        return $this->viewIdentifier;
    }
}
