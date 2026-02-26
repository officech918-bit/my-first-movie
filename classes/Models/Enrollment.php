<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'enrollments';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'uid',
        'fee',
        'explanation',
        'no_of_files',
        'dt',
        'status',
        'season_id',
        'category_id',
    ];  

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo('App\Models\WebUser', 'uid', 'uid');
    }
}
