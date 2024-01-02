<?php

namespace JaOcero\ActivityTimeline\Components;

use Filament\Infolists\Components\IconEntry;

class ActivityIcon extends IconEntry
{
    protected string $viewIdentifier = 'activityIcon';

    protected string $view = 'activity-timeline::infolists.components.activity-icon';

    public function getViewIdentifier(): string
    {
        return $this->viewIdentifier;
    }
}
