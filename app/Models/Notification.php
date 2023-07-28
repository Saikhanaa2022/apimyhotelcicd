<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'notifications';

    protected $casts = [
        'data' => 'array',
     ];
    
    protected $fillable = [
        'id', 'type', 'notifiable_type', 'notifiable_id', 'data', 'is_fetched', 'read_at', 'created_at', 'updated_at'
    ];
}
