<?php

namespace Tv2regionerne\StatamicLocks;

use Illuminate\Console\Scheduling\Schedule;
use Statamic\Events;
use Statamic\Facades\CP\Nav;
use Statamic\Facades\Permission;
use Statamic\Providers\AddonServiceProvider;
use Statamic\Statamic;
use Statamic\Support\Str;
use Tv2regionerne\StatamicLocks\Console\ClearLocks;

class ServiceProvider extends AddonServiceProvider
{
    protected $commands = [
        ClearLocks::class,
    ];
    protected $listen = [
        Events\AssetSaving::class => [Listeners\LockListener::class],
        Events\EntrySaving::class => [Listeners\LockListener::class],
        Events\TermSaving::class => [Listeners\LockListener::class],
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

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'statamic-locks');

        Statamic::provideToScript([
            'statamicLocks' => [
                'locks' => collect(config('statamic-locks.locks'))->map(fn ($lock) => Str::ensureLeft($lock['cp_url'], '/'))->all(),
                'pollInterval' => config('statamic-locks.locks.poll_interval', 30),
            ],
        ]);

        $this
            ->bootNav()
            ->bootObservers()
            ->bootPermissions()
            ->bootScheduledTasks();
    }

    private function bootNav()
    {
        Nav::extend(function ($nav) {
            $nav->content(__('Locks'))
                ->route('statamic-locks.locks.index')
                ->icon('lock')
                ->can('view locks');
        });

        return $this;
    }

    private function bootObservers()
    {
        Models\LockModel::observe(Observers\LockObserver::class);

        return $this;
    }

    private function bootPermissions()
    {
        Permission::group('locks', __('Locks'), function () {
            Permission::register('view locks', function ($permission) {
                $permission
                    ->label(__('View Locks'));
            });

            Permission::register('delete user locks', function ($permission) {
                $permission
                    ->label(__('Delete Other User\'s Locks'));
            });
        });

        return $this;
    }

    private function bootScheduledTasks()
    {
        $this->callAfterResolving(Schedule::class, function (Schedule $schedule) {
            $schedule->command('statamic-locks:clear-locks')->everyMinute();
        });

        return $this;
    }
}
