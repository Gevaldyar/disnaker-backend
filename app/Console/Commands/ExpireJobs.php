<?php

namespace App\Console\Commands;

use App\Models\Job;
use Illuminate\Console\Command;

class ExpireJobs extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'jobs:expire';

    /**
     * The console command description.
     */
    protected $description = 'Mengubah lowongan yang melewati deadline menjadi expired';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $count = Job::where('status', 'approved')
            ->whereNotNull('expires_at')
            ->whereDate('expires_at', '<', today())
            ->update([
                'status' => 'expired',
            ]);

        $this->info("{$count} lowongan berhasil diubah menjadi expired.");

        return self::SUCCESS;
    }
}