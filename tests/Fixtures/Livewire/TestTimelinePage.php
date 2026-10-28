<?php

namespace JaOcero\ActivityTimeline\Tests\Fixtures\Livewire;

use Closure;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Illuminate\Database\Eloquent\Model;
use JaOcero\ActivityTimeline\Concerns\HasSetting;
use JaOcero\ActivityTimeline\Tests\Fixtures\Models\Post;
use Livewire\Component;

/**
 * Mimics `ActivityTimelinePage` without needing a panel and a resource.
 */
class TestTimelinePage extends Component implements HasActions, HasSchemas
{
    use HasSetting;
    use InteractsWithActions;
    use InteractsWithSchemas;

    /**
     * @var Closure(): array<string, array<string, mixed>> | null
     */
    public static ?Closure $configuration = null;

    public static int $perPage = 20;

    public Post $record;

    public function getRecord(): Model
    {
        return $this->record;
    }

    protected function configuration(): array
    {
        return static::$configuration ? (static::$configuration)() : [];
    }

    protected function getActivitiesPerPage(): int
    {
        return static::$perPage;
    }

    public function render(): string
    {
        return <<<'BLADE'
            <div>
                {{ $this->activityInfolist }}
            </div>
            BLADE;
    }
}
