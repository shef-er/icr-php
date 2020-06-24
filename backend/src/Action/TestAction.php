<?php
declare(strict_types=1);

namespace App\Action;

use App\Action;
use App\Domain\Mapper\UserMapper;
use Psr\Http\Message\ResponseInterface as Response;

class TestAction extends Action
{
    /**
     * @return Response
     */
    protected function action(): Response
    {
        return $this->respondWithData( (new UserMapper)->findByGoogleUid('114742091814936181594') );
    }
}
