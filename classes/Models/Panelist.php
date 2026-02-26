<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Panelist extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'panelists';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'intro',
        'image',
        'display_order',
        'status',
        'create_date'
    ];
}