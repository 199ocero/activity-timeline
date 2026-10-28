<?php

namespace JaOcero\ActivityTimeline\Components;

use BackedEnum;
use Filament\Infolists\Components\TextEntry;
use Illuminate\Support\Facades\Lang;
use JaOcero\ActivityTimeline\Concerns\RendersTimelineText;

/**
 * A small pill shown next to the title, e.g. the activity event or log name.
 */
class ActivityBadge extends TextEntry
{
    use RendersTimelineText;

    protected function setUp(): void
    {
        parent::setUp();

        $this->badge();

        $this->color(static fn (mixed $state): string => match (static::normalizeEvent($state)) {
            'created' => 'success',
            'updated' => 'info',
            'deleted' => 'danger',
            'restored' => 'warning',
            default => 'gray',
        });

        $this->formatStateUsing(static function (mixed $state): ?string {
            $event = static::normalizeEvent($state);

            if (blank($event)) {
                return null;
            }

            $translationKey = "activity-timeline::timeline.events.{$event}";

            return Lang::has($translationKey) ? __($translationKey) : str($event)->headline()->toString();
        });
    }

    public static function normalizeEvent(mixed $state): ?string
    {
        if ($state instanceof BackedEnum) {
            return (string) $state->value;
        }

        return is_scalar($state) ? (string) $state : null;
    }
}
