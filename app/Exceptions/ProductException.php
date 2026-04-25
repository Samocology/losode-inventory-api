<?php

namespace App\Exceptions;

use Exception;

class ProductException extends Exception
{
    protected $code = 422;
}