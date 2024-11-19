<?php
declare(strict_types=1);

namespace App\Utility;

use Cake\Http\Client;
use Cake\Http\Exception\BadRequestException;

class SpotifyApi
{

    /**
     * Access Token used in requests
     * @var string
     */
    private string $_accessToken;

    /**
     * Refresh Token used to refresh Access Token
     * @var string
     */
    private string $_refreshToken;

    /**
     * Client object
     * @var \Cake\Http\Client
     */
    private $_httpClient;

    /**
     * Client ID
     * @var string
     */
    private string $_clientId;

    /**
     * Client Secret
     * @var string
     */
    private string $_clientSecret;

    /**
     * URI to redirect
     * @var string
     */
    private string $_redirectUri;

    /**
     * URL to account 
     * @var string
     */
    public const ACCOUNT_URL = "https://accounts.spotify.com";

    /**
     * URL to api
     * @var string
     */
    public const API_URL = "https://api.spotify.com";

    /**
     * Options used in request
     * @var array
     */
    private array $_options;

    /**
     * Headers used in request
     * @var array
     */
    private array $_headers;

    /**
     * Indicates if request should be retried
     * @var bool
     */
    private bool $_retry = false;

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
        
        $this->_options = [];
        $this->_headers = [];

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
     * @return void
     */
    public function requestAccessToken(string $code)
    {
        $data =
        [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $this->getRedirectUri(),
        ];

        $this->sendTokenRequest($data);
    }

    /**
     * Sends request to Token endpoint
     * @param array $data
     * @return void
     */
    private function sendTokenRequest($data)
    {
        $headers = [
            'Authorization' => 'Basic '.base64_encode($this->getClientId().':'.$this->getClientSecret()),
            'Content-Type' => 'application/x-www-form-urlencoded',
        ];

        $this->_addHeaders($headers);

        $response = $this->_request('POST',self::ACCOUNT_URL.'/api/token', $data);

        var_dump($response);

        $this->_setAccessToken($response['access_token']);
        $this->_setRefreshToken($response['refresh_token']);
    }

    /**
     * Refreshes token from Spotify API
     * 
     * @return void
     */
    private function _refreshTokens()
    {
        $data =
        [
            'grant_type' => 'refresh_token',
            'refresh_token' => $this->getRefreshToken(),
        ];

        $this->sendTokenRequest($data);
    }

    /**
     * Get the current user's top artists or tracks based on calculated affinity.
     * @param string $type The type of entity to return. Valid values: artists or tracks
     * @param string $time_range Over what time frame the affinities are computed. Valid values: long_term (calculated from ~1 year of data and including all new data as it becomes available), medium_term (approximately last 6 months), short_term (approximately last 4 weeks). Default: medium_term
     * @param int $limit The maximum number of items to return. Default: 20. Minimum: 1. Maximum: 50.
     * @param int $offset The index of the first item to return. Default: 0 (the first item). Use with limit to get the next set of items.
     * @return array
     */
    public function getTop(string $type, string $time_range = 'medium_term', int $limit = 20, int $offset = 0) :array
    {
        if(!in_array($type, ['artists', 'tracks']))
            return null;

        return $this->_request('GET', self::API_URL.'/v1/me/top/'.$type, ['time_range' => $time_range, 'limit' => $limit, 'offset' => $offset]);
    }

    /**
     * Get user profile
     * 
     * @return array
     */
    public function getProfile()
    {
        return $this->_request('GET', self::API_URL.'/v1/me');
    }

    /**
     * Get currently playing song
     * 
     * @return array
     */
    public function getCurrentlyPlaying()
    {
        return $this->_request('GET', self::API_URL.'/v1/me/player/currently-playing');
    }

    /**
     * Checks wheter tokens are set
     * @return bool
     */
    public function checkTokens()
    {
        return isset($this->_accessToken) && isset($this->_refreshToken);
    }

    /**
     * Sets tokens from user data
     * @param string $accessToken
     * @param string $refreshToken
     * @return void
     */
    public function setTokensOfUser(string $accessToken, string $refreshToken)
    {
        $this->_setAccessToken($accessToken);
        $this->_setRefreshToken($refreshToken);
    }

    /**
     * Handles requests
     * @param string $method
     * @param string $url
     * @param array $data
     * @return mixed
     */
    private function _request(string $method, string $url, array $data = [])
    {
        try{
            $result = $this->_send($method, $url, $data);
        }
        catch(\Exception $e){
            if($this->shouldRetry())
                $result = $this->_send($method, $url, $data);

            throw $e;
        }

        $this->_clearOptions();

        if($result->getStatusCode() === 200)
            return $result->getJson();
    }

    private function _clearOptions()
    {
        $this->_options = [];
        $this->_headers = [];
    }

    /**
     * Returns if request should be retried and changes state if needed
     * @return bool
     */
    public function shouldRetry()
    {
        if($this->_retry)
        {
            $this->_retry = false;
            return true;
        }
        return false;
    }

    /**
     * Send request to the Spotify API
     * 
     * @param string $method GET/POST
     * @param string $url URL to the endpoint after API_URL
     * @param array $data Optional. Data to send with request
     * 
     * @return \Cake\Http\Client\Response
     */
    private function _send(string $method, string $url, array $data = [])
    {
        $this->_setOptions();

        $response = match(strtoupper($method))
        {
            'GET' => $this->getClient()->get($url, $data, $this->_options),
            'POST' => $this->getClient()->post($url, $data, $this->_options),
        };

        if($response->getStatusCode() >= 400)
            $this->_handleError($response);

        return $response;
    }

    /**
     * Prepares options to send with API
     * @return void
     */
    private function _setOptions()
    {
        if(!isset($this->_options['headers']) && empty($this->_headers))
            $this->_addHeaders(['Authorization' => 'Bearer '.$this->getAccessToken()]);

        $this->_options['headers'] = $this->_headers;
    }

    /**
     * Adds options which will be used in request to API
     * @param array $option
     * @return void
     */
    private function _addOptions(array $option)
    {
        $this->_options = array_merge($this->_options, $option);
    }

    /**
     * Add headers which will be used in request to API
     * @param array $headers
     * @return void
     */
    private function _addHeaders(array $headers)
    {
        $this->_headers = array_merge($this->_headers, $headers);
    }

    /**
     * Summary of _handleError
     * @param \Cake\Http\Client\Response $response
     * @return void
     */
    private function _handleError(\Cake\Http\Client\Response $response)
    {
        if($response->getStatusCode() === 401)
        {
            if($this->_refreshTokens())
                $this->retry();
            else
                throw new \Exception('Access revoked. Please login');
        }
        else
            throw new \Exception($response->getStatusCode().' - '.$response->getReasonPhrase());
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

    public function retry()
    {
        $this->_retry = true;
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