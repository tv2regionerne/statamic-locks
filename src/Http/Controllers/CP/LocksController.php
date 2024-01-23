<?php

namespace Tv2regionerne\StatamicLocks\Http\Controllers\CP;

use Illuminate\Http\Request;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\CpController;
use Tv2regionerne\StatamicLocks\Models\LockModel;

class LocksController extends CpController
{
    public function index($liveblog)
    {
        // tbc
    }

    public function create(Request $request)
    {
        $itemId = $request->input('item_id');
        $itemType = $request->input('item_type');
        $user = User::current();

        if ($lock = LockModel::where(['item_id' => $itemId, 'item_type', => $itemType])->first()) {
            if ($lock->user()->id() != $user->id()) {
                return [
                    'error' => true,
                    'message' => 'already_locked',
                    'locked_by' => $lock->user(),
                    'last_updated' => $lock->updated_at,
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
        $lock = LockModel::find($post);

        $this->authorize('delete', $lock, 'You are not authorized to delete this lock.');

        $lock->delete();
    }
}
