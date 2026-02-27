<?php 


namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'testimonials';

    /**
     * The name of the "created at" column.
     *
     * @var string
     */
    const CREATED_AT = 'created';

    /**
     * The name of the "updated at" column.
     *
     * @var string
     */
    const UPDATED_AT = 'modified';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'testimonial',
        'client_name',
        'company',
        'logo',
        'logo_thumb',
        'short_order',
        'status',
        'created_by',
        'modified_by',
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;
    
}

?>