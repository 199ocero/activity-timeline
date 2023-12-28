<?php

namespace JaOcero\ActivityTimeline\Commands;

use Illuminate\Console\Command;

class ActivityTimelineCommand extends Command
{
    public $signature = 'activity-timeline';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
