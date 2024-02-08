<?php

namespace Tv2regionerne\StatamicLocks\Http\Controllers\CP;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Statamic\CP\Column;
use Statamic\Facades\Site;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\CpController;
use Tv2regionerne\StatamicLocks\Jobs\DeleteLockJob;
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

        $user = User::current();

        $locks = LockModel::where('site', Site::current()->handle())
            ->get()
            ->map(function ($lock) use ($user) {
                return [
                    'id' => $lock->getKey(),
                    'item_id' => $lock->item_id,
                    'item_type' => $lock->item_type,
                    'title' => $lock->item()?->title ?? $lock->item_id,
                    'show_url' => $lock->item()?->editUrl(),
                    'user' => $lock->user()?->name() ?? __('Unknown user'),
                    'updated_at' => $lock->updated_at->format('Y-m-d H:i:s'),
                    'can_delete' => $user->can('delete user locks') || $lock->user()?->id() == $user->id(),
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
            ->first();

        if ($lock) {
            if ($lock->user()?->id() != $user->id()) {
                if ($lock->updated_at->lt(Carbon::now()->addSeconds(config('statamic-locks.clear_locks_after', 5) * 60))) {
                    // expired
                    $lock->delete();
                } else {
                    return [
                        'error' => true,
                        'lock_id' => $lock->getKey(),
                        'message' => 'already_locked',
                        'locked_by' => ['name' => $lock->user()?->name() ?? __('Unknown user'), 'email' => $lock->user()?->email() ?? ''],
                        'last_updated' => $lock->updated_at->format('Y-m-d H:i:s'),
                    ];
                }
            } else {
                $lock->touch();
            }
        }

        if (! $lock) {
            $lock = LockModel::create([
                'item_id' => $itemId,
                'item_type' => $itemType,
                'user_id' => $user->id(),
                'site' => Site::current()->handle(),
            ]);
        }

        return [
            'error' => false,
            'status' => [
                'lock_id' => $lock->getKey(),
            ],
        ];
    }

    public function destroy(Request $request, $id)
    {
        $lock = LockModel::find($id);

        if (! $lock) {
            return;
        }

        if (! $lock->user()?->id() == User::current()->id()) {
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
