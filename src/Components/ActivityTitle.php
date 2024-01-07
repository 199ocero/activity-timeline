<?php

namespace JaOcero\ActivityTimeline\Components;

use Filament\Forms\Components\Concerns\CanAllowHtml;
use Filament\Infolists\Components\Entry;

class ActivityTitle extends Entry
{
    use CanAllowHtml;

    protected string $viewIdentifier = 'activityTitle';

    protected string $view = 'activity-timeline::infolists.components.activity-title';

    public function getViewIdentifier(): string
    {
        return $this->viewIdentifier;
    }
}
