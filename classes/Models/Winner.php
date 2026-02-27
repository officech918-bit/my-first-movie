<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Winner extends Model
{
    protected $table = 'winners';

    protected $fillable = [
        'user_id',
        'season_id',
        'category_id',
        'created_at',
        'announcement_date',
        'description',
        'image',
        'image_thumb',
        'rank_position',
        'title',
        'winner_photo',
    ];

    public $timestamps = false;

    const CREATED_AT = 'created_at';

    public function user()
    {
        return $this->belongsTo(WebUser::class, 'user_id', 'uid');
    }

    public function season()
    {
        return $this->belongsTo(Season::class, 'season_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}
