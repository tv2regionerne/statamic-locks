<?php

namespace Tv2regionerne\StatamicLocks\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Tv2regionerne\StatamicLocks\Models\LockModel;

class DeleteLockJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private LockModel $lock;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(LockModel $lock)
    {
        $this->lock = $lock;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->lock?->delete();
    }
}
