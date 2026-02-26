<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DownloadFile extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'download_files';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'file_path',
        'display_order',
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;
}