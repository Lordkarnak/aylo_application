<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PornstarThumbnail extends Model
{
    /** @use HasFactory<\Database\Factories\PornstarThumbnailFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'pornstar_id', 'height', 'width', 'type'];

    public function pornstar() : BelongsTo
    {
        return $this->belongsTo(Pornstar::class);
    }

    public function urls() : HasMany
    {
        return $this->hasMany(PornstarThumbnailUrl::class);
    }
}
