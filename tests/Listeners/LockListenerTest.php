<?php

uses(\Tv2regionerne\StatamicLocks\Tests\TestCase::class);

use Illuminate\Support\Facades\Event;
use Statamic\Events;
use Statamic\Facades;
use Tv2regionerne\StatamicLocks\Models\LockModel;

it('prevents saving when there is a lock', function () {
    Facades\Collection::make()
        ->handle('test')
        ->save();

    $entry = tap(Facades\Entry::make()
        ->id('entry-test')
        ->collection('test'))
        ->save();

    $model = LockModel::create([
        'item_id' => 'entry-test',
        'item_type' => 'entry',
        'user_id' => 1,
        'site' => 'default',
    ]);

    $entry->save();
})->throws(Exception::class);;
