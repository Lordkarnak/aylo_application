<?php

namespace App\Console\Commands;

use App\Models\Pornstar;
use App\Services\PornstarService;
use File;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use PhpParser\PrettyPrinter;
use PHPUnit\Framework\Constraint\IsEmpty;
use Storage;
use Str;
use function PHPUnit\Framework\isEmpty;

class DownloadJsonContents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:get-pornstars {--force} {--debug} {--max=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Decode the incoming json and store pornstars and their thumbnails in local db';

    private $items = [];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Getting contents from json at " . Config::get('app.feed_url'));

        /** Ideally, this command would have a nice foreach and would call the service for every item that the service decoded.
         * As the table is pretty large, I prefer to call the loops inside each function and remove the processed cells once done.
         * I optimized the command memory requirements some more by passing the items array by reference so the items will vanish.
         */
        try {
            $bForce = $this->option('force') ?? false;

            $service = new PornstarService();
            $service->debug = $this->option('debug') ?? false;
            
            $maxItems = $this->option('max') ?? -1;
            if ($maxItems > -1) {
                $this->info("Parsing only [$maxItems] items");
            }
            $items = $service->fetch(Config::get('app.feed_url'), $bForce, $maxItems);

            $this->info('Storing items...');
            $service->store($items, true);
            $this->info('Done!');

            $this->info('Caching items thumbnails...');
            $service->cache($items, true);
            $this->info('Done!');

            // a progress bar to beautify this process
            /* $bar = $this->output->createProgressBar(count($items));
            $bar->start();

            foreach ($items as &$item) {
                try {
                    $pornstar = $service->store($item);
                    $service->cache($pornstar);
                } catch (\Exception $e) {
                    $bar->clear();
                    $this->error($e->getMessage());
                    $bar->display();
                }

                $bar->advance();
            } */
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            if (str_contains($e->getMessage(), 'not modified')) {
                $this->error('Try running this command with option --force to retrieve content.');
            }
        }

        // $bar->finish();
        $this->info("Info fetched and saved successfully.");
        exit;
    }
}
