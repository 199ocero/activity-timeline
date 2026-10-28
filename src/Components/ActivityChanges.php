<?php

namespace JaOcero\ActivityTimeline\Components;

use BackedEnum;
use Closure;
use DateTimeInterface;
use Filament\Infolists\Components\Entry;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Str;
use JsonSerializable;
use UnitEnum;

/**
 * Renders a before → after list of changed attributes.
 *
 * The state should be an array shaped like Spatie's activity changes:
 * `['old' => [...], 'attributes' => [...]]`.
 */
class ActivityChanges extends Entry
{
    /**
     * @var array<string, string> | Closure
     */
    protected array|Closure $attributeLabels = [];

    /**
     * @var array<string> | Closure | null
     */
    protected array|Closure|null $onlyAttributes = null;

    /**
     * @var array<string> | Closure
     */
    protected array|Closure $exceptAttributes = ['updated_at', 'created_at', 'deleted_at'];

    protected ?Closure $formatValueUsing = null;

    protected int|Closure|null $valueLimit = 120;

    /**
     * @param  array<string, string> | Closure  $labels
     */
    public function attributeLabels(array|Closure $labels): static
    {
        $this->attributeLabels = $labels;

        return $this;
    }

    /**
     * @param  array<string> | Closure | null  $attributes
     */
    public function only(array|Closure|null $attributes): static
    {
        $this->onlyAttributes = $attributes;

        return $this;
    }

    /**
     * @param  array<string> | Closure  $attributes
     */
    public function except(array|Closure $attributes): static
    {
        $this->exceptAttributes = $attributes;

        return $this;
    }

    /**
     * The callback receives `$value` and `$attribute` and should return a string.
     */
    public function formatValueUsing(?Closure $callback): static
    {
        $this->formatValueUsing = $callback;

        return $this;
    }

    public function valueLimit(int|Closure|null $limit): static
    {
        $this->valueLimit = $limit;

        return $this;
    }

    /**
     * @return array<int, array{attribute: string, label: string, old: ?string, new: ?string, hasOld: bool, hasNew: bool}>
     */
    public function getChangeRows(): array
    {
        $state = $this->getState();

        if ($state instanceof Arrayable) {
            $state = $state->toArray();
        }

        if (! is_array($state)) {
            return [];
        }

        $old = static::toArray($state['old'] ?? []);
        $new = static::toArray($state['attributes'] ?? []);

        $attributes = array_unique([...array_keys($new), ...array_keys($old)]);

        if (filled($only = $this->evaluate($this->onlyAttributes))) {
            $attributes = array_intersect($attributes, $only);
        }

        $attributes = array_diff($attributes, $this->evaluate($this->exceptAttributes) ?? []);

        $labels = $this->evaluate($this->attributeLabels) ?? [];

        $rows = [];

        foreach ($attributes as $attribute) {
            $hasOld = array_key_exists($attribute, $old);
            $hasNew = array_key_exists($attribute, $new);

            $oldValue = $hasOld ? $this->formatChangeValue($old[$attribute], $attribute) : null;
            $newValue = $hasNew ? $this->formatChangeValue($new[$attribute], $attribute) : null;

            if ($hasOld && $hasNew && ($oldValue === $newValue)) {
                continue;
            }

            $rows[] = [
                'attribute' => $attribute,
                'label' => $labels[$attribute] ?? str($attribute)->replace(['_', '.'], ' ')->ucfirst()->toString(),
                'old' => $oldValue,
                'new' => $newValue,
                'hasOld' => $hasOld,
                'hasNew' => $hasNew,
            ];
        }

        return $rows;
    }

    protected function formatChangeValue(mixed $value, string $attribute): ?string
    {
        if ($this->formatValueUsing) {
            $value = $this->evaluate($this->formatValueUsing, [
                'value' => $value,
                'attribute' => $attribute,
            ]);
        }

        $value = match (true) {
            $value === null, $value === '' => null,
            is_bool($value) => $value ? 'true' : 'false',
            $value instanceof BackedEnum => (string) $value->value,
            $value instanceof UnitEnum => $value->name,
            $value instanceof DateTimeInterface => $value->format('Y-m-d H:i:s'),
            is_array($value), $value instanceof JsonSerializable => json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            default => (string) $value,
        };

        if (($value !== null) && ($limit = $this->evaluate($this->valueLimit))) {
            $value = Str::limit($value, $limit);
        }

        return $value;
    }

    /**
     * @return array<string, mixed>
     */
    protected static function toArray(mixed $value): array
    {
        if ($value instanceof Arrayable) {
            return $value->toArray();
        }

        return is_array($value) ? $value : [];
    }
}
