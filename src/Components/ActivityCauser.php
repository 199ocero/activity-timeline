<?php

namespace JaOcero\ActivityTimeline\Components;

use Closure;
use Filament\Facades\Filament;
use Filament\Infolists\Components\Entry;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Throwable;

/**
 * Shows who performed the activity as an avatar next to the title.
 *
 * The state can be a model (e.g. the Spatie `causer`), a name string, or an
 * array with `name` and `avatar` keys.
 */
class ActivityCauser extends Entry
{
    protected string|Closure|null $avatarUrl = null;

    protected string|Closure|null $causerName = null;

    protected bool|Closure $isNameVisible = false;

    public function avatarUrl(string|Closure|null $url): static
    {
        $this->avatarUrl = $url;

        return $this;
    }

    public function causerName(string|Closure|null $name): static
    {
        $this->causerName = $name;

        return $this;
    }

    public function showName(bool|Closure $condition = true): static
    {
        $this->isNameVisible = $condition;

        return $this;
    }

    public function isNameVisible(): bool
    {
        return (bool) $this->evaluate($this->isNameVisible);
    }

    public function getCauserName(): ?string
    {
        $name = $this->evaluate($this->causerName, ['causer' => $causer = $this->getState()]);

        if (filled($name)) {
            return $name;
        }

        if (is_string($causer)) {
            return $causer;
        }

        if (is_array($causer)) {
            return $causer['name'] ?? null;
        }

        if (! $causer instanceof Model) {
            return null;
        }

        if ($causer instanceof Authenticatable && $this->hasCurrentPanel()) {
            try {
                return Filament::getUserName($causer);
            } catch (Throwable) {
                // Fall through to the common attribute names.
            }
        }

        return static::guessModelName($causer);
    }

    public function getAvatarUrl(): ?string
    {
        $url = $this->evaluate($this->avatarUrl, ['causer' => $causer = $this->getState()]);

        if (filled($url)) {
            return $url;
        }

        if (is_array($causer)) {
            return $causer['avatar'] ?? null;
        }

        if ($causer instanceof Authenticatable && $causer instanceof Model && $this->hasCurrentPanel()) {
            try {
                return Filament::getUserAvatarUrl($causer);
            } catch (Throwable) {
                return null;
            }
        }

        return null;
    }

    public function getInitials(): ?string
    {
        $name = $this->getCauserName();

        if (blank($name)) {
            return null;
        }

        return str($name)
            ->squish()
            ->explode(' ')
            ->take(2)
            ->map(fn (string $part): string => mb_strtoupper(mb_substr($part, 0, 1)))
            ->implode('');
    }

    public static function guessModelName(Model $model): ?string
    {
        foreach (['name', 'full_name', 'username', 'email'] as $attribute) {
            if (filled($value = $model->getAttribute($attribute))) {
                return (string) $value;
            }
        }

        $fullName = trim("{$model->getAttribute('first_name')} {$model->getAttribute('last_name')}");

        return filled($fullName) ? $fullName : null;
    }

    protected function hasCurrentPanel(): bool
    {
        try {
            return Filament::getCurrentOrDefaultPanel() !== null;
        } catch (Throwable) {
            return false;
        }
    }
}
