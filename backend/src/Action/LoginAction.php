<?php
declare(strict_types=1);

namespace App\Action;

use App\Action;
use App\Domain\Entity\User;
use App\Domain\Mapper\UserMapper;
use App\GoogleClient;
use App\Session;
use DateTime;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Psr\Http\Message\ResponseInterface as Response;

class LoginAction extends Action
{
    protected function action(): Response
    {
        $get = $this->request->getQueryParams();
        $post = $this->request->getParsedBody();

        // for testing purpose
        // return $this->respondWithData($post ?? []);

        $uri = $this->request->getUri();

        /**
         * @var GoogleClient $google_client
         */
        $google_client = $this->container->get(GoogleClient::class);

        // $redirect_uri = $base_uri . $uri->getPath();
        // $google_client->setRedirectUri($redirect_uri);

        if(isset($get['code'])) {
            $id_token = $google_client->fetchIdTokenWithAuthCode($get['code']);

            // for testing purpose
            // return $this->respondWithData($id_token);

            $data = $google_client->fetchDataWithIdToken($id_token);

            // for testing purpose
            // return $this->respondWithData($data);

            $mapper = new UserMapper();
            $user = $mapper->findByGoogleUid($data['google_uid']);

            if(empty($user)) {
                $user = new User();
                $user->setFullName($data['full_name']);
                $user->setGoogleUid($data['google_uid']);
                $user->setAvatar($data['avatar']);
                $user->addEmail($data['email']);

                $mapper->create($user);
            }

            Session::set('id_token', $id_token);
            Session::set('user_id', $user->getId());

        } elseif(isset($post['tokenId'])) {
            $id_token = $post['tokenId'];

            $data = $google_client->fetchDataWithIdToken($id_token);

            // for testing purpose
            // return $this->respondWithData($data);

            $mapper = new UserMapper();
            $user = $mapper->findByGoogleUid($data['google_uid']);

            if(empty($user)) {
                $user = new User();
                $user->setFullName($data['full_name']);
                $user->setGoogleUid($data['google_uid']);
                $user->setAvatar($data['avatar']);
                $user->addEmail($data['email']);

                $mapper->create($user);
            }

            $api_token = bin2hex(random_bytes(64));
            
            $timestamp = new DateTime();
            $expiry = $timestamp
                ->add(date_interval_create_from_date_string('7 days'))
                ->format('Y-m-d H:i:s');

            /**
             * @var QueryBuilder $db
             */
            $db = $this->container->get(QueryBuilder::class);
            $db
            ->insert('api_tokens')
            ->values([
                'user_id'   => '?',
                'api_token' => '?',
                'expiry'    => '?'
            ])
            ->setParameter(0, $user->getId())
            ->setParameter(1, $api_token)
            ->setParameter(2, $expiry)
            ->execute();

            // Session::set('api_token', $api_token);
            // Session::set('user_id', $user->getId());

            $data['token'] = $api_token;

            return $this->respondWithData($data);
        } elseif(
            !Session::isset('user_id')
            || !Session::isset('id_token')
        ) {
            // return $this->respondWithData($oauth->createAuthUrl());
            return $this->redirect($google_client->createAuthUrl());
        }

        $base_uri = $uri->getScheme() .'://'. $uri->getHost();
        return $this->redirect($base_uri);
    }
}
