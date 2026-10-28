<?php

namespace JaOcero\ActivityTimeline\Concerns;

use Illuminate\Contracts\Support\Htmlable;

trait RendersTimelineText
{
    /**
     * Format the state with Filament's `TextEntry` pipeline and return safe HTML,
     * falling back to the placeholder when there is nothing to show.
     */
    public function getTimelineHtml(): ?string
    {
        $state = $this->formatState($this->getState());

        if ($state instanceof Htmlable) {
            $state = $state->toHtml();

            return filled($state) ? $state : $this->getTimelinePlaceholderHtml();
        }

        if (blank($state)) {
            return $this->getTimelinePlaceholderHtml();
        }

        return e($state);
    }

    protected function getTimelinePlaceholderHtml(): ?string
    {
        $placeholder = $this->getPlaceholder();

        if (blank($placeholder)) {
            return null;
        }

        return $placeholder instanceof Htmlable ? $placeholder->toHtml() : e($placeholder);
    }
}
