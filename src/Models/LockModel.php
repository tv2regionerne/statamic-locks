<?php

namespace Tv2regionerne\StatamicLocks\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Statamic\Facades\User;

class LockModel extends Model
{
    protected $fillable = [
        'item_id',
        'item_type',
        'site',
        'user_id',
    ];

    protected $casts = [];

    protected $table = 'statamic_locks';

    public function getConnectionName()
    {
        return config('statamic-locks.database.connection');
    }

    public function item()
    {
        if ($lock = Arr::get(config('statamic-locks.locks', []), $this->item_type)) {
            if ($handler = Arr::get($lock, 'handler')) {
                $method = Arr::get($lock, 'method', 'find');
                return $handler::$method($this->item_id);
            }
        }

        return null;
    }

    public function user()
    {
        return User::find($this->user_id);
    }
}
