<?php

namespace App\Services;

use App\Models\Pornstar;
use App\Models\PornstarAlias;
use App\Models\PornstarThumbnail;
use App\Models\PornstarThumbnailUrl;
use Illuminate\Support\Collection;

class PornstarService
{
    public function fetchAndSave(string $url)
    {
        $data = $this->fetch($url);
        if (!$data) {
            throw new \Exception("Could not parse data.");
        }

        foreach ($data as $record) {
            $pornstar = $this->store($record);
            $this->cache($pornstar);
        }
    }
    
    public function fetch(string $url): array
    {
        if (empty($url)) { return []; }
        
        // For testing, use this properly formatted stream
        $stream = '[{"attributes":{"hairColor":"Blonde","ethnicity":"White","tattoos":true,"piercings":true,"breastSize":34,"breastType":"A","gender":"female","orientation":"straight","age":43,"stats":{"subscriptions":5687,"monthlySearches":822300,"views":458473,"videosCount":52,"premiumVideosCount":27,"whiteLabelVideoCount":42,"rank":4298,"rankPremium":4401,"rankWl":4104}},"id":2,"name":"Aaliyah Jolie","license":"REGULAR","wlStatus":"1","aliases":["Aliyah Julie","Aliyah Jolie","Aaliyah","Macy"],"link":"https:\/\/www.pornhub.com\/pornstar\/aaliyah-jolie","thumbnails":[{"height":344,"width":234,"type":"pc","urls":["https:\/\/ei.phncdn.com\/pics\/pornstars\/000\/000\/002\/(m=lciuhScOb_c)(mh=5Lb6oqzf58Pdh9Wc)thumb_22561.jpg"]},{"height":344,"width":234,"type":"mobile","urls":["https:\/\/ei.phncdn.com\/pics\/pornstars\/000\/000\/002\/(m=lciuhScOb_c)(mh=5Lb6oqzf58Pdh9Wc)thumb_22561.jpg"]},{"height":344,"width":234,"type":"tablet","urls":["https:\/\/ei.phncdn.com\/pics\/pornstars\/000\/000\/002\/(m=lciuhScOb_c)(mh=5Lb6oqzf58Pdh9Wc)thumb_22561.jpg"]}]}]';
        return json_decode($stream, true, 4096, JSON_THROW_ON_ERROR + JSON_INVALID_UTF8_IGNORE);

        /* if (!Storage::has("feed_pornstars.json") || !Storage::exists("feed_pornstars.json")) {

            $response = Http::get($url);
            if ($response->ok()) {
                Storage::put("feed_pornstars.json", $response->body());
            }
            $response->close();
        }
            
        $stream = Storage::read("feed_pornstars.json"); */
    }

    public function cache(Pornstar $pornstar)
    {

    }

    private function store(array $record): Pornstar
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
            $aliases[] = PornstarAlias::firstOrCreate(['pornstar_id' => $pornstar->id, 'alias' => $alias]);
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
                $thumbnailUrls[] = PornstarThumbnailUrl::firstOrCreate(['pornstar_thumbnail_id' => $thumbnail->id, 'url' => $url]);
            }
            // Store urls to thumbnail
            $thumbnail->urls()->saveMany($thumbnailUrls);
        }

        // finally, store thumbnails to pornstar
        $pornstar->thumbnails()->saveMany($thumbnails);

        return $pornstar;
    }
}