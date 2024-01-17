<?php

namespace JaOcero\ActivityTimeline\Components;

use Filament\Forms\Components\Concerns\CanAllowHtml;
use Filament\Infolists\Components\Entry;
use JaOcero\ActivityTimeline\Concerns\CanModifyState;

class ActivityDescription extends Entry
{
    use CanAllowHtml, CanModifyState;

    protected string $viewIdentifier = 'activityDescription';

    protected string $view = 'activity-timeline::infolists.components.activity-description';

    public function getViewIdentifier(): string
    {
        return $this->viewIdentifier;
    }
}
