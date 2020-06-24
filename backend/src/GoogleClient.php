<?php
declare(strict_types=1);

namespace App;

use Google_Client;
use Exception;

class GoogleClient
{
    /**
     * @var Google_Client
     */
    protected $client;

    public function __construct(
        array $config,
        array $scopes,
        string $redirect_uri = null
    )
    {
        $this->client = new Google_Client($config);
        $this->client->setScopes($scopes);

        if(null !== $redirect_uri) {
            $this->client->setRedirectUri($redirect_uri);
        }
    }

    public function getClient()
    {
        return $this->client;
    }

    public function getRedirectUri()
    {
        return $this->client->getRedirectUri();
    }

    public function setRedirectUri(string $redirect_uri)
    {
        $this->client->setRedirectUri($redirect_uri);
    }

    public function fetchIdTokenWithAuthCode(string $auth_code): string
    {
        $token = $this->client->fetchAccessTokenWithAuthCode($auth_code);
        return $token['id_token'];
    }

    public function fetchDataWithIdToken(string $id_token = null): array
    {
        $token_payload = $this->client->verifyIdToken($id_token);

        return [
            'full_name'     => $token_payload['name'],
            'email'         => $token_payload['email'],
            'google_uid'    => $token_payload['sub'],
            'avatar'        => $token_payload['picture'],
            // 'token_payload' => $token_payload,
        ];
    }

    public function createAuthUrl() {
        return $this->client->createAuthUrl();
    }
}
