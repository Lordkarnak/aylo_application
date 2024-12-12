<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PornstarResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'attributes' => [
                'hairColor' => $this->hair_color,
                'ethnicity' => $this->ethnicity,
                'tattoos' => $this->tattoos,
                'piercings' => $this->piercings,
                'breastSize' => $this->breast_size,
                'breastType' => $this->breast_type,
                'gender' => $this->gender,
                'orientation' => $this->orientation,
                'age' => $this->age,
                'stats' => [
                    'subscriptions' => $this->subscriptions,
                    'monthlySearches' => $this->monthly_searches,
                    'views' => $this->views,
                    'videosCount' => $this->videos_count,
                    'premiumVideosCount' => $this->premium_videos_count,
                    'whiteLabelVideoCount' => $this->white_label_video_count,
                    'rank' => $this->rank,
                    'rankPremium' => $this->rank_premium,
                    'rankWl' => $this->rank_wl,
                ]
            ],
            'id' => $this->id,
            'name' => $this->name,
            'license' => $this->license,
            'wlStatus' => $this->wl_status,
            'aliases' => PornstarAliasResource::collection($this->aliases),
            'link' => $this->link,
            'thumbnails' => PornstarThumbnailResource::collection($this->thumbnails)
        ];
    }
}
