<?php

namespace Tv2regionerne\StatamicLocks\Observers;

use Tv2regionerne\StatamicLocks\Events\LockDeleted;
use Tv2regionerne\StatamicLocks\Models\LockModel;

class LockObserver
{
    public function deleted(LockModel $lock)
    {
        LockDeleted::dispatch($lock);
    }
}
