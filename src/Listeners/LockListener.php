<?php

namespace Tv2regionerne\StatamicLocks\Listeners;

use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Statamic\Events;
use Statamic\Facades\Site;
use Statamic\Facades\User;
use Statamic\Support\Str;
use Tv2regionerne\StatamicLocks\Models\LockModel;

class LockListener
{
    public function handle($event)
    {
        $itemId = false;
        $itemType = false;

        $path = request()->path();

        // Only apply locks in cp
        if (!Str::of($path)->startsWith('cp')) {
            return;
        }


        if ($event instanceof Events\AssetSaving) {
            $itemId = $event->asset->id() ?? false;
            $itemType = 'asset';
        }

        if ($event instanceof Events\EntrySaving) {
            $itemId = $event->entry->id() ?? false;
            $itemType = 'entry';
        }

        if ($event instanceof Events\TermSaving) {
            $itemId = $event->term->id() ?? false;
            $itemType = 'term';
        }

        if (! $itemId) {
            return;
        }

        $user = User::current();

        if ($lock = LockModel::where(['item_id' => $itemId, 'item_type' => $itemType, 'site' => Site::current()->handle()])->first()) {
            if (! $lock->user() || ! $user || $lock->user()->id() != $user->id()) {
                if ($lock->updated_at > Carbon::now()->subMinutes(config('statamic-locks.clear_locks_after', 5))) {
                    throw ValidationException::withMessages([
                        'locked' => __('This item is locked'),
                    ]);
                }
            }
        }
    }
}
