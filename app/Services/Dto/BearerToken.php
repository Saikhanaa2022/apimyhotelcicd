<?php
namespace App\Services\Dto;

use Carbon\Carbon;

class BearerToken
{

    private $token;
    private $expired_date;
    public function __construct($token, $expires_in)
    {
        $this->token = $token;
        $this->expired_date = Carbon::now()->addSeconds($expires_in);
    }

    public function isTokenExpired()
    {
        $now = Carbon::now();
        return $this->expired_date < $now;
    }

    public function getToken()
    {
        return $this->token;
    }
}