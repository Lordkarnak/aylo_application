<?php

namespace App\Services;

use App\Models\Pornstar;

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
        // For testing, use this properly formatted stream
        $stream = '{"attributes":{"hairColor":"Blonde","ethnicity":"White","tattoos":true,"piercings":true,"breastSize":34,"breastType":"A","gender":"female","orientation":"straight","age":43,"stats":{"subscriptions":5687,"monthlySearches":822300,"views":458473,"videosCount":52,"premiumVideosCount":27,"whiteLabelVideoCount":42,"rank":4298,"rankPremium":4401,"rankWl":4104}},"id":2,"name":"Aaliyah Jolie","license":"REGULAR","wlStatus":"1","aliases":["Aliyah Julie","Aliyah Jolie","Aaliyah","Macy"],"link":"https:\/\/www.pornhub.com\/pornstar\/aaliyah-jolie","thumbnails":[{"height":344,"width":234,"type":"pc","urls":["https:\/\/ei.phncdn.com\/pics\/pornstars\/000\/000\/002\/(m=lciuhScOb_c)(mh=5Lb6oqzf58Pdh9Wc)thumb_22561.jpg"]},{"height":344,"width":234,"type":"mobile","urls":["https:\/\/ei.phncdn.com\/pics\/pornstars\/000\/000\/002\/(m=lciuhScOb_c)(mh=5Lb6oqzf58Pdh9Wc)thumb_22561.jpg"]},{"height":344,"width":234,"type":"tablet","urls":["https:\/\/ei.phncdn.com\/pics\/pornstars\/000\/000\/002\/(m=lciuhScOb_c)(mh=5Lb6oqzf58Pdh9Wc)thumb_22561.jpg"]}]}';
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
        return Pornstar::updateOrCreate(
            ["id" => $record['id']],
            [
                'id' => $record['id'], 'name' => $record['name'], 'license' => $record['license'], 'wl_status' =>  $record['wlStatus'],  'link' => $record['link'],
                'hair_color' => $record['hairColor'], 'ethnicity' => $record['ethnicity'], 'tattoos' => $record['tattoos'], 'piercings' =>  $record['piercings'],  'breast_size' => $record['breastSize'], 'breast_type' => $record['breastType'], 'gender' => $record['gender'], 'orientation' => $record['orientation'], 'age' => $record['age'],
                'subscriptions' => $record['subscriptions'], 'monthly_searches' => $record['monthlySearches']
            ]
        );
    }
}