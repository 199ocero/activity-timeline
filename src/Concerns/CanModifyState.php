<?php

namespace JaOcero\ActivityTimeline\Concerns;

use Closure;
use Illuminate\Support\HtmlString;

trait CanModifyState
{
    protected $state;

    public function modifyState(Closure $callback): static
    {
        $this->state = $callback;

        return $this;
    }

    public function getModifiedState(): ?HtmlString
    {
        return $this->evaluate($this->state);
    }
}
