<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'orders';

    protected $fillable = [
        'user_id',
        'amount',
        'order_status',
        'date',
    ];

    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo(WebUser::class, 'user_id', 'uid');
    }
}
