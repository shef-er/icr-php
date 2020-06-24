<?php
declare(strict_types=1);

namespace App\Exception;

use Exception;

class MissingIdEventFormatException extends Exception
{
    public $message = "Wrong event format: \"id\" not found in body section";
}
