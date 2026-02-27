<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BehindTheSceneImage extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'behind_the_scenes_images';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'bts',
        'image',
        'image_thumb',
        'short_order',
        'status',
        'created_by',
        'last_updated_by',
        'last_update_ip',
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
}