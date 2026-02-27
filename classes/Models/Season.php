<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Season extends Model
{
    protected $table = 'seasons';

    protected $fillable = [
        'title',
        'display_note',
        'start_date',
        'end_date',
        'status',
        'fee',
        'created_by',
        'last_updated_by',
        'last_update_ip',
        'is_default',
    ];

    public $timestamps = true;

    const CREATED_AT = 'create_date';
    const UPDATED_AT = 'last_update';

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'season_categories', 'season_id', 'cat_id');
    }
}