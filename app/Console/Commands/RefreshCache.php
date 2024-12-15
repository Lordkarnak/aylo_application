<?php

namespace App\Console\Commands;

use App\Models\Pornstar;
use App\Services\PornstarService;
use Illuminate\Console\Command;

class RefreshCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:refresh-cache {--debug} {--all}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $service = new PornstarService();
        $service->debug = $this->option('debug') ?? false;

        $this->info('Retrieving items...');
        $pornstars = Pornstar::all();

        $this->info('Caching items thumbnails...');
        foreach ($pornstars as $pornstar) {
            try {
                $service->cacheByPornstar($pornstar);
            } catch (\Exception $e) {
                $this->error($e->getMessage());
            }
        }

        $this->info('Cache refreshed!');
    }
}
