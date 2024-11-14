<?php
declare(strict_types=1);

namespace App\Utility;

use Cake\Http\Client;

class SpotifyApi
{

    private $_accessToken;

    private $_refreshToken;
    private $_httpClient;

    private $_clientId;

    private $_clientSecret;

    private $_redirectUri;

    public const ACCOUNT_URL = "https://accounts.spotify.com";

    public const API_URL = "https://api.spotify.com";

    /**
     * Constructor
     * 
     * @param string $clientId
     * @param string $clientSecret
     * @param string $redirectUri
     */
    public function __construct(string $clientId, string $clientSecret, string $redirectUri)
    {
        $this->_clientId = $clientId;
        $this->_clientSecret = $clientSecret;
        $this->_redirectUri = $redirectUri;
        $this->_httpClient = new Client();
    }

    /**
     * Build Authorization URL
     * 
     * @param array $options Optional. Set options
     * - scope - Set one or more scopes to request from user. Optional - If no scopes then grant access to only public information
     * - state - CSRF Token. Optional
     * - show_dialog - Whether or not to force the user to approve the app again if they’ve already done so. Optional - Default false
     * 
     * @return string Authorization URL
     */
    public function getAuthorizeUrl(array $options = [])
    {
        $parameters = 
        [
            'response_type' => 'code',
            'client_id' => $this->getClientId(),
            'redirect_uri' => $this->getRedirectUri(),
            'scope' => isset($options['scope']) ? implode(' ', $options['scope']) : null,
            'state' => $options['state'] ?? null,
            'show_dialog' => !empty($options['show_dialog']) ? 'true' : null,
        ];

        return $this->getClient()->buildUrl(self::ACCOUNT_URL.'/authorize', $parameters);
    }

    /**
     * Requests Access Token and other data from Spotify API
     * 
     * @param string $code Authorization Code returned after successful log in through Authorization URL
     * 
     * @return bool
     */
    public function requestAccessToken(string $code)
    {
        $data =
        [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $this->getRedirectUri(),
        ];

        $options =
        [
            'headers' => 
            [
                'Authorization' => 'Basic '.base64_encode($this->getClientId().':'.$this->getClientSecret()),
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
        ];

        $response = $this->getClient()->post(self::ACCOUNT_URL.'/api/token', $data, $options);

        if($response->getStatusCode() === 200)
        {
            $this->_setAccessToken($response->getJson()['access_token']);
            $this->_setRefreshToken($response->getJson()['refresh_token']);
            return true;
        }

        return false;
    }

    public function getProfile()
    {
        return $this->getRequest('/v1/me')->getJson();
    }

    /**
     * Do a GET request to the Spotify API
     * 
     * @param string $url URL to the endpoint after API_URL
     * 
     * @return \Cake\Http\Client\Response
     */
    public function getRequest($url)
    {
        $options = 
        [
            'headers' =>
            [
                'Authorization' => 'Bearer '.$this->getAccessToken(),
            ],
        ];

        return $this->getClient()->get(self::API_URL.$url, [], $options);
    }

    /**
     * Set Access Token
     * 
     * @param string $accessToken
     * 
     * @return void
     */
    private function _setAccessToken($accessToken)
    {
        $this->_accessToken = $accessToken;
    }

    /**
     * Get Access Token
     * 
     * @return string
     */
    public function getAccessToken()
    {
        return $this->_accessToken;
    }

    /**
     * Get Refresh Token
     * 
     * @return string
     */
    public function getRefreshToken()
    {
        return $this->_refreshToken;
    }

    /**
     * Set Refresh Token
     * 
     * @param string $refreshToken
     * 
     * @return void
     */
    private function _setRefreshToken($refreshToken)
    {
        $this->_refreshToken = $refreshToken;
    }

    /**
     * Get Client ID
     * 
     * @return string
     */
    public function getClientId()
    {
        return $this->_clientId;
    }

    /**
     * Get Redirect URI
     * 
     * @return string
     */
    public function getRedirectUri()
    {
        return $this->_redirectUri;
    }

    /**
     * Get HTTP Client
     * 
     * @return Client
     */
    public function getClient()
    {
        return $this->_httpClient;
    }

    /**
     * Get Client Secret
     * 
     * @return string
     */
    public function getClientSecret()
    {
        return $this->_clientSecret;
    }
}