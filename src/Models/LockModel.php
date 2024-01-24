<?php

namespace Tv2regionerne\StatamicLocks\Models;

use Illuminate\Database\Eloquent\Model;
use Statamic\Facades;

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

    public function item()
    {
        if ($this->item_type == 'entry') {
            return Facades\Entry::find($this->item_id);
        }

        if ($this->item_type == 'term') {
            return Facades\Term::find($this->item_id);
        }

        if ($this->item_type == 'asset') {
            return Facades\Asset::find($this->item_id);
        }

        return null;
    }

    public function user()
    {
        return Facades\User::find($this->user_id);
    }
}
