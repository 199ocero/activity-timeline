<?php

namespace JaOcero\ActivityTimeline\Components;

use Filament\Infolists\Components\Entry;

class ActivityDescription extends Entry
{
    protected string $viewIdentifier = 'activityDescription';

    protected string $view = 'activity-timeline::infolists.components.activity-description';

    public function getViewIdentifier(): string
    {
        return $this->viewIdentifier;
    }
}
