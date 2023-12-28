<?php

namespace JaOcero\ActivityTimeline\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \JaOcero\ActivityTimeline\ActivityTimeline
 */
class ActivityTimeline extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \JaOcero\ActivityTimeline\ActivityTimeline::class;
    }
}
