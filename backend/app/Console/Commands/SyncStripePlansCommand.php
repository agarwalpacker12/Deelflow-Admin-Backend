<?php

namespace App\Console\Commands;


use Illuminate\Console\Command;
use App\Jobs\SyncStripePlans;

class SyncStripePlansCommand extends Command
{
    protected $signature = 'stripe:sync-plans';
    protected $description = 'Sync subscription packages from Stripe to local DB';

    public function handle()
    {
        SyncStripePlans::dispatchSync(); // Runs immediately
        $this->info('Stripe plans synced successfully.');
    }
}
