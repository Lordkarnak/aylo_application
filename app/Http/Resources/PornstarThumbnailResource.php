<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PornstarThumbnailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'height' => $this->height,
            'width' => $this->width,
            'type' => $this->type,
            'urls' => PornstarThumbnailUrlResource::collection($this->urls)
        ];
    }
}
