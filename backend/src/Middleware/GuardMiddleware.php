<?php
declare(strict_types=1);

namespace App\Middleware;

use App\GoogleClient;
use App\Session;
use Psr\Container\ContainerInterface as Container;
use Psr\Http\Message\ResponseFactoryInterface as ResponseFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface as Middleware;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class GuardMiddleware implements Middleware
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function process(Request $request, RequestHandler $handler): Response
    {
        if (!Session::isset('user_id')) {
            $response_factory = $this->container->get(ResponseFactory::class);
            $response = $response_factory->createResponse();

            $client = $this->container->get(GoogleClient::class);
            $auth_url = $client->createAuthUrl();

            return $response->withHeader('Location', $auth_url);
        }

        return $handler->handle($request);
    }

    /*private function checkExcluded(Request $request): bool
    {
        if(empty($this->settings['exclude_path'])){
            return false;
        }

        $request_path = $request->getUri()->getPath();

        foreach ($this->settings['exclude_path'] as $rule) {
            $rule = mb_substr($rule, -1) === '/' ? $rule : $rule . '/';
            $match = (bool)preg_match($rule, $request_path);
            if($match) return true;
        }

        return false;
    }*/
}
