<?php

namespace Tv2regionerne\StatamicLocks\Http\Controllers\CP;

use Illuminate\Http\Request;
use Statamic\CP\Column;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\CpController;
use Tv2regionerne\StatamicLocks\Models\LockModel;

class LocksController extends CpController
{
    public function index(Request $request)
    {
        $this->authorize('view locks');

        $columns = [
            Column::make('title')->label(__('Item')),
            Column::make('user')->label(__('User')),
            Column::make('updated_at')->label(__('Last Updated')),
        ];

        $locks = LockModel::all()
            ->map(function ($lock) {
                return [
                    'id' => $lock->getKey(),
                    'item_id' => $lock->item_id,
                    'item_type' => $lock->item_type,
                    'title' => $lock->item()?->title ?? $lock->item_id,
                    'show_url' => $lock->item()?->editUrl(),
                    'user' => $lock->user()->name(),
                    'updated_at' => $lock->updated_at->format('Y-m-d H:i:s'),
                    'delete_url' => cp_route('statamic-locks.locks.destroy', [$lock->getKey()])
                ];
            })
            ->values();

        if ($request->wantsJson()) {
            return [
                'meta' => [
                    'columns' => $columns,
                    'activeFilterBadges' => [],
                ],
                'data' => $locks,
            ];
        }

        return view('statamic-locks::locks.index', [
            'locks' => $locks,
            'initialColumns' => $columns,
        ]);
    }

    public function store(Request $request)
    {
        $itemId = $request->input('item_id');
        $itemType = $request->input('item_type');
        $user = User::current();

        if ($lock = LockModel::where(['item_id' => $itemId, 'item_type' => $itemType])->first()) {
            if ($lock->user()->id() != $user->id()) {
                return [
                    'error' => true,
                    'message' => 'already_locked',
                    'locked_by' => ['name' => $lock->user()->name(), 'email' => $lock->user()->email()],
                    'last_updated' => $lock->updated_at->format('Y-m-d H:i:s'),
                ];
            }

            $lock->touch();

            return [
                'error' => false,
            ];
        }

        LockModel::create([
            'item_id' => $itemId,
            'item_type' => $itemType,
            'user_id' => $user->id(),
        ]);

        return [
            'error' => false,
        ];
    }

    public function destroy($id)
    {
        $this->authorize('view locks');

        LockModel::find($id)?->delete();
    }
}
