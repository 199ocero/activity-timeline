<?php

namespace JaOcero\ActivityTimeline\Concerns;

use Closure;
use Illuminate\Contracts\Support\Htmlable;

/**
 * Keeps the 1.x API working on top of Filament's `TextEntry` formatting.
 */
trait CanModifyState
{
    /**
     * @deprecated Use `formatStateUsing()` instead.
     */
    public function modifyState(?Closure $callback): static
    {
        return $this->formatStateUsing($callback);
    }

    /**
     * @deprecated Use `html()` instead. HTML is now sanitized before it is rendered.
     */
    public function allowHtml(bool|Closure $condition = true): static
    {
        return $this->html($condition);
    }

    /**
     * @deprecated Use `isHtml()` instead.
     */
    public function isHtmlAllowed(): bool
    {
        return $this->isHtml();
    }

    /**
     * @deprecated Use `formatState($this->getState())` instead.
     */
    public function getModifiedState(): string|Htmlable|null
    {
        return $this->formatState($this->getState());
    }
}
