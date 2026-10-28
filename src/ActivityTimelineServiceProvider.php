<?php

namespace JaOcero\ActivityTimeline;

use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class ActivityTimelineServiceProvider extends PackageServiceProvider
{
    public static string $name = 'activity-timeline';

    public static string $viewNamespace = 'activity-timeline';

    public function configurePackage(Package $package): void
    {
        $package->name(static::$name)
            ->hasViews(static::$viewNamespace)
            ->hasTranslations()
            ->hasInstallCommand(function (InstallCommand $command): void {
                $command->askToStarRepoOnGitHub('jaocero/activity-timeline');
            });
    }
}
