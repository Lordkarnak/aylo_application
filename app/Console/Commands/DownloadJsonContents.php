<?php

namespace App\Console\Commands;

use App\Models\Pornstar;
use App\Services\PornstarService;
use File;
use Illuminate\Console\Command;
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
    protected $signature = 'app:get-pornstars';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    private $items = [];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $url = "https://ph-c3fuhehkfqh6huc0.z01.azurefd.net/feed_pornstars.json";
        $this->info("Getting contents from json at $url");

        try {
            $service = new PornstarService();
            $items = $service->fetch($url);
            foreach ($items as $item) {
                $this->line("Storing item...");
                // $pornstar = $service->store($item);
                $pornstar = Pornstar::find($item['id'])->first();
                
                $this->line("Caching images...");
                $service->cache($pornstar);

                $cachedImage = $service->retrieveCachedImage($pornstar);
                $this->line("Retrieved cached image data: ");
                $this->line($cachedImage ?: 'null');
            }
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            exit;
        }

        $this->info("Info fetched and saved successfully.");
        exit;
    }
}
