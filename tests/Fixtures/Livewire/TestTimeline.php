<?php

namespace JaOcero\ActivityTimeline\Tests\Fixtures\Livewire;

use Closure;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use JaOcero\ActivityTimeline\Tests\Fixtures\Models\Post;
use Livewire\Component;

class TestTimeline extends Component implements HasActions, HasSchemas
{
    use InteractsWithActions;
    use InteractsWithSchemas;

    /**
     * @var Closure(): array<\Filament\Schemas\Components\Component>
     */
    public static ?Closure $components = null;

    /**
     * @var array<string, mixed>
     */
    public static array $state = [];

    public ?Post $record = null;

    public function mount(?Post $record = null): void
    {
        $this->record = $record?->exists ? $record : null;
    }

    public function infolist(Schema $schema): Schema
    {
        if ($this->record) {
            $schema->record($this->record);
        } else {
            $schema->state(static::$state);
        }

        return $schema->components((static::$components)());
    }

    public function render(): string
    {
        return <<<'BLADE'
            <div>
                {{ $this->infolist }}
            </div>
            BLADE;
    }
}
