<?php

namespace App\Exceptions;

use Exception;

class KhanbankException extends Exception
{
    //
    public function __construct(string $message, int $code)
    {
        parent::__construct($message, $code);
    }

}