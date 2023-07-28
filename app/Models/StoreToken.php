<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreToken extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'store_tokens';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'access_token', 'expires_in', 'refresh_token', 'refresh_expires_in'
    ];
}
