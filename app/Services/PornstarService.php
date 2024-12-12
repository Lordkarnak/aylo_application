<?php

namespace App\Services;

use App\Models\Pornstar;
use App\Models\PornstarAlias;
use App\Models\PornstarThumbnail;
use App\Models\PornstarThumbnailUrl;
use Cache;
use Http;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class PornstarService
{
    public bool $debug = false;
    
    public function fetchAndSave(string $url): bool
    {
        $data = $this->fetch($url);
        if (!$data) {
            throw new \Exception("Could not parse data.");
        }

        foreach ($data as $record) {
            $pornstar = $this->store($record);
            $this->cache($pornstar);
        }

        return true;
    }
    
    public function fetch(string $url): array
    {
        if (empty($url)) { return []; }

        // For testing, use this properly formatted stream
        // $stream = '[{"attributes":{"hairColor":"Blonde","ethnicity":"White","tattoos":true,"piercings":true,"breastSize":34,"breastType":"A","gender":"female","orientation":"straight","age":43,"stats":{"subscriptions":5687,"monthlySearches":822300,"views":458473,"videosCount":52,"premiumVideosCount":27,"whiteLabelVideoCount":42,"rank":4298,"rankPremium":4401,"rankWl":4104}},"id":2,"name":"Aaliyah Jolie","license":"REGULAR","wlStatus":"1","aliases":["Aliyah Julie","Aliyah Jolie","Aaliyah","Macy"],"link":"https:\/\/www.pornhub.com\/pornstar\/aaliyah-jolie","thumbnails":[{"height":344,"width":234,"type":"pc","urls":["https:\/\/ei.phncdn.com\/pics\/pornstars\/000\/000\/002\/(m=lciuhScOb_c)(mh=5Lb6oqzf58Pdh9Wc)thumb_22561.jpg"]},{"height":344,"width":234,"type":"mobile","urls":["https:\/\/ei.phncdn.com\/pics\/pornstars\/000\/000\/002\/(m=lciuhScOb_c)(mh=5Lb6oqzf58Pdh9Wc)thumb_22561.jpg"]},{"height":344,"width":234,"type":"tablet","urls":["https:\/\/ei.phncdn.com\/pics\/pornstars\/000\/000\/002\/(m=lciuhScOb_c)(mh=5Lb6oqzf58Pdh9Wc)thumb_22561.jpg"]}]}]';
        // return json_decode($stream, true, 4096, JSON_THROW_ON_ERROR + JSON_INVALID_UTF8_IGNORE);

        // store the json file locally to process it, this way we don't hold the connection alive for the whole decoding period (of 5 seconds or so)
        if (!Storage::has("feed_pornstars.json") || !Storage::exists("feed_pornstars.json")) {

            $response = Http::get($url);
            if ($response->ok()) {
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
                    $parsedLine = json_decode($jsonLines[$index], true, 4096, JSON_INVALID_UTF8_IGNORE);
                    
                    // ensure that only the last bit that wasn't properly decoded gets stored in the remainder
                    if ($parsedLine == null) {
                        if ($index == ($size - 1)) {
                            $remainder = $jsonLines[$index];
                        } else {
                            if ($this->debug) {
                                echo "\n\nCould not parse line at index [$trueIndex]: " . $jsonLines[$index];
                            }
                        }
                    }
                    else if (!empty($parsedLine)) {
                        $parsedJson[] = $parsedLine;
                    }
                    $trueIndex++;
                }
            }
        }
        fclose($stream);

        return $parsedJson;
    }

    public function cache(Pornstar $pornstar)
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

            PornstarThumbnail::where('id', $item->pornstar_thumbnail_id)->update(['cached' => 1]);
        }
    }

    public function invalidateCachedImage(Pornstar $pornstar, ?PornstarThumbnail $thumbnail = null): bool
    {
        if (empty($pornstar)) {
            throw new \Exception("No pornstar present. Could not retrieve cache for an empty reference.");
        }

        if (empty($thumbnail)) {
            $bSuccess = false;
            $thumbnails = $pornstar->thumbnails()->where('cached', 1)->get();
            foreach ($thumbnails as $thumbnail) {
                $key = 'thumb_' . $pornstar->id . '_' . $thumbnail->id;
                $bSuccess = $bSuccess && Cache::forget($key);
            }
            
            return $bSuccess;
        }

        $key = 'thumb_' . $pornstar->id . '_' . $thumbnail->id;
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

    public function store(array $record): Pornstar
    {
        $recordCollection = collect($record);
        $attributes = $recordCollection->pull('attributes');
        $stats = $attributes['stats'];
        
        $pornstar = Pornstar::updateOrCreate(
            ["id" => $record['id']],
            [
                'id' => $recordCollection['id'], 'name' => $recordCollection['name'], 'license' => $recordCollection['license'], 'wl_status' =>  $recordCollection['wlStatus'],  'link' => $recordCollection['link'],
                'hair_color' => $attributes['hairColor'], 'ethnicity' => $attributes['ethnicity'], 'tattoos' => $attributes['tattoos'], 'piercings' =>  $attributes['piercings'],  'breast_size' => $attributes['breastSize'], 'breast_type' => $attributes['breastType'], 'gender' => $attributes['gender'], 'orientation' => $attributes['orientation'], 'age' => $attributes['age'],
                'subscriptions' => $stats['subscriptions'], 'monthly_searches' => $stats['monthlySearches'], 'views' => $stats['views'], 'videos_count' => $stats['videosCount'], 'premium_videos_count' => $stats['premiumVideosCount'], 'white_label_video_count' => $stats['whiteLabelVideoCount'], 'rank' => $stats['rank'], 'rank_premium' => $stats['rankPremium'], 'rank_wl' => $stats['rankWl']
            ]
        );

        // Save aliases
        $aliases = [];
        foreach ($recordCollection->get('aliases') as $alias) {
            if ($alias != '') {
                $aliases[] = PornstarAlias::firstOrCreate(['pornstar_id' => $pornstar->id, 'alias' => $alias]);
            }
        }
        // store aliases to pornstar
        $pornstar->aliases()->saveMany($aliases);

        // Save thumbnails
        $thumbnails = [];
        foreach ($recordCollection->get('thumbnails') as $thumbnailRef) {
            $thumbnail = PornstarThumbnail::firstOrCreate(
                ['pornstar_id' => $pornstar->id, 'type' => $thumbnailRef['type']],
                ['width' => $thumbnailRef['width'], 'height' => $thumbnailRef['height']]
            );
            $thumbnails[] = $thumbnail;

            // Save thumbnail urls
            $thumbnailUrls = [];
            foreach ($thumbnailRef['urls'] as $url) {
                if ($url != '') {
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
}