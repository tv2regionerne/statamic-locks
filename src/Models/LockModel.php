<?php

namespace Tv2regionerne\StatamicLocks\Models;

use Illuminate\Database\Eloquent\Model;
use Statamic\Facades\User;

class LockModel extends Model
{
    protected $fillable = [
        'item_id',
        'item_type',
        'user_id',
    ];

    protected $casts = [];

    protected $table = 'statamic_locks';

    public function getConnectionName()
    {
        return config('statamic-locks.database.connection');
    }

    public function user()
    {
        return User::find($this->user_id);
    }
}
