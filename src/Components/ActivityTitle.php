<?php

namespace JaOcero\ActivityTimeline\Components;

use Filament\Infolists\Components\TextEntry;
use JaOcero\ActivityTimeline\Concerns\CanModifyState;
use JaOcero\ActivityTimeline\Concerns\RendersTimelineText;

class ActivityTitle extends TextEntry
{
    use CanModifyState;
    use RendersTimelineText;
}
