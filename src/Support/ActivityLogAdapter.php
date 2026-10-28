<?php

namespace JaOcero\ActivityTimeline\Support;

use BackedEnum;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use LogicException;

/**
 * Normalizes Spatie Activitylog records into plain timeline items.
 *
 * Supports both v4 (changes stored in `properties.old` / `properties.attributes`)
 * and v5 (changes stored in the `attribute_changes` column).
 */
class ActivityLogAdapter
{
    /**
     * @return class-string<Model>
     */
    public static function getActivityModel(): string
    {
        $model = config('activitylog.activity_model') ?? 'Spatie\\Activitylog\\Models\\Activity';

        if (! class_exists($model)) {
            throw new LogicException('The activity timeline needs [spatie/laravel-activitylog]. Install it with `composer require spatie/laravel-activitylog`.');
        }

        return $model;
    }

    /**
     * @return Builder<Model>
     */
    public static function query(Model $subject): Builder
    {
        $model = static::getActivityModel();

        /** @var Model $activity */
        $activity = new $model;

        return $model::query()
            ->whereMorphedTo('subject', $subject)
            ->with('causer')
            ->orderByDesc($activity->qualifyColumn($activity->getCreatedAtColumn() ?? 'created_at'))
            ->orderByDesc($activity->getQualifiedKeyName());
    }

    /**
     * @return array<string, mixed>
     */
    public static function toTimelineItem(Model $activity): array
    {
        $changes = static::getChanges($activity);
        $properties = static::getProperties($activity);
        $event = static::getEvent($activity);

        return [
            'id' => $activity->getKey(),
            'log_name' => $activity->getAttribute('log_name'),
            'description' => $activity->getAttribute('description'),
            'event' => $event,
            'subject' => $activity->getRelationValue('subject'),
            'causer' => $activity->getRelationValue('causer'),
            'properties' => $properties,
            'changes' => $changes,
            'created_at' => $activity->getAttribute('created_at'),
            'activity' => $activity,

            // The shape 1.x passed to `modify_state` callbacks, kept for backwards compatibility.
            'activityData' => [
                'log_name' => $activity->getAttribute('log_name'),
                'description' => $activity->getAttribute('description'),
                'subject' => $activity->getRelationValue('subject'),
                'event' => $event,
                'causer' => $activity->getRelationValue('causer'),
                'properties' => [...$properties, ...array_filter($changes)],
                'batch_uuid' => $activity->getAttribute('batch_uuid'),
            ],
        ];
    }

    /**
     * @return array{old: array<string, mixed>, attributes: array<string, mixed>}
     */
    public static function getChanges(Model $activity): array
    {
        $changes = static::toArray($activity->getAttribute('attribute_changes'));

        if (blank($changes)) {
            $changes = static::toArray($activity->getAttribute('properties'));
        }

        return [
            'old' => static::toArray($changes['old'] ?? []),
            'attributes' => static::toArray($changes['attributes'] ?? []),
        ];
    }

    /**
     * Custom properties added with `withProperties()`, without the tracked changes.
     *
     * @return array<string, mixed>
     */
    public static function getProperties(Model $activity): array
    {
        $properties = static::toArray($activity->getAttribute('properties'));

        unset($properties['old'], $properties['attributes']);

        return $properties;
    }

    public static function getEvent(Model $activity): ?string
    {
        $event = $activity->getAttribute('event');

        if ($event instanceof BackedEnum) {
            return (string) $event->value;
        }

        return filled($event) ? (string) $event : null;
    }

    /**
     * @return array<string, mixed>
     */
    protected static function toArray(mixed $value): array
    {
        if ($value instanceof Arrayable) {
            return $value->toArray();
        }

        if (is_string($value)) {
            $value = json_decode($value, associative: true);
        }

        return is_array($value) ? $value : [];
    }
}
