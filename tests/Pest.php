<?php

use JaOcero\ActivityTimeline\Tests\TestCase;

/*
 * Let the fixtures use the v5 namespaces when the tests run against Spatie Activitylog v4.
 */
if (! trait_exists('Spatie\Activitylog\Models\Concerns\LogsActivity') && trait_exists('Spatie\Activitylog\Traits\LogsActivity')) {
    class_alias('Spatie\Activitylog\Traits\LogsActivity', 'Spatie\Activitylog\Models\Concerns\LogsActivity');
    class_alias('Spatie\Activitylog\LogOptions', 'Spatie\Activitylog\Support\LogOptions');
}

uses(TestCase::class)->in(__DIR__);
