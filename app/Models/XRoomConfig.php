<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class XRoomConfig extends Model
{
    //
    public $table = 'xroom_configs';

    protected $primaryKey = 'code';
    public $incrementing = false;

    protected $hidden = [
        'created_at',
        'updated_at'
    ];
}