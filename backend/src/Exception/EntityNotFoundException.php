<?php
declare(strict_types=1);

namespace App\Exception;

use Exception;

class EntityNotFoundException extends Exception
{
    public $message = "Entity not found";
}
