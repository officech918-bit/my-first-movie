<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CcavResponse extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ccav_resp';

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
    public $timestamps = false; // Assuming no created_at/updated_at columns

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uid',
        'order_id',
        'title',
        'amount',
        'billing_name',
        'billing_address',
        'billing_city',
        'billing_state',
        'billing_zip',
        'billing_country',
        'billing_tel',
        'billing_email',
        'billing_ip',
        'status', // This is the order_status from CCAvenue
        'msg',
        'dt',
        'act', // This is the payment status (0 or 1)
    ];

    /**
     * Get the user that owns the CCAvenue response.
     */
    public function user()
    {
        return $this->belongsTo('App\Models\WebUser', 'uid', 'uid');
    }
}