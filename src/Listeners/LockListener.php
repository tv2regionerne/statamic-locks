<?php

namespace Tv2regionerne\StatamicLocks\Listeners;

use Statamic\Events;
use Statamic\Facades\User;
use Tv2regionerne\StatamicLocks\Models\LockModel;

class LockListener
{
    public function handle($event)
    {
        $itemId = false;
        $itemType = false;

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

        if ($lock = LockModel::where(['item_id' => $itemId, 'item_type', => $itemType])->first()) {
            if ($lock->user()->id() != $user?->id()) {
                return false;
            }
        }
    }
}
