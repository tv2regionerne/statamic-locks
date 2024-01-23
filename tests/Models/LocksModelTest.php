<?php

uses(\Tv2regionerne\StatamicLocks\Tests\TestCase::class);

use Illuminate\Support\Facades\Event;
use Tv2regionerne\StatamicLocks\Events;
use Tv2regionerne\StatamicLocks\Models\LockModel;

test('fires a deleting event', function () {
    Event::fake([Events\LockDeleted::class]);

    $model = LockModel::create([
        'item_id' => 'test',
        'item_type' => 'test',
        'user_id' => 1,
    ]);

    $model->delete();

    Event::assertDispatched(Events\LockDeleted::class);
});
