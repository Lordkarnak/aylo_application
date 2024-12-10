<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pornstar extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'name', 'license', 'wl_status', 'link', 'hair_color',
        'ethnicity', 'tattoos', 'piercings', 'breast_size', 'breast_type', 'gender', 'orientation', 'age',
        'subscriptions', 'monthly_searches', 'views', 'videos_count', 'premium_videos_count', 'white_label_video_count', 'rank', 'rank_premium', 'rank_wl'
    ];
}
