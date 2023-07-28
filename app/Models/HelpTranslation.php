<?php

namespace App\Models;

class HelpTranslation extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'help_translations';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'content',
    ];

    /**
     * The attributes that are searchable.
     *
     * @var array
     */
    protected $searchable = [
        'name', 'content',
    ];

    public function help() {
        return $this->hasOne('App\Model\Help');
    }
}
