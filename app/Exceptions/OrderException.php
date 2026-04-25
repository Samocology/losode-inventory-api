<?php

namespace App\Exceptions;

use Exception;

class OrderException extends Exception
{
    protected $code = 422;
}