<?php

namespace Tv2regionerne\StatamicLocks\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Tv2regionerne\StatamicLocks\Models\LockModel;

class ClearLocks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statamic-locks:clear-locks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear locks on your items';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Clearing locks...');
        $this->line('');

        LockModel::where('updated_at', '<', Carbon::now()->subMinutes(config('statamic-locks.clear_locks_after', 5)))
            ->get()
            ->each
            ->delete();

        $this->info('✔️ Done');

        return 1;
    }
}
