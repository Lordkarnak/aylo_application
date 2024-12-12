<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class GetImage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:get-image';

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
        //
        $url = 'https://ei.phncdn.com/pics/pornstars/000/000/002/(m=lciuhScOb_c)(mh=5Lb6oqzf58Pdh9Wc)thumb_22561.jpg';
        // $response = Http::get($url);
        // $imageContents = $response->body();

        // Cache::put('testimage', $imageContents, now()->addMinutes(2));
        // $cachedContent = Cache::get('testimage');
        // $this->line(empty($cachedContent) ? 'empty' : $cachedContent);
        $this->line(dump(Cache::has('testimage')));
    }
}
