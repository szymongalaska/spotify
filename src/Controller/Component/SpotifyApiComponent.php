<?php
declare(strict_types=1);

namespace App\Controller\Component;

use Cake\Controller\Component;
use Psr\Http\Client\ClientInterface;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\ForbiddenException;
use Cake\Http\Exception\HttpException;
use Cake\Http\Exception\ServiceUnavailableException;
use Cake\Http\Exception\UnauthorizedException;
use InvalidArgumentException;

/**
 * Connect to Spotify API
 * @psalm-property array{clientId:string, clientSecret:string, redirectUri:string, client:ClientInterface, useMarket:bool} $_config
 */
class SpotifyApiComponent extends Component
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
     * @var \Psr\Http\Client\ClientInterface;
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
     * Counter of request retries
     * @var int
     */
    private $_retryCount = 0;

    /**
     * Number of max request retries
     * @var int
     */
    private const MAX_RETRY_NUMBER = 3;

    private bool $_useMarket = false;

    private string $market = 'from_token';

    /**
     * Constructor
     * 
     * @param array $config
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->_clientId = $this->getConfig('clientId');
        $this->_clientSecret = $this->getConfig('clientSecret');
        $this->_redirectUri = $this->getConfig('redirectUri');

        if ($this->getConfig('useMarket') == true)
            $this->_useMarket = true;

        $this->_options = [];
        $this->_headers = [];

        $this->_httpClient = $this->getConfig('client');
    }

    /**
     * Set the market property
     * 
     * @param bool $useMarket
     * 
     * @return void
     */
    public function setMarket(bool $useMarket): void
    {
        $this->_useMarket = $useMarket;
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
    public function getAuthorizeUrl(array $options = []): string
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

        return $this->getClient()->buildUrl(self::ACCOUNT_URL . '/authorize', $parameters);
    }

    /**
     * Requests Access Token and other data from Spotify API
     *
     * @param string $code Authorization Code returned after successful log in through Authorization URL
     *
     * @return array|null
     */
    protected function _requestAccessToken(string $code): array|null
    {
        $data =
            [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => $this->getRedirectUri(),
            ];

        return $this->_getTokens($data);
    }

    /**
     * Set tokens with code from Authorization URL
     * 
     * @param string $code Authorization Code returned after successful log in through Authorization URL
     * 
     * @return bool
     */
    public function setTokensByCode(string $code): bool
    {
        $tokens = $this->_requestAccessToken($code);
        return $this->setTokens($tokens);
    }

    /**
     * Request Tokens from Spotify API and set them
     * @param array{access_token:string, refresh_token:string} Array containing tokens to set
     * @return bool
     */
    public function setTokens(array $tokens): bool
    {
        if (!empty($tokens['access_token'])) {
            $this->_setAccessToken($tokens['access_token']);

            if (isset($tokens['refresh_token']))
                $this->_setRefreshToken($tokens['refresh_token']);

            return true;
        } else
            return false;
    }

    /**
     * Sends request to Token endpoint
     *
     *  @param array $data
     *
     * @return array|null
     */
    protected function _getTokens(array $data): array|null
    {
        $headers = [
            'Authorization' => 'Basic ' . base64_encode($this->getClientId() . ':' . $this->getClientSecret()),
            'Content-Type' => 'application/x-www-form-urlencoded',
        ];

        $this->_addHeaders($headers);

        return $this->request('POST', self::ACCOUNT_URL . '/api/token', $data);
    }

    /**
     * Refreshes token from Spotify API
     *
     * @return bool
     */
    public function refreshTokens(): bool
    {
        $data =
        [
            'grant_type' => 'refresh_token',
            'refresh_token' => $this->getRefreshToken(),
        ];

        $tokens = $this->_getTokens($data);

        return $this->setTokens($tokens);
    }

    /**
     * Get the current user's top artists or tracks based on calculated affinity.
     *
     * @param string $type The type of entity to return. Valid values: artists or tracks
     * @param string $time_range Over what time frame the affinities are computed. Valid values: long_term (calculated from ~1 year of data and including all new data as it becomes available), medium_term (approximately last 6 months), short_term (approximately last 4 weeks). Default: medium_term
     * @param int $limit The maximum number of items to return. Default: 20. Minimum: 1. Maximum: 50.
     * @param int $offset The index of the first item to return. Default: 0 (the first item). Use with limit to get the next set of items.
     *
     * @throws InvalidArgumentException Thrown when unsupported type
     *
     * @return array
     */
    public function getTop(string $type, string $time_range = 'medium_term', int $limit = 20, int $offset = 0): array
    {
        if (!in_array($type, ['artists', 'tracks']))
            throw new InvalidArgumentException('Not supported type.');

        return $this->request('GET', self::API_URL . '/v1/me/top/' . $type, ['time_range' => $time_range, 'limit' => $limit, 'offset' => $offset]);
    }

    /**
     * Get user profile
     *
     * @return array
     */
    public function getProfile()
    {
        return $this->request('GET', self::API_URL . '/v1/me');
    }

    /**
     * Get currently playing song
     *
     * @return array
     */
    public function getCurrentlyPlaying()
    {
        return $this->request('GET', self::API_URL . '/v1/me/player/currently-playing');
    }

    /**
     * Get a list of all songs saved in the current Spotify user's 'Your Music' library.
     *
     * @return array
     */
    public function getAllSavedTracks()
    {

        return $this->_batchRetrieveData(self::API_URL . '/v1/me/tracks?limit=50');
    }

    /**
     * Get a list of the songs saved in the current Spotify user's 'Your Music' library.
     *
     * @param int $limit The maximum number of items to return. Default: 20. Minimum: 1. Maximum: 50.
     * @param mixed $offset The index of the first item to return. Default: 0 (the first item). Use with limit to get the next set of items.
     *
     * @return array
     */
    public function getSavedTracks(int $limit = 20, int $offset = 0)
    {
        return $this->request('GET', self::API_URL . '/v1/me/tracks', ['limit' => $limit, 'offset' => $offset]);
    }

    /**
     * Get a list of the playlists owned or followed by the current Spotify user.
     *
     * @param int $limit The maximum number of items to return. Default: 20. Minimum: 1. Maximum: 50.
     * @param int $offset The index of the first playlist to return. Default: 0 (the first object). Maximum offset: 100.000. Use with limit to get the next set of playlists.'
     *
     * @return array
     */
    public function getPlaylists(int $limit = 20, int $offset = 0)
    {
        return $this->request('GET', self::API_URL . '/v1/me/playlists', ['limit' => $limit, 'offset' => $offset]);
    }

    /**
     * Get a list of all playlists owned or followed by the current Spotify user.
     *
     * @return array
     */
    public function getAllPlaylists()
    {
        $playlists = $this->_batchRetrieveData(self::API_URL . '/v1/me/playlists?limit=50');

        return array_filter($playlists);
    }

    /**
     * Get a list of all playlists owned by the current Spotify user.
     *
     * @param string $ownerId Need to compare with playlist owner ID
     *
     * @return array
     */
    public function getOwnedPlaylists(string $ownerId)
    {
        $playlists = $this->getAllPlaylists();

        return array_filter($playlists, function ($playlist) use ($ownerId) {
            if ($playlist['owner']['id'] == $ownerId)
                return $playlist;
        });
    }

    /**
     * Batch retrieve all data from any given URL which returns 'next' url (eg. Playlists)
     *
     * @param string $url URL used in request
     * @param string $method Method used in request. GET or POST. Default GET
     * @param string $path Dot-notated path
     *
     * @return array
     */
    protected function _batchRetrieveData(string $url, string $method = 'GET', string $path = '')
    {
        $items = [];

        do {
            $response = $this->request($method, $url);

            if ($path !== '')
                $response = $this->_getValueByPath($response, $path);

            $url = $response['next'];
            $items = array_merge($items, $response['items']);
        }
        while (!empty($response['next']));

        return $items;
    }

    /**
     * Helper function to retrieve a value from a nested array using a dot-notated path.
     *
     * @param array $array Source array
     * @param string $path Dot-notated path (e.g. 'tracks.items')
     *
     * @return mixed
     */
    private function _getValueByPath(array $array, string $path)
    {
        $keys = explode('.', $path);
        foreach ($keys as $key) {
            if (!is_array($array) || !array_key_exists($key, $array)) {
                return null;
            }
            $array = $array[$key];
        }
        return $array;
    }

    /**
     * Checks wheter tokens are set
     *
     * @return bool
     */
    public function checkTokens()
    {
        return isset($this->_accessToken) && isset($this->_refreshToken);
    }

    /**
     * Handles requests
     *
     * @param string $method
     * @param string $url
     * @param array|string $data
     *
     * @return array|null
     */
    public function request(string $method, string $url, array|string $data = [])
    {
        do {
            try {
                $result = $this->_send($method, $url, $data);
            } catch (\Exception $e) {
                if (!$this->shouldRetry())
                    throw $e;
            }
            ;
        }
        while ($this->shouldRetry());

        $this->_clearOptions();

        if ($result->getStatusCode() === 200)
            return $result->getJson();
        else
            return null;
    }

    /**
     * Clears options and headers param
     *
     * @return void
     */
    private function _clearOptions()
    {
        $this->_options = [];
        $this->_headers = [];
    }

    /**
     * Returns if request should be retried and changes state if needed
     *
     * @return bool
     */
    public function shouldRetry()
    {
        if ($this->_retry) {
            $this->_retry = false;
            return true;
        }
        return false;
    }

    private function _addMarket(array|string $data)
    {
        if ($this->_useMarket == false)
            return $data;

        if (is_string($data))
            $data .= '&market=' . $this->market;
        else
            $data['market'] = $this->market;

        return $data;
    }

    /**
     * Send request to the Spotify API
     *
     * @param string $method GET/POST
     * @param string $url URL to the endpoint after API_URL
     * @param array|string $data Optional. Data to send with request
     *
     * @return \Cake\Http\Client\Response
     */
    private function _send(string $method, string $url, array|string $data = [])
    {
        $this->_setOptions();

        if (preg_match('/\/tracks/', $url))
            $data = $this->_addMarket($data);

        $response = match (strtoupper($method)) {
            'GET' => $this->getClient()->get($url, $data, $this->_options),
            'POST' => $this->getClient()->post($url, $data, $this->_options),
            'DELETE' => $this->getClient()->delete($url, $data, $this->_options),
        };

        if ($response->getStatusCode() >= 400)
            $this->_handleError($response);

        return $response;
    }

    /**
     * Prepares options to send with API
     *
     * @return void
     */
    private function _setOptions()
    {
        if (!isset($this->_options['headers']) && empty($this->_headers))
            $this->_addHeaders(['Authorization' => 'Bearer ' . $this->getAccessToken()]);

        $this->_options['headers'] = $this->_headers;
    }

    /**
     * Adds options which will be used in request to API
     *
     * @param array $option
     *
     * @return void
     */
    private function _addOptions(array $option)
    {
        $this->_options = array_merge($this->_options, $option);
    }

    /**
     * Add headers which will be used in request to API
     *
     * @param array $headers
     *
     * @return void
     */
    private function _addHeaders(array $headers)
    {
        $this->_headers = array_merge($this->_headers, $headers);
    }


    /**
     *
     * Handles error based on the response status code
     *
     * @param \Cake\Http\Client\Response $response
     *
     * @throws \Cake\Http\Exception\UnauthorizedException
     * @throws \Cake\Http\Exception\HttpException
     * @throws \Cake\Http\Exception\ServiceUnavailableException
     * @throws \Cake\Http\Exception\BadRequestException
     *
     * @return void
     */
    private function _handleError(\Cake\Http\Client\Response $response)
    {
        $exception = null;

        switch ($response->getStatusCode()) {
            case 401:
                if ($this->refreshTokens())
                    $this->retry();
                else
                    $exception = new UnauthorizedException(__('Access revoked. Please login.'));
                break;
            case 429:
                $sleep = (int) $response->getHeader('retry-after')[0];

                if ($sleep >= 30) {
                    $exception = new HttpException(__("Too many requests. Wait for {0} seconds.", $sleep), 429);
                    break;
                }

                sleep($sleep);
                $this->retry();
                break;
            case 503:
                $exception = new ServiceUnavailableException(__('Spotify API is currently unavailable. Try again later.'));
                break;
            case 400:
                $exception = new BadRequestException();
                break;
            case 403:
                $exception = new ForbiddenException(__('Scope not authorized.'));
                break;
            default:
                $exception = new HttpException($response->getStatusCode() . ' - ' . $response->getReasonPhrase(), $response->getStatusCode());
                break;
        }

        if ($exception)
            throw $exception;
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
     * Sets retry value and increments counter of retries. Throws exception when limit of retries reached
     *
     * @throws \Exception
     *
     * @return void
     */
    public function retry()
    {
        $this->_retry = true;
        $this->_retryCount++;

        if ($this->_retryCount > self::MAX_RETRY_NUMBER) {
            $this->_retry = false;
            throw new \Exception('Max request retries reached.');
        }
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
     * @return ClientInterface
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

    /**
     * Get a playlist owned by a Spotify user.
     *
     * @param string $playlistId The Spotify ID of the playlist.
     * @param string $fields Optional. Filters for the query: a comma-separated list of the fields to return. If omitted, all fields are returned. For example, to get just the playlist''s description and URI: fields=description,uri. A dot separator can be used to specify non-reoccurring fields, while parentheses can be used to specify reoccurring fields within objects. For example, to get just the added date and user ID of the adder: fields=tracks.items(added_at,added_by.id). Use multiple parentheses to drill down into nested objects, for example: fields=tracks.items(track(name,href,album(name,href))). Fields can be excluded by prefixing them with an exclamation mark, for example: fields=tracks.items(track(name,href,album(!name,href)))
     *
     * @return array
     */
    public function getPlaylist(string $playlistId, string $fields = '')
    {
        $options = $fields !== '' ? ['fields' => $fields] : [];

        return $this->request('GET', self::API_URL . '/v1/playlists/' . $playlistId, $options);
    }

    /**
     * Get snapshot ID of a playlist.
     *
     * @param string $playlistId The Spotify ID of the playlist.
     *
     * @return string
     */
    public function getPlaylistSnapshotId(string $playlistId)
    {
        return $this->getPlaylist($playlistId, 'snapshot_id')['snapshot_id'];
    }

    /**
     * Get full details of the items of a playlist owned by a Spotify user.
     *
     * @param string $playlistId The Spotify ID of the playlist.
     * @param string $fields Optional. Filters for the query: a comma-separated list of the fields to return. If omitted, all fields are returned. For example, to get just the playlist''s description and URI: fields=description,uri. A dot separator can be used to specify non-reoccurring fields, while parentheses can be used to specify reoccurring fields within objects. For example, to get just the added date and user ID of the adder: fields=tracks.items(added_at,added_by.id). Use multiple parentheses to drill down into nested objects, for example: fields=tracks.items(track(name,href,album(name,href))). Fields can be excluded by prefixing them with an exclamation mark, for example: fields=tracks.items(track(name,href,album(!name,href)))
     *
     * @return array
     */
    public function getPlaylistTracks(string $playlistId, string $fields = '')
    {
        $options = $fields !== '' ? ['fields' => 'next, items(' . $fields . ')'] : [];

        $url = $this->getClient()->buildUrl(self::API_URL . '/v1/playlists/' . $playlistId . '/tracks', $options);

        return $this->_batchRetrieveData($url, 'GET');
    }

    /**
     * Modify playlist according to method parameter
     *
     * @param string $method HTTP method (POST for add, DELETE for remove)
     * @param string $playlistId The Spotify ID of the playlist.
     * @param array $tracks Array of track URIs
     *
     * @param array $options Optional. Can be:
     *
     * - prepend - Prepends tracks to the playlist
     *
     * @return bool|null
     *@throws InvalidArgumentException|\Exception
     *
     */
    private function _modifyPlaylistTracks(string $method, string $playlistId, array $tracks, array $options = [])
    {
        if (empty($tracks))
            throw new InvalidArgumentException('Tracks array cannot be empty.');

        $this->_addOptions(['type' => 'json']);

        if ($method === 'DELETE') {

            $tracks = array_map(function ($track) {
                return ['uri' => $track];
            }, $tracks);

            $snapshot_id = $this->getPlaylistSnapshotId($playlistId);
        }

        $tracks = array_chunk($tracks, 100);


        foreach ($tracks as $data) {

            if ($method === 'DELETE')
                $data = [
                    'tracks' => $data,
                    'snapshot_id' => $snapshot_id,
                ];
            else
                $data = ['uris' => $data];

            if (isset($options['prepend']) && ($options['prepend']) === true)
                $data['position'] = 0;

            $this->request($method, self::API_URL . '/v1/playlists/' . $playlistId . '/tracks', json_encode($data));
        }


        return true;
    }

    /**
     * Add tracks to playlist
     *
     * @param string $playlistId The Spotify ID of the playlist.
     * @param array $tracks Array of track URIs
     * @param bool $prepend If set to true will prepend tracks to the playlist
     *
     * @return bool|null
     */
    public function addTracksToPlaylist(string $playlistId, array $tracks, bool $prepend = false)
    {
        $options['prepend'] = $prepend;
        return $this->_modifyPlaylistTracks('POST', $playlistId, $tracks, $options);
    }

    /**
     * Delete tracks from playlist
     *
     * @param string $playlistId The Spotify ID of the playlist.
     * @param array $tracks Array of track URIs
     *
     * @return bool|null
     */
    public function deleteTracksFromPlaylist(string $playlistId, array $tracks)
    {
        return $this->_modifyPlaylistTracks('DELETE', $playlistId, $tracks);
    }

    /**
     * Get Spotify catalog information for a single track identified by its unique Spotify ID.
     *
     * @param string $trackId The Spotify ID for the track.
     *
     * @return array
     */
    public function getTrack(string $trackId)
    {
        return $this->request('GET', self::API_URL . '/v1/tracks/' . $trackId);
    }

    /**
     * Get Spotify catalog information for multiple tracks based on their Spotify IDs.
     *
     * @param array $tracks Array of track IDs
     *
     * @return array
     */
    public function getMultipleTracks(array $tracks)
    {
        $tracks = array_chunk($tracks, 50);
        $result = [];

        foreach ($tracks as $chunk) {
            $result = array_merge($result, $this->request('GET', self::API_URL . '/v1/tracks/', ['ids' => implode(',', $chunk)])['tracks']);
        }

        return $result;
    }

    /**
     * Get the current user's least popular artist from top artists list
     *
     * @return array
     */
    public function getLeastPopularArtist()
    {
        $url = $this->getTop('artists', 'long_term', 50)['href'];
        $items = $this->_batchRetrieveData($url);

        usort($items, function ($a, $b) {
            return $a['popularity'] <=> $b['popularity'];
        });

        return $items[0];
    }

    /**
     * Get tracks from the current user's recently played tracks.
     *
     * @param int $limit The maximum number of items to return. Default: 20. Minimum: 1. Maximum: 50.
     * @param int|null $after A Unix timestamp in milliseconds. Returns all items after (but not including) this cursor position. If after is specified, before must not be specified.
     * @param int|null $before A Unix timestamp in milliseconds. Returns all items before (but not including) this cursor position. If before is specified, after must not be specified.
     *
     * @throws InvalidArgumentException
     *
     * @return array
     */
    public function getRecentlyPlayedTracks(int $limit = 20, ?int $after = null, ?int $before = null)
    {
        if ($after !== null && $before !== null)
            throw new InvalidArgumentException(__('One of cursor positions must be empty'));

        $data = [
            'limit' => $limit,
            'after' => $after,
            'before' => $before
        ];

        $url = $this->getClient()->buildUrl(self::API_URL . '/v1/me/player/recently-played', $data);

        return $this->_batchRetrieveData($url);
    }
}
