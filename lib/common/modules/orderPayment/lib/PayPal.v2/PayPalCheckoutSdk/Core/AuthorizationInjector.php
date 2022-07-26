<?php

namespace PayPalCheckoutSdk\Core;

use PayPalHttp\HttpRequest;
use PayPalHttp\Injector;
use PayPalHttp\HttpClient;

class AuthorizationInjector implements Injector
{
    private $client;
    private $environment;
    private $refreshToken;
    /** @var AccessToken */
    public $accessToken;
    
    /**
     * Instance of cipher used to encrypt/decrypt data while storing in cache.
     *
     * @var Cipher
     */
    private $cipher;
    private $clientId;


    public function __construct(HttpClient $client, PayPalEnvironment $environment, $refreshToken)
    {
        $this->client = $client;
        $this->environment = $environment;
        $this->refreshToken = $refreshToken;
// TL cache add
        $this->cipher = new \PayPal\Security\Cipher($this->environment->getClientSecret());
        $this->clientId = $this->environment->getClientId();
// TL cache add end
    }

    public function inject($request)
    {
        if (!$this->hasAuthHeader($request) && !$this->isAuthRequest($request))
        {
            // TL cache add
            $config = \PayPal\Core\PayPalConfigManager::getInstance();
            $token = \PayPal\Cache\AuthorizationCache::pull($config, $this->environment->getClientId());
            if ($token) {
            // We found it in cache
                $this->accessToken = new AccessToken($this->cipher->decrypt($token['accessTokenEncrypted']), $token['tokenType']??'Bearer', $token['tokenExpiresIn'], $token['tokenCreateTime']);
            }
            // TL cache add end

            if (is_null($this->accessToken) || $this->accessToken->isExpired())
            {
                $this->accessToken = $this->fetchAccessToken();
                // TL cache add
                \PayPal\Cache\AuthorizationCache::push($config, $this->clientId, $this->cipher->encrypt($this->accessToken->token), $this->accessToken->getCreateDate(), $this->accessToken->expiresIn);
                // TL cache add end
            }
            $request->headers['Authorization'] = 'Bearer ' . $this->accessToken->token;
        }
    }

    private function fetchAccessToken()
    {
        $accessTokenResponse = $this->client->execute(new AccessTokenRequest($this->environment, $this->refreshToken));
        $accessToken = $accessTokenResponse->result;
        return new AccessToken($accessToken->access_token, $accessToken->token_type, $accessToken->expires_in);
    }

    private function isAuthRequest($request)
    {
        return $request instanceof AccessTokenRequest || $request instanceof RefreshTokenRequest;
    }

    private function hasAuthHeader(HttpRequest $request)
    {
        return array_key_exists("Authorization", $request->headers);
    }
}
