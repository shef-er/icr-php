<?php
declare(strict_types=1);

namespace App\Action;

use App\Action;
use Psr\Http\Message\ResponseInterface as Response;

class MainAction extends Action
{
    /**
     * @return Response
     */
    protected function action(): Response
    {
        $file = file_get_contents(ROOT_DIR . '/public/index.html');
        return $this->respondWithContent($file);
    }
}
