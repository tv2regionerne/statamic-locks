<?php

namespace Tv2regionerne\StatamicLocks\Http\Controllers\CP;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Statamic\CP\Column;
use Statamic\Facades\Site;
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

        $locks = LockModel::where('site', Site::current()->handle())
            ->get()
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

        $lock = LockModel::where('item_id', $itemId)
            ->where('item_type', $itemType)
            ->where('site', Site::current()->handle())
            ->where('updated_at', '>', Carbon::now()->subSeconds(config('statamic-locks.clear_locks_after', 5) * 60))
            ->first();

        if ($lock) {
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
            'site' => Site::current()->handle(),
        ]);

        return [
            'error' => false,
        ];
    }

    public function destroy($id)
    {
        $this->authorize('view locks');

        if (! $lock) {
            return;
        }

        if (! $lock->user()->id() == User::current()->id()) {
            $this->authorize('delete user locks');
        }

        if ($request->input('delay')) {
            $lock->updated_at = Carbon::now()->subSeconds((config('statamic-locks.clear_locks_after', 5) * 60) - 5);
            $lock->save();

            return;
        }

        $lock->delete();
    }
}
