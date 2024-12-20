<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PornstarAlias extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'pornstar_id', 'alias'];

    public function pornstar() : BelongsTo
    {
        return $this->belongsTo(Pornstar::class);
    }
}
