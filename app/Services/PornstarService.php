<?php

namespace App\Services;

use App\Models\Pornstar;
use App\Models\PornstarAlias;
use App\Models\PornstarThumbnail;
use App\Models\PornstarThumbnailUrl;
use Illuminate\Console\Command;
use Illuminate\Console\Concerns\InteractsWithIO;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;

class PornstarService
{
    public bool $debug = true;
    
    public function fetchAndSave(string $url)
    {
        $data = $this->fetch($url);
        if (!$data) {
            throw new \Exception("Could not parse data.");
        }

        $this->store($data);
        $this->cache();
    }
    
    public function fetch(string $url, $bForce = false): array
    {
        if (empty($url)) { return []; }

        // For testing, use this properly formatted stream
        // $stream = '[{"attributes":{"hairColor":"Blonde","ethnicity":"White","tattoos":true,"piercings":true,"breastSize":34,"breastType":"A","gender":"female","orientation":"straight","age":43,"stats":{"subscriptions":5687,"monthlySearches":822300,"views":458473,"videosCount":52,"premiumVideosCount":27,"whiteLabelVideoCount":42,"rank":4298,"rankPremium":4401,"rankWl":4104}},"id":2,"name":"Aaliyah Jolie","license":"REGULAR","wlStatus":"1","aliases":["Aliyah Julie","Aliyah Jolie","Aaliyah","Macy"],"link":"https:\/\/www.pornhub.com\/pornstar\/aaliyah-jolie","thumbnails":[{"height":344,"width":234,"type":"pc","urls":["https:\/\/ei.phncdn.com\/pics\/pornstars\/000\/000\/002\/(m=lciuhScOb_c)(mh=5Lb6oqzf58Pdh9Wc)thumb_22561.jpg"]},{"height":344,"width":234,"type":"mobile","urls":["https:\/\/ei.phncdn.com\/pics\/pornstars\/000\/000\/002\/(m=lciuhScOb_c)(mh=5Lb6oqzf58Pdh9Wc)thumb_22561.jpg"]},{"height":344,"width":234,"type":"tablet","urls":["https:\/\/ei.phncdn.com\/pics\/pornstars\/000\/000\/002\/(m=lciuhScOb_c)(mh=5Lb6oqzf58Pdh9Wc)thumb_22561.jpg"]}]}]';
        // return json_decode($stream, true, 4096, JSON_THROW_ON_ERROR + JSON_INVALID_UTF8_IGNORE);

        // store the json file locally to process it, this way we don't hold the connection alive for the whole decoding period (of 5 seconds or so)
        if (!Storage::has("feed_pornstars.json") || !Storage::exists("feed_pornstars.json")) {
            $response = Http::get($url);
            if ($response->ok()) {
                Cache::forever('feed_etag', $response->getHeader('ETag')[0]);
                Storage::put("feed_pornstars.json", $response->body());
            }
            $response->close();
        }
        // file exists, find if modified and download a new copy
        else {
            $lastModified = new Carbon(Storage::lastModified("feed_pornstars.json"));
            $etag = Cache::get('feed_etag') ?? null;
            $response = Http::withHeader('If-Modified-Since', $lastModified->toRfc7231String())
                // ->withHeader('If-None-Match', $etag)
                ->get($url);
                
            // Not modified, if not forced to run, exit
            if ($response->status() === 304 && $bForce == false) {
                throw new \Exception("File was not modified. Exiting...");
            }
            // modified, download
            else if ($response->status() === 200) {
                Cache::forever('feed_etag', $response->getHeader('ETag')[0]);
                Storage::put("feed_pornstars.json", $response->body());
            }

            $response->close();
        }
        
        // we always read the file locally using the good ol' streaming techniques of php
        $stream = Storage::readStream("feed_pornstars.json");
        $chunkSize = 4096; // large chunks of 4kb hold enough info to begin decoding
        $startOfCreators = -1; // index where the json parsing starts
        $remainder = null; // the remainder holds the previous undecoded chunk, if any
        $trueIndex = 0; // used for debugging the json file
        $output = [];
        
        while (!feof($stream)) {
            $line = fread($stream, $chunkSize);

            // handle undecoded line from previous chunk
            if ($remainder !== null) {
                $line = $remainder . $line;
                $remainder = null;
            }

            $jsonLines = explode("\n", $line);

            // clear the line
            unset($line);

            $index = 0;
            $size = count($jsonLines);

            if ($startOfCreators == -1) {
                while ($index < $size) {
                    if (str_contains($jsonLines[$index], 'items')) {
                        $startOfCreators = $index;
                        $index++;
                        break;
                    }
                    $index++;
                    $trueIndex++;
                }
            }

            // start was set, continue parsing creators
            if ($startOfCreators > -1) {
                for ($index; $index < $size; $index++) {
                    // remove trailing comma
                    $jsonLines[$index] = rtrim($jsonLines[$index], ',');

                    // attempt to decode
                    $parsedLine = json_decode($jsonLines[$index], true, 4096, JSON_INVALID_UTF8_IGNORE);
                    
                    // ensure that only the last bit that wasn't properly decoded gets stored in the remainder
                    if ($parsedLine == null) {
                        if ($index == ($size - 1)) {
                            $remainder = $jsonLines[$index];
                        } else if (preg_match('/"\]\["/', $jsonLines[$index]) !== false) {
                            // attempt to fix and decode again
                            $jsonLines[$index] = preg_replace('/"\]\["/', '"],["',$jsonLines[$index]);
                            $parsedLine = json_decode($jsonLines[$index], true, 4096, JSON_INVALID_UTF8_IGNORE);

                            // successfull parse, store the line
                            if (!empty($parsedLine)) {
                                $output[] = $parsedLine;
                            }
                        } else {
                            if ($this->debug) {
                                echo "\n\nCould not parse line at index [$trueIndex]: " . $jsonLines[$index];
                            }
                        }
                    }
                    else if (!empty($parsedLine)) {
                        $output[] = $parsedLine;
                    }
                    $trueIndex++;
                }
            }
        }
        fclose($stream);

        return $output;
    }

    public function storeWithModels(array $record): Pornstar|null
    {
        $recordCollection = collect($record);
        $attributes = $recordCollection->pull('attributes');
        $stats = $attributes['stats'];
        
        // id is mandatory
        if (!isset($recordCollection['id'])) {
            return null;
        }

        $pornstar = Pornstar::updateOrCreate(
            ["id" => $record['id']],
            [
                'id' => $recordCollection['id'], 'name' => $recordCollection['name'] ?? null, 'license' => $recordCollection['license'] ?? null, 'wl_status' =>  $recordCollection['wlStatus'] ?? null,  'link' => $recordCollection['link'] ?? null,
                'hair_color' => $attributes['hairColor'] ?? null, 'ethnicity' => $attributes['ethnicity'] ?? null, 'tattoos' => $attributes['tattoos'] ?? null, 'piercings' =>  $attributes['piercings'] ?? null,  'breast_size' => $attributes['breastSize'] ?? null, 'breast_type' => $attributes['breastType'] ?? null, 'gender' => $attributes['gender'] ?? null, 'orientation' => $attributes['orientation'] ?? null, 'age' => $attributes['age'] ?? null,
                'subscriptions' => $stats['subscriptions'] ?? null, 'monthly_searches' => $stats['monthlySearches'] ?? null, 'views' => $stats['views'] ?? null, 'videos_count' => $stats['videosCount'] ?? null, 'premium_videos_count' => $stats['premiumVideosCount'] ?? null, 'white_label_video_count' => $stats['whiteLabelVideoCount'] ?? null, 'rank' => $stats['rank'] ?? null, 'rank_premium' => $stats['rankPremium'] ?? null, 'rank_wl' => $stats['rankWl'] ?? null
            ]
        );

        // Save aliases as a relation of one (pornstar) to many (names)
        $aliases = [];
        foreach ($recordCollection->get('aliases') as $alias) {
            if (!empty($alias)) {
                $aliases[] = PornstarAlias::firstOrCreate(['pornstar_id' => $pornstar->id, 'alias' => $alias]);
            }
        }
        // store aliases to pornstar
        $pornstar->aliases()->saveMany($aliases);

        // Save thumbnails as a relation of one (pornstar) to many (thumbnails)
        $thumbnails = [];
        foreach ($recordCollection->get('thumbnails') as $thumbnailRef) {
            $thumbnail = PornstarThumbnail::firstOrCreate(
                ['pornstar_id' => $pornstar->id, 'type' => strtolower($thumbnailRef['type'])  ?? 'pc'],
                ['width' => $thumbnailRef['width'] ?? null, 'height' => $thumbnailRef['height'] ?? null]
            );
            $thumbnails[] = $thumbnail;

            // Save thumbnail urls as a relation of one (thumbnail) to many (alternative urls)
            $thumbnailUrls = [];
            foreach ($thumbnailRef['urls'] as $url) {
                if (!empty($url)) {
                    $thumbnailUrls[] = PornstarThumbnailUrl::firstOrCreate(['pornstar_thumbnail_id' => $thumbnail->id, 'url' => $url]);
                }
            }
            // Store urls to thumbnail
            $thumbnail->urls()->saveMany($thumbnailUrls);
        }

        // finally, store thumbnails to pornstar
        $pornstar->thumbnails()->saveMany($thumbnails);

        return $pornstar;
    }

    public function store(array &$items, $withProgressBar = false)
    {
        // $pornstarRecords = [];
        // $thumbnailRecords = [];
        // $urlsRecords = [];
        
        if ($withProgressBar) {
            $consoleOutput = new ConsoleOutput();
            $progress = new ProgressBar($consoleOutput, array_key_last($items));
            $progress->start();
        }

        while (!empty($items)) {
            try {
                // get an item
                $item = array_pop($items);

                // prepare pornstar
                $pornstar = [
                    'id' => $item['id'],
                    'name' => $item['name'] ?? null,
                    'license' => $item['license'] ?? null,
                    'wl_status' =>  $item['wlStatus'] ?? null,
                    'link' => $item['link'] ?? null,

                    // attributes
                    'hair_color' => $item['attributes']['hairColor'] ?? null,
                    'ethnicity' => $item['attributes']['ethnicity'] ?? null,
                    'tattoos' => $item['attributes']['tattoos'] ?? null,
                    'piercings' =>  $item['attributes']['piercings'] ?? null,
                    'breast_size' => $item['attributes']['breastSize'] ?? null,
                    'breast_type' => $item['attributes']['breastType'] ?? null,
                    'gender' => $item['attributes']['gender'] ?? null,
                    'orientation' => $item['attributes']['orientation'] ?? null,
                    'age' => $item['attributes']['age'] ?? null,

                    // attributes -> stats
                    'subscriptions' => $item['attributes']['stats']['subscriptions'] ?? null,
                    'monthly_searches' => $item['attributes']['stats']['monthlySearches'] ?? null,
                    'views' => $item['attributes']['stats']['views'] ?? null,
                    'videos_count' => $item['attributes']['stats']['videosCount'] ?? null,
                    'premium_videos_count' => $item['attributes']['stats']['premiumVideosCount'] ?? null,
                    'white_label_video_count' => $item['attributes']['stats']['whiteLabelVideoCount'] ?? null,
                    'rank' => $item['attributes']['stats']['rank'] ?? null,
                    'rank_premium' => $item['attributes']['stats']['rankPremium'] ?? null,
                    'rank_wl' => $item['attributes']['stats']['rankWl'] ?? null
                ];

                // store pornstar
                DB::table('pornstars')->upsert($pornstar, 'id', [
                    'id',
                    'name',
                    'license',
                    'wl_status',
                    'link',
                    'hair_color',
                    'ethnicity',
                    'tattoos',
                    'piercings',
                    'breast_size',
                    'breast_type',
                    'gender',
                    'orientation',
                    'age',
                    'subscriptions',
                    'monthly_searches',
                    'views',
                    'videos_count',
                    'premium_videos_count',
                    'white_label_video_count',
                    'rank',
                    'rank_premium',
                    'rank_wl'
                ]);

                // clear aliases, in case something changed in aliases size
                // delete without first selecting records to view size as we have to process too many lines and we need to reduce db calls and processing logic as much as possible.
                DB::table('pornstar_aliases')->where('pornstar_id', $item['id'])->delete();
                
                // prepare aliases
                $aliases = [];
                foreach ($item['aliases'] as $alias) {
                    $aliases[] = [
                        'pornstar_id' => $item['id'],
                        'alias' => $alias
                    ];
                }
                
                // store new aliases
                DB::table('pornstar_aliases')->insert($aliases);


                // clear thumbnails and links, in case something changed in thumbnails size
                // delete without first selecting records to view size as we have to process too many lines and we need to reduce db calls and processing logic as much as possible.
                DB::table('pornstar_thumbnail_urls')->where('pornstar_id', $item['id'])->delete();
                DB::table('pornstar_thumbnails')->where('pornstar_id', $item['id'])->delete();

                // clear cache for these links
                $this->invalidateCachedImage($item['id']);
                
                foreach ($item['thumbnails'] as $thumbnail) {
                    // prepare and store thumbnail
                    $thumbnailId = DB::table('pornstar_thumbnails')->insertGetId([
                        'pornstar_id' => $item['id'],
                        'height' => $thumbnail['height'] ?? null,
                        'width' => $thumbnail['width'] ?? null,
                        'type' => $thumbnail['type'] ?? 'pc',
                    ]);

                    // prepare and store urls for thumbnail
                    foreach ($thumbnail['urls'] as $url) {
                        DB::table('pornstar_thumbnail_urls')->insert([
                            'pornstar_id' => $item['id'],
                            'pornstar_thumbnail_id' => $thumbnailId,
                            'url' => $url
                        ]);
                    }
                }
            } catch (\Exception $e) {
                if ($withProgressBar) { $progress->clear(); }

                if ($this->debug) {
                    $consoleOutput->write($e->getMessage(), $newline = true);
                }
                
                if ($withProgressBar) { $progress->display(); }
            }

            if ($withProgressBar) { $progress->advance(); }
        }
        
        if ($withProgressBar) { $progress->finish(); }
    }

    public function cache($withProgressBar = false)
    {
        $items = DB::table('pornstar_thumbnail_urls')->select(DB::raw('MIN(pornstar_id) AS pornstar_id, MIN(pornstar_thumbnail_id) AS pornstar_thumbnail_id, url'))->groupBy('url')->where('cached', 0)->get();

        if ($withProgressBar) {
            $consoleOutput = new ConsoleOutput();
            $progress = new ProgressBar($consoleOutput, $items->count());
            $progress->start();
        }

        while (!empty($items)) {
            try {
                $item = $items->pop();
                $key = 'thumb_' . $item->pornstar_id . '_' . $item->pornstar_thumbnail_id;
                if (!Cache::has($key)) {
                    $response = Http::get($item->url);
                    $content = $response->body();
                    Cache::put($key, $content, now()->addDay());
                    DB::table('pornstar_thumbnail_urls')
                        ->where('pornstar_id', $item->pornstar_id)
                        ->where('pornstar_thumbnail_id', $item->pornstar_thumbnail_id)
                        ->update(['cached' => 1]);
                }
            } catch (\Exception $e) {
                if ($withProgressBar) { $progress->clear(); }

                if ($this->debug) {
                    $consoleOutput->write($e->getMessage(), $newline = true);
                }
                
                if ($withProgressBar) { $progress->display(); }
            }

            if ($withProgressBar) { $progress->advance(); }
        }
        
        if ($withProgressBar) { $progress->finish(); }
    }

    public function cacheByPornstar(Pornstar $pornstar)
    {
        if (empty($pornstar)) {
            throw new \Exception('No pornstar present. Skipping cache.');
        }

        if (!$pornstar->thumbnails()->exists()) {
            throw new \Exception('Pornstar has no thumbnails. Skipping cache.');
        }

        $urls = collect([]);
        foreach ($pornstar->thumbnails as $thumbnail) {
            if (!$thumbnail->urls()->exists()) {
                continue;
            }

            // Collect all urls of all thumbnails
            foreach ($thumbnail->urls as $item) {
                $urls->add($item);
            }
        }

        // Ensure no duplicate urls get fetched, as per requirement.
        $urls = $urls->unique('url');
        foreach ($urls as $item) {
            // cache
            $key = 'thumb_' . $pornstar->id . '_' . $item->pornstar_thumbnail_id;
            $content = Http::get($item->url)->body();
            Cache::put($key, $content, now()->addDay());

            PornstarThumbnailUrl::where('pornstar_id', $pornstar->id)
                ->where('pornstar_thumbnail_id', $item->pornstar_thumbnail_id)
                ->update(['cached' => 1]);
        }
    }

    public function invalidateCachedImage($pornstar_id, $thumbnail_id = null): bool
    {
        if (empty($pornstar_id)) {
            throw new \Exception("No pornstar id present. Could not retrieve cache for an empty reference.");
        }

        // erase all thumbnail cache
        if (empty($thumbnail_id)) {
            $bSuccess = false;
            $thumbnailUrls = DB::table('pornstar_thumbnail_urls')->where('pornstar_id', $pornstar_id)->where('cached', 1)->get('id');
            foreach ($thumbnailUrls as $thumbnailUrl) {
                $key = 'thumb_' . $pornstar_id . '_' . $thumbnailUrl->id;
                $bSuccess = $bSuccess && Cache::forget($key);
            }
            
            return $bSuccess;
        }

        $key = 'thumb_' . $pornstar_id . '_' . $thumbnail_id;
        return Cache::forget($key);
    }

    public function invalidateCache(): bool
    {
        $bSuccess = false;
        $cachedThumbnails = PornstarThumbnail::where('cached', 1)->get(['id', 'pornstar_id']);
        foreach ($cachedThumbnails as $thumbnail) {
            $key = 'thumb_' . $thumbnail['pornstar_id'] . '_' . $thumbnail['id'];
            $bSuccess = $bSuccess && Cache::forget($key);
        }
        return $bSuccess;
    }

    public function retrieveCachedImage(Pornstar $pornstar, ?PornstarThumbnail $thumbnail = null) : string|null
    {
        if (empty($pornstar)) {
            throw new \Exception("No pornstar present. Could not retrieve cache for an empty reference.");
        }
        if (empty($thumbnail)) {
            $thumbnail = $pornstar->thumbnails->where('cached', 1)->first();
            if ($thumbnail) {
                $key = 'thumb_' . $pornstar->id . '_' . $thumbnail->id;
            }
        } else {
            $key = 'thumb_' . $pornstar->id . '_' . $thumbnail->id;
        }

        return Cache::get($key);
    }
}