<?php

namespace Tv2regionerne\StatamicLocks;

use Illuminate\Console\Scheduling\Schedule;
use Statamic\Events;
use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    protected $listen = [
        Events\AssetSaving::class => [LockListener::class],
        Events\EntrySaving::class => [LockListener::class],
        Events\TermSaving::class => [LockListener::class],
    ];

    protected $routes = [
        'cp' => __DIR__.'/../routes/cp.php',
    ];

    protected $vite = [
        'input' => [
            'resources/js/addon.js',
        ],
        'publicDirectory' => 'resources/dist',
    ];

    public function bootAddon()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/statamic-locks.php', 'statamic-locks');

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->callAfterResolving(Schedule::class, function (Schedule $schedule) {
            $schedule->command('statamic-locks:clear-locks')->everyMinute();
        });

        Models\LockModel::observe(Observers\LockObserver::class);
    }
}
