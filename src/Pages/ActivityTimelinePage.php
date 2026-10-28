<?php

namespace JaOcero\ActivityTimeline\Pages;

use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use JaOcero\ActivityTimeline\Concerns\HasSetting;

/**
 * A resource page that shows the Spatie Activitylog timeline of a record.
 *
 * Register it in `getPages()` with a route containing `{record}`:
 *
 * ```php
 * 'activities' => Pages\ViewProjectActivities::route('/{record}/activities'),
 * ```
 */
class ActivityTimelinePage extends Page
{
    use HasSetting;
    use InteractsWithRecord;

    protected string $view = 'filament-panels::pages.page';

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }

    public function content(Schema $schema): Schema
    {
        return $this->activityInfolist($schema);
    }

    public function getTitle(): string|Htmlable
    {
        return __('activity-timeline::timeline.page.title', [
            'record' => strip_tags((string) $this->getRecordTitle()),
        ]);
    }

    public function getBreadcrumb(): ?string
    {
        return __('activity-timeline::timeline.page.breadcrumb');
    }
}
