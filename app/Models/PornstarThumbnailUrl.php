<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PornstarThumbnailUrl extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'pornstar_thumbnail_id', 'url', 'cached'];

    public function pornstar_thumbnail() : BelongsTo
    {
        return $this->belongsTo(PornstarThumbnail::class);
    }
}
