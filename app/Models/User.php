<?php

namespace App\Models;

use App\Traits\Searchable;
use App\Notifications\{ResetPassword, VerifyEmail};
use Illuminate\Notifications\Notifiable;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;

// implements MustVerifyEmail
class User extends Authenticatable 
{
    use Searchable, Notifiable, HasApiTokens;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users';
    
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_default' => 'boolean',
        'is_owner' => 'boolean',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $hidden = [
        'password'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'position', 'phone_number', 'email', 'password', 'is_default', 'is_owner', 'hotel_id', 'role_id', 'sys_role',
    ];
    
    /**
     * The attributes that are searchable.
     *
     * @var array
     */
    protected $searchable = [
        'name', 'position', 'phone_number', 'email',
    ];

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPassword($token));
    }

    /**
     * Send the verify email notification.
     *
     * @return void
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyEmail());
    }

    /**
     * Check user has permission.
     *
     * @param  string  $name
     * @return boolean
     */
    public function hasPermission($code)
    {
        return $this->role->permissions()
            ->where('code', $code)
            ->exists();
    }

    /**
     * Get the hotels that owns the user.
     */
    public function hotels()
    {
        return $this->belongsToMany('App\Models\Hotel')->withTimestamps();
    }

    /**
     * Get role that related to the user.
     */
    public function role()
    {
        return $this->belongsTo('App\Models\Role');
    }

    /**
     * Get all of the reservations for the user.
     */
    public function reservations()
    {
        return $this->hasManyThrough('App\Models\Reservation', 'App\Models\UserClone');
    }

    /**
     * Get all of the clones for the user.
     */
    public function userClones()
    {
        return $this->hasMany('App\Models\UserClone');
    }
}
