<?php

namespace iEducar\Community\Reports\Providers;

use iEducar\Community\Reports\Commands\CommunityReportsLinkCommand;
use iEducar\Reports\Contracts\TeacherReportCard;
use Illuminate\Support\ServiceProvider;
use TeacherReportCardReport;

class ReportsServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

            $this->commands([
                CommunityReportsLinkCommand::class,
            ]);
        }
    }

    public function register()
    {
        $this->app->bind(TeacherReportCard::class, TeacherReportCardReport::class);
    }
}