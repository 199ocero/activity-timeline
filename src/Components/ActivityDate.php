<?php

namespace JaOcero\ActivityTimeline\Components;

use Illuminate\Support\Carbon;
use Filament\Infolists\Components\Entry;

class ActivityDate extends Entry
{
    protected string $viewIdentifier = 'activityDate';

    protected string $view = 'activity-timeline::infolists.components.activity-date';

    protected ?string $date = null;
    protected string | null $dateFormat = null;
    protected string | null $dateTimezone = null;

    public function date(string | null $format = null, string | null $timezone = null): static
    {

        $this->dateFormat = $format;
        $this->dateTimezone = $timezone;

        return $this;
    }

    public function getDate($value): ?string
    {
        $date = Carbon::parse($value)
            ->setTimezone($this->getTimezone());

        if ($this->getFormat() != null) {
            $this->date = $date->translatedFormat($this->getFormat());
        } else {
            $this->date = $date;
        }

        return $this->evaluate($this->date);
    }

    public function getFormat(): ?string
    {
        return $this->evaluate($this->dateFormat);
    }

    public function getTimezone(): ?string
    {
        return $this->evaluate($this->dateTimezone) ?? config('app.timezone');
    }

    public function getViewIdentifier(): string
    {
        return $this->viewIdentifier;
    }
}
