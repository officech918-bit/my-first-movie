<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BehindTheScene extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'behind_the_scenes';

    /**
     * The name of the "created at" column.
     *
     * @var string
     */
    const CREATED_AT = 'create_date';

    /**
     * The name of the "updated at" column.
     *
     * @var string
     */
    const UPDATED_AT = 'last_update';

    /**
     * The attributes that are mass assignable.
     *
     * @var     array
     */
    protected $fillable = [
        'title',
        'display_note',
        'season',
        'day',
        'image',
        'video_url',
        'status',
        'screenshot',
        'screenshot_thumb',
        'short_order',
        'created_by',
        'last_updated_by',
        'last_update_ip',
    ];

    /**
     * Eloquent automatically manages created_at and updated_at columns.
     * Set to false if your table does not have these columns.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * Get the images for the behind the scenes post.
     */
    public function images()
    {
        return $this->hasMany(BehindTheSceneImage::class, 'bts');
    }
}