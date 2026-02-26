<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebUser extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'web_users';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'uid';

    /**
     * The name of the "created at" column.
     *
     * @var string
     */
    const CREATED_AT = 'create_date';

    /**
     * The name of the "updated at" column.
     *
     * @var string
     */
    const UPDATED_AT = 'last_update_on';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'contact',
        'gender',
        'company',
        'billing_address',
        'address',
        'city',
        'state',
        'zip',
        'country',
        'about_me',
        'newsletter',
        'status',
        'user_type',
        'admin_approved',
        'password',
        'hash_code',
        'tnc_agreed',
        'ip',
        'region',
        'avatar',
        'avatar_thumb',
        'avatar_path',
        'activation_code',
        'activation_time',
        'activation_expire_time',
        'activation_link',
        'activation_status',
        'reset_req_id',
        'reset_time',
        'reset_expire_time',
        'last_login',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'salt',
        'hash_code',
        'reset_req_id',
    ];

    /**
     * Get the enrollments for the user.
     */
    public function enrollments()
    {
        return $this->hasMany('App\Models\Enrollment', 'uid');
    }
}
