<?php

namespace Tv2regionerne\StatamicLocks\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;
use Statamic\Events\Event;

class LockDeleted extends Event implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public $lock;

    public function __construct($lock)
    {
        $this->lock = $lock;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('statamic-locks.'.$this->lock->id);
    }

    public function broadcastAs()
    {
        return 'StatamicLocks.LockDeleted';
    }
}
