<?php

namespace JaOcero\ActivityTimeline\Components;

use Carbon\CarbonInterface;
use Closure;
use Filament\Infolists\Components\TextEntry;
use Illuminate\Support\Carbon;
use JaOcero\ActivityTimeline\Concerns\CanModifyState;
use JaOcero\ActivityTimeline\Concerns\RendersTimelineText;
use Throwable;

class ActivityDate extends TextEntry
{
    use CanModifyState;
    use RendersTimelineText;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dateTime();
    }

    /**
     * Show "2 hours ago" with the full date and time in a tooltip.
     */
    public function relative(bool|Closure $condition = true, string|Closure|null $tooltipFormat = null): static
    {
        if (! $this->evaluate($condition)) {
            return $this;
        }

        $this->since();
        $this->dateTimeTooltip($tooltipFormat);

        return $this;
    }

    /**
     * The state as a Carbon instance in the entry's timezone, used for the
     * `<time datetime>` attribute and for grouping items by day.
     */
    public function getTimelineDate(): ?CarbonInterface
    {
        $state = $this->getState();

        if (blank($state) || is_array($state)) {
            return null;
        }

        try {
            return Carbon::parse($state)->setTimezone($this->getTimezone());
        } catch (Throwable) {
            return null;
        }
    }
}
