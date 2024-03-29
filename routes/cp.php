<?php

use Tv2regionerne\StatamicLocks\Http\Controllers\CP\LocksController;

Route::middleware('statamic.cp.authenticated')
    ->name('statamic-locks.')
    ->prefix('statamic-locks')
    ->group(function () {
        Route::resource('locks', LocksController::class);
    });
