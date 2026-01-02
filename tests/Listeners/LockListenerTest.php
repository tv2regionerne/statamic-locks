<?php

uses(\Tv2regionerne\StatamicLocks\Tests\TestCase::class);

use Statamic\Facades;
use Tv2regionerne\StatamicLocks\Models\LockModel;

it('allows saving when there is no lock', function () {
    Facades\Collection::make()
        ->handle('test')
        ->save();

    $entry = tap(Facades\Entry::make()
        ->id('entry-test-2')
        ->collection('test'))
        ->save();

    $this->mock(\Tv2regionerne\StatamicLocks\Listeners\LockListener::class, function ($mock) {
        $mock->makePartial()
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive('isCpRequest')
            ->andReturn(true);
    });

    expect($entry->save())->toBeTrue();
});

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

    // Mock the LockListener->isCpRequest to always return true
    $this->mock(\Tv2regionerne\StatamicLocks\Listeners\LockListener::class, function ($mock) {
        $mock->makePartial()
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive('isCpRequest')
            ->andReturn(true);
    });

    $entry->save();
})->throws(Exception::class);

//it('prevents saving when entry has been updated since lock was created', function () {
//
//})->throws(Exception::class);
