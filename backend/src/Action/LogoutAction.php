<?php
declare(strict_types=1);

namespace App\Action;

use App\Action;
use App\Session;
use Psr\Http\Message\ResponseInterface as Response;

class LogoutAction extends Action
{
    /**
     * @return Response
     */
    protected function action(): Response
    {
        Session::unset('id_token', 'user_id');

        return $this->respondWithData('You logged out');
    }
}
