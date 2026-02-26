<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'industry_news';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'headline',
        'content',
        'image',
        'is_admin_news',
        'status',
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    const CREATED_AT = 'create_date';
    const UPDATED_AT = null; // No 'updated_at' column in the table
}