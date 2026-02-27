<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoreTeam extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'core_team';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'intro',
        'image',
        'status',
        'display_order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'status' => 'boolean',
        'display_order' => 'integer',
    ];

    const CREATED_AT = 'create_date';
    const UPDATED_AT = null; // 'updated_at' is not in the table
}