<?php
declare(strict_types=1);

namespace App\Exception;

use Exception;

class BadEventFormatException extends Exception
{
    public $message = "Wrong event format";
}
