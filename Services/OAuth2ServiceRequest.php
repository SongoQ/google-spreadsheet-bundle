<?php

namespace Wk\GoogleSpreadsheetBundle\Services;

use Google\Spreadsheet\DefaultServiceRequest;
use \Google_Client;
use \Google_Auth_OAuth2;

/**
 * Class OAuth2ServiceRequest
 * @package Wk\GoogleSpreadsheetBundle\Model
 */
class OAuth2ServiceRequest extends DefaultServiceRequest
{
    /**
     * @var Google_Client
     */
    private $client;

    /**
     * set google client
     */
    public function __construct()
    {
        $this->client = new Google_Client();

        parent::__construct('');
    }

    /**
     * @param Google_Client $client
     *
     * @return $this
     */
    public function setClient(Google_Client $client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * @param string $serviceAccountJsonFile
     * @param string $scope
     */
    public function setCredentials($serviceAccountJsonFile, $scope)
    {
        $credentials = $this->client->loadServiceAccountJson($serviceAccountJsonFile, [$scope]);
        $this->client->setAssertionCredentials($credentials);
    }

    /**
     * @return string|null
     */
    public function refreshExpiredToken()
    {
        /** @var Google_Auth_OAuth2 $auth */
        $auth = $this->client->getAuth();

        if ($auth->isAccessTokenExpired()) {
            $auth->refreshTokenWithAssertion();
        }

        $accessTokenArray = json_decode($auth->getAccessToken(), true);

        return ($accessTokenArray && isset($accessTokenArray['access_token'])) ? $accessTokenArray['access_token'] : null;
    }

    /**
     * {@inheritdoc}
     */
    protected function initRequest($url, $requestHeaders = [])
    {
        $this->accessToken = $this->refreshExpiredToken();

        return parent::initRequest($url, $requestHeaders);
    }
}