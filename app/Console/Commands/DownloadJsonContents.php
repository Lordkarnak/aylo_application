<?php

namespace App\Console\Commands;

use App\Models\Pornstar;
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

/*         if (!Storage::has("feed_pornstars.json") || !Storage::exists("feed_pornstars.json")) {

            $response = Http::get($url);
            if ($response->ok()) {
                Storage::put("feed_pornstars.json", $response->body());
            }
            $response->close();
        }

        $stream = Storage::read("feed_pornstars.json");
 */
        $stream = '{"attributes":{"hairColor":"Blonde","ethnicity":"White","tattoos":true,"piercings":true,"breastSize":34,"breastType":"A","gender":"female","orientation":"straight","age":43,"stats":{"subscriptions":5687,"monthlySearches":822300,"views":458473,"videosCount":52,"premiumVideosCount":27,"whiteLabelVideoCount":42,"rank":4298,"rankPremium":4401,"rankWl":4104}},"id":2,"name":"Aaliyah Jolie","license":"REGULAR","wlStatus":"1","aliases":["Aliyah Julie","Aliyah Jolie","Aaliyah","Macy"],"link":"https:\/\/www.pornhub.com\/pornstar\/aaliyah-jolie","thumbnails":[{"height":344,"width":234,"type":"pc","urls":["https:\/\/ei.phncdn.com\/pics\/pornstars\/000\/000\/002\/(m=lciuhScOb_c)(mh=5Lb6oqzf58Pdh9Wc)thumb_22561.jpg"]},{"height":344,"width":234,"type":"mobile","urls":["https:\/\/ei.phncdn.com\/pics\/pornstars\/000\/000\/002\/(m=lciuhScOb_c)(mh=5Lb6oqzf58Pdh9Wc)thumb_22561.jpg"]},{"height":344,"width":234,"type":"tablet","urls":["https:\/\/ei.phncdn.com\/pics\/pornstars\/000\/000\/002\/(m=lciuhScOb_c)(mh=5Lb6oqzf58Pdh9Wc)thumb_22561.jpg"]}]}';

        try {
            $data = json_decode($stream, true, 4096, JSON_THROW_ON_ERROR + JSON_INVALID_UTF8_IGNORE);            
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }

        if ($data) {
            $this->info("Info fetched successfully.");
            // dd($data);

            $record = Pornstar::updateOrCreate(
                ["id" => $data['id']],
                [
                    'id' => $data['id'], 'name' => $data['name'], 'license' => $data['license'], 'wl_status' =>  $data['wlStatus'],  'link' => $data['link'],
                    'hair_color' => $data['hairColor'], 'ethnicity' => $data['ethnicity'], 'tattoos' => $data['tattoos'], 'piercings' =>  $data['piercings'],  'breast_size' => $data['breastSize'], 'breast_type' => $data['breastType'], 'gender' => $data['gender'], 'orientation' => $data['orientation'], 'age' => $data['age'],
                    'subscriptions' => $data['subscriptions'], 'monthly_searches' => $data['monthlySearches']
                ]
            );
            exit;
        } else {
            $this->info("Info was empty");
        }
    }
}
