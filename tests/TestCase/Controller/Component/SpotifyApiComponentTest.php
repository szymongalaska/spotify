<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\Component;

use App\Controller\Component\SpotifyApiComponent;
use Cake\Http\Client\Response;
use Cake\TestSuite\TestCase;
use Cake\Http\TestSuite\HttpClientTrait;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\Controller\ComponentRegistry;
use InvalidArgumentException;
use ReflectionClass;

/**
 * SpotifyApiComponentTest Class
 */
class SpotifyApiComponentTest extends TestCase
{
    use HttpClientTrait;
    use IntegrationTestTrait;
    protected $SpotifyApi;

    protected $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createSpotifyMock();
    }

    protected function tearDown(): void
    {
        unset($this->SpotifyApi);
        unset($this->client);

        parent::tearDown();
    }

    /**
     * Helper function to retrieve fixture data from json files in SpotifyApi dir
     * @param string $filename Name of file to retrieve
     * 
     * @return mixed
     */
    private function getJsonFixture(string $filename)
    {
        return json_decode(file_get_contents(TESTS . 'Fixture' . DS . 'SpotifyApi' . DS . $filename . '.json'), true);
    }

    /**
     * Create SpotifyApiComponent mock
     * 
     * @param array $onlyMethods List of methods to mock
     * 
     * @param \Cake\Http\Client|null $httpClient Mock object of \Cake\Http\Client, if not passed default mock will be created
     * 
     * @return void
     */
    protected function createSpotifyMock(array $onlyMethods = [], \Cake\Http\Client|null $httpClient = null)
    {
        if ($httpClient == null)
            $httpClient = $this->createMock(\Cake\Http\Client::class);

        $config = [
            'clientId' => 'testClientId',
            'clientSecret' => 'testClientSecret',
            'redirectUri' => 'testRedirectUri',
            'client' => $httpClient,
            'useMarket' => null
        ];

        $this->SpotifyApi = $this->getMockBuilder(SpotifyApiComponent::class)
            ->setConstructorArgs([new ComponentRegistry(), $config])
            ->onlyMethods($onlyMethods)
            ->getMock();
    }

    /**
     * Test setMarket method
     * @return void
     
     */
    public function testSetMarket()
    {
        $this->SpotifyApi->setMarket(true);
        $reflection = new ReflectionClass(SpotifyApiComponent::class);
        $property = $reflection->getProperty('_useMarket');

        $this->assertTrue($property->getValue($this->SpotifyApi));

        $this->SpotifyApi->setMarket(false);
        $this->assertFalse($property->getValue($this->SpotifyApi));
    }


    /**
     * Test getAuthorizeUrl method without any parameters result should be a string
     * @return void
     */
    public function testGetAuthorizeUrlWithoutParams()
    {
        $client = $this->getMockBuilder(\Cake\Http\Client::class)
            ->onlyMethods(['buildUrl'])
            ->getMock();

        $expectedUrl = 'https://accounts.spotify.com/authorize';
        $expectedParameters = [
            'response_type' => 'code',
            'client_id' => 'testClientId',
            'redirect_uri' => 'testRedirectUri',
            'scope' => null,
            'state' => null,
            'show_dialog' => null
        ];
        $expectedReturn = 'https://accounts.spotify.com/authorize?response_type=code&client_id=testClientId&redirect_uri=testRedirectUri';
        $client->method('buildUrl')->with($expectedUrl, $expectedParameters, [])->willReturn($expectedReturn);

        $this->createSpotifyMock([], $client);
        $this->assertSame($expectedReturn, $this->SpotifyApi->getAuthorizeUrl());
    }
    /**
     * Test getAuthorizeUrl method with scope parameter result should be a string
     * @return void
     */
    public function testGetAuthorizeUrlOnlyScopeParam()
    {
        $client = $this->getMockBuilder(\Cake\Http\Client::class)
            ->onlyMethods(['buildUrl'])
            ->getMock();

        $options = ['scope' => ['testScope', 'testScope2']];
        $expectedUrl = 'https://accounts.spotify.com/authorize';
        $expectedParameters = [
            'response_type' => 'code',
            'client_id' => 'testClientId',
            'redirect_uri' => 'testRedirectUri',
            'scope' => 'testScope testScope2',
            'state' => null,
            'show_dialog' => null
        ];
        $expectedReturn = 'https://accounts.spotify.com/authorize?response_type=code&client_id=testClientId&redirect_uri=testRedirectUri&scope=testScope%20testScope2';

        $client->method('buildUrl')->with($expectedUrl, $expectedParameters, [])->willReturn($expectedReturn);

        $this->createSpotifyMock([], $client);
        $this->assertSame($expectedReturn, $this->SpotifyApi->getAuthorizeUrl($options));
    }

    /**
     * Test getAuthorizeUrl method with state parameter result should be a string
     * @return void
     */
    public function testGetAuthorizeUrlOnlyStateParam()
    {

        $client = $this->getMockBuilder(\Cake\Http\Client::class)
            ->onlyMethods(['buildUrl'])
            ->getMock();

        $options = ['state' => 'testState'];
        $expectedUrl = 'https://accounts.spotify.com/authorize';
        $expectedParameters = [
            'response_type' => 'code',
            'client_id' => 'testClientId',
            'redirect_uri' => 'testRedirectUri',
            'scope' => null,
            'state' => 'testState',
            'show_dialog' => null
        ];
        $expectedReturn = 'https://accounts.spotify.com/authorize?response_type=code&client_id=testClientId&redirect_uri=testRedirectUri&state=testState';

        $client->method('buildUrl')->with($expectedUrl, $expectedParameters, [])->willReturn($expectedReturn);

        $this->createSpotifyMock([], $client);
        $this->assertSame($expectedReturn, $this->SpotifyApi->getAuthorizeUrl($options));
    }

    /**
     * Test getAuthorizeUrl method with show_dialog parameter result should be a string
     * @return void
     */
    public function testGetAuthorizeUrlOnlyShowDialogParam()
    {
        $client = $this->getMockBuilder(\Cake\Http\Client::class)
            ->onlyMethods(['buildUrl'])
            ->getMock();

        $options = ['show_dialog' => 'test'];
        $expectedUrl = 'https://accounts.spotify.com/authorize';
        $expectedParameters = [
            'response_type' => 'code',
            'client_id' => 'testClientId',
            'redirect_uri' => 'testRedirectUri',
            'scope' => null,
            'state' => null,
            'show_dialog' => 'true'
        ];
        $expectedReturn = 'https://accounts.spotify.com/authorize?response_type=code&client_id=testClientId&redirect_uri=testRedirectUri&show_dialog=true';

        $client->method('buildUrl')->with($expectedUrl, $expectedParameters, [])->willReturn($expectedReturn);

        $this->createSpotifyMock([], $client);
        $this->assertSame($expectedReturn, $this->SpotifyApi->getAuthorizeUrl($options));
    }

    /**
     * Test getAuthorizeUrl method with all parameters result should be a string
     * @return void
     */
    public function testGetAuthorizeUrl()
    {
        $client = $this->getMockBuilder(\Cake\Http\Client::class)
            ->onlyMethods(['buildUrl'])
            ->getMock();

        $options = ['scope' => ['testScope', 'testScope2'], 'state' => 'testState', 'show_dialog' => 'test'];
        $expectedUrl = 'https://accounts.spotify.com/authorize';
        $expectedParameters = [
            'response_type' => 'code',
            'client_id' => 'testClientId',
            'redirect_uri' => 'testRedirectUri',
            'scope' => 'testScope testScope2',
            'state' => 'testState',
            'show_dialog' => 'true'
        ];
        $expectedReturn = 'https://accounts.spotify.com/authorize?response_type=code&client_id=testClientId&redirect_uri=testRedirectUri&scope=testScope%20testScope2&state=testState&show_dialog=true';

        $client->method('buildUrl')->with($expectedUrl, $expectedParameters, [])->willReturn($expectedReturn);

        $this->createSpotifyMock([], $client);
        $this->assertSame($expectedReturn, $this->SpotifyApi->getAuthorizeUrl($options));
    }

    /**
     * Test getTop method with not supported parameter it should throw exception
     * @return void
     */
    public function testGetTopFailsWithInvalidParams()
    {
        $this->expectException(InvalidArgumentException::class);

        $this->SpotifyApi->getTop('testType');
    }

    /**
     * Test getTop method result should be an array with `items` key
     * @return void
     */
    public function testGetTop()
    {
        $this->createSpotifyMock(['request']);

        $time_range = 'medium_term';
        $limit = 20;
        $offset = 0;

        $expectedUrl = 'https://api.spotify.com/v1/me/top/artists';
        $expectedData = ['time_range' => $time_range, 'limit' => $limit, 'offset' => $offset];

        $expectedResult = $this->getJsonFixture('top-artists-and-tracks');
        $this->SpotifyApi->expects($this->once())->method('request')->with('GET', $expectedUrl, $expectedData)->willReturn($expectedResult);

        $result = $this->SpotifyApi->getTop('artists', 'medium_term', 20, 0);
        $this->assertArrayHasKey('items', $result);
    }

    /**
     * Test setTokens method result should return true and tokens should be set correctly
     * @return void
     */
    public function testSetTokens()
    {
        $expectedTokens =
            [
                'access_token' => 'access_token_test',
                'refresh_token' => 'refresh_token_test'
            ];

        $result = $this->SpotifyApi->setTokens($expectedTokens);

        $this->assertTrue($result);
        $this->assertSame('access_token_test', $this->SpotifyApi->getAccessToken());
        $this->assertSame('refresh_token_test', $this->SpotifyApi->getRefreshToken());
    }

    /**
     * Test setTokens when invalid data is provided to parameters result should be false
     * @return void
     */
    public function testSetTokensWithInvalidData()
    {
        $result = $this->SpotifyApi->setTokens([]);
        $this->assertFalse($result);

        $result = $this->SpotifyApi->setTokens(['refresh_token' => 'refresh_token_test']);
        $this->assertFalse($result);
    }

    /**
     * Test refreshTokens method result should be true and access token should be set correctly
     * @return void
     */
    public function testRefreshTokens()
    {
        $this->createSpotifyMock(['_getTokens', 'getRefreshToken']);

        $expectedData =
            [
                'grant_type' => 'refresh_token',
                'refresh_token' => 'refresh_token_test'
            ];

        $this->SpotifyApi->expects($this->once())->method('getRefreshToken')->willReturn('refresh_token_test');
        $this->SpotifyApi->expects($this->once())->method('_getTokens')->with($expectedData)->willReturn(['access_token' => 'access_token_test', 'refresh_token' => 'refresh_token_test_2']);
        $result = $this->SpotifyApi->refreshTokens();

        $this->assertTrue($result);
        $this->assertSame('access_token_test', $this->SpotifyApi->getAccessToken());
    }

    /**
     * Test setTokensByCode method result should be true
     * @return void
     */
    public function testSetTokensByCode()
    {
        $this->createSpotifyMock(['_requestAccessToken', 'setTokens']);
        $expectedCode = 'testCode';
        $expectedTokens = ['access_token' => 'access_token_test', 'refresh_token' => 'refresh_token_test'];
        $this->SpotifyApi->expects($this->once())->method('_requestAccessToken')->with($expectedCode)->willReturn($expectedTokens);
        $this->SpotifyApi->expects($this->once())->method('setTokens')->with($expectedTokens)->willReturn(true);

        $result = $this->SpotifyApi->setTokensByCode('testCode');

        $this->assertTrue($result);
    }

    /**
     * Test getProfile method result is an array with `id`key
     * @return void
     */
    public function testGetProfile()
    {
        $this->createSpotifyMock(['request']);

        $expectedReturn = [
            'display_name' => 'testDisplayName',
            'external_urls' => ['spotify' => 'https://open.spotify.com/user/testUser'],
            'followers' => ['href' => null, 'total' => 100],
            'href' => 'https://api.spotify.com/v1/users/testUser',
            'id' => 'testUser',
            'images' => [['height' => 300, 'url' => 'https://test.url.com', 'width' => 300], ['height' => 64, 'url' => 'https://test.url.com/2', 'width' => 64]],
            'type' => 'user',
            'uri' => 'spotify:user:testUser',
        ];
        $this->SpotifyApi->expects($this->once())->method('request')->with('GET', 'https://api.spotify.com/v1/me')->willReturn($expectedReturn);
        $result = $this->SpotifyApi->getProfile();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('id', $result);
    }

    /**
     * Test getCurrentlyPlaying method result is an array with `is_playing` and `item` keys
     * @return void
     */
    public function testGetCurrentlyPlaying()
    {
        $this->createSpotifyMock( ['request']);

        $expectedReturn = [
            'timestamp' => 123456789,
            'context' => [
                'external_urls' => [
                    'spotify' => 'https://test.external.url/test/123456789',
                ],
                'href' => 'https://test.href.url/test/123456789',
                'type' => 'testType',
                'uri' => 'spotify:testUri:123456789',
            ],
            'progress_ms' => 123456789,
            'item' => $this->getJsonFixture('all-saved-tracks'),
            'currently_playing_type' => 'testPlayingType',
            'actions' => [
                'disallows' => [
                    'resuming' => true,
                ],
            ],
            'is_playing' => true,
        ];

        $this->SpotifyApi->expects($this->once())->method('request')->with('GET', 'https://api.spotify.com/v1/me/player/currently-playing')->willReturn($expectedReturn);
        $result = $this->SpotifyApi->getCurrentlyPlaying();

        $this->assertArrayHasKey('is_playing', $result);
        $this->assertArrayhasKey('item', $result);
    }

    /**
     * Test getAllSavedTracks result is an array with `items` key and `href` key containing 'tracks' word
     * @return void
     
     */
    public function testGetAllSavedTracks()
    {
        $this->createSpotifyMock( ['_batchRetrieveData']);
        $expectedResult = $this->getJsonFixture('all-saved-tracks');
        $this->SpotifyApi->expects($this->once())->method('_batchRetrieveData')->with('https://api.spotify.com/v1/me/tracks?limit=50')->willReturn($expectedResult);

        $result = $this->SpotifyApi->getAllSavedTracks();

        $this->assertArrayHasKey('items', $result);
        $this->assertStringContainsString('tracks', $result['href']);
    }

    /**
     * Test getSavedTracks result is an array with `items` key and `href` key containing 'offset=0', 'limit=20' and 'tracks' words
     * @return void
     */
    public function testGetSavedTracks()
    {
        $this->createSpotifyMock( ['request']);

        $expectedResult = $this->getJsonFixture('all-saved-tracks');

        $this->SpotifyApi->expects($this->once())->method('request')->with('GET', 'https://api.spotify.com/v1/me/tracks', ['limit' => 50, 'offset' => 0])->willReturn($expectedResult);
        $result = $this->SpotifyApi->getSavedTracks(50, 0);

        $this->assertArrayHasKey('items', $result);
        $this->assertStringContainsString('offset=0', $result['href']);
        $this->assertStringContainsString('limit=50', $result['href']);
        $this->assertStringContainsString('tracks', $result['href']);
    }

    /**
     * Test getPlaylists method result is an array with `items` key and `href` key containing 'offset=0', 'limit=20' and 'playlists' words
     * @return void
     */
    public function testGetPlaylists()
    {
        $this->createSpotifyMock( ['request']);

        $expectedResult = $this->getJsonFixture('playlists');

        $this->SpotifyApi->expects($this->once())->method('request')->with('GET', 'https://api.spotify.com/v1/me/playlists', ['limit' => 20, 'offset' => 0])->willReturn($expectedResult);
        $result = $this->SpotifyApi->getPlaylists();

        $this->assertArrayHasKey('items', $result);
        $this->assertStringContainsString('offset=0', $result['href']);
        $this->assertStringContainsString('limit=20', $result['href']);
        $this->assertStringContainsString('playlists', $result['href']);
    }

    /**
     * Test getAllPlaylists method result is an array
     * @return void
     */
    public function testGetAllPlaylists()
    {
        $this->createSpotifyMock( ['_batchRetrieveData']);

        $playlistFixture = $this->getJsonFixture('all-playlists');
        $expectedResult = $playlistFixture;

        $willReturn = $expectedResult;
        $willReturn[] = null;

        $this->SpotifyApi->expects($this->once())->method('_batchRetrieveData')->with('https://api.spotify.com/v1/me/playlists?limit=50')->willReturn($willReturn);
        $result = $this->SpotifyApi->getAllPlaylists();

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Test getOwnedPlaylists method result is an array containing playlists owned by user
     * @return void
     */
    public function testGetOwnedPlaylists()
    {
        $this->createSpotifyMock( ['getAllPlaylists', 'getProfile']);

        $expectedResult = $this->getJsonFixture('owned-playlists');
        $allPlaylists = $this->getJsonFixture('all-playlists');
        $getProfile = [
            'display_name' => 'testDisplayName',
            'external_urls' => ['spotify' => 'https://open.spotify.com/user/testUser'],
            'followers' => ['href' => null, 'total' => 100],
            'href' => 'https://api.spotify.com/v1/users/testUser',
            'id' => 'testUser',
            'images' => [['height' => 300, 'url' => 'https://test.url.com', 'width' => 300], ['height' => 64, 'url' => 'https://test.url.com/2', 'width' => 64]],
            'type' => 'user',
            'uri' => 'spotify:user:testUser',
        ];

        $this->SpotifyApi->expects($this->once())->method('getAllPlaylists')->willReturn($allPlaylists);
        $this->SpotifyApi->expects($this->once())->method('getProfile')->willReturn($getProfile);

        $result = $this->SpotifyApi->getOwnedPlaylists();

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Test checkTokens method
     * 
     * - first it fails because no tokens are set
     * - next it fails again because only one token is set
     * - last time it succeeds 
     * @return void
     */
    public function testCheckTokens()
    {
        $result = $this->SpotifyApi->checkTokens();

        $this->assertFalse($result);

        $this->SpotifyApi->setTokens([
            'access_token' => 'access_token_test'
        ]);

        $result = $this->SpotifyApi->checkTokens();

        $this->assertFalse($result);

        $this->SpotifyApi->setTokens([
            'access_token' => 'access_token_test',
            'refresh_token' => 'refresh_token_test'
        ]);

        $result = $this->SpotifyApi->checkTokens();

        $this->assertTrue($result);
    }

    /**
     * Test request result should be an array with data
     * @return void
     */
    public function testRequestSuccess()
    {
        $this->createSpotifyMock( ['_send', 'shouldRetry']);
        $response = $this->createMock(Response::class);

        $response->expects($this->once())->method('isOk')->willReturn(true);
        $response->expects($this->once())->method('getJson')->willReturn(['data' => []]);

        $this->SpotifyApi->expects($this->once())->method('shouldRetry')->willReturn(false);
        $this->SpotifyApi->expects($this->once())->method('_send')->with('GET', 'https://test.url', $this->isArray())->willReturn($response);

        $result = $this->SpotifyApi->request('GET', 'https://test.url');
        $this->assertIsArray($result);
    }

    /**
     * Test request method when it first fails and is successful after retrying
     * @return void
     */
    public function testRequestWithRetry()
    {
        $this->createSpotifyMock( ['_send', 'shouldRetry']);
        $response = $this->createMock(Response::class);
        $exception = $this->createMock(\Exception::class);

        $response->expects($this->once())->method('isOk')->willReturn(true);
        $response->expects($this->once())->method('getJson')->willReturn(['data' => []]);

        $this->SpotifyApi->expects($this->exactly(3))->method('shouldRetry')->willReturnOnConsecutiveCalls(true, true, false);
        $this->SpotifyApi->expects($this->exactly(3))->method('_send')->with('GET', 'https://test.url', $this->isArray())->willReturnOnConsecutiveCalls($exception, $exception, $response);

        $result = $this->SpotifyApi->request('GET', 'https://test.url');
        $this->assertIsArray($result);
    }

    /**
     * Test request method when it throws exception
     * @return void
     */
    public function testRequestThrowException()
    {
        $this->createSpotifyMock( ['_send', 'shouldRetry']);
        $exception = $this->createMock(\Exception::class);

        $this->SpotifyApi->expects($this->exactly(3))->method('shouldRetry')->willReturnOnConsecutiveCalls(true, true, false);
        $this->SpotifyApi->expects($this->exactly(2))->method('_send')->with('GET', 'https://test.url', $this->isArray())->willThrowException($exception);

        $this->expectException(\Exception::class);
        $this->SpotifyApi->request('GET', 'https://test.url');
    }

    /**
     * Test request method result should return null because the request failed and no exceptions were thrown
     * @return void
     */
    public function testRequestFailure()
    {
        $this->createSpotifyMock( ['_send', 'shouldRetry']);
        $response = $this->createMock(Response::class);

        $response->expects($this->once())->method('isOk')->willReturn(false);

        $this->SpotifyApi->expects($this->once())->method('shouldRetry')->willReturn(false);
        $this->SpotifyApi->expects($this->once())->method('_send')->with('GET', 'https://test.url', $this->isArray())->willReturn($response);

        $result = $this->SpotifyApi->request('GET', 'https://test.url');
        $this->assertNull($result);
    }

    /**
     * Test getAccessToken method result should return a string
     * @return void
     
     */
    public function testGetAccessToken()
    {
        $this->SpotifyApi->setTokens(['access_token' => 'access_token_test']);

        $this->assertEquals('access_token_test', $this->SpotifyApi->getAccessToken());
    }

    /**
     * Test getRefreshTokens method result should return a string
     * @return void
     */
    public function testGetRefreshToken()
    {
        $this->SpotifyApi->setTokens(['access_token' => 'access_token_test', 'refresh_token' => 'refresh_token_test']);

        $this->assertEquals('refresh_token_test', $this->SpotifyApi->getRefreshToken());
    }

    /**
     * Test getPlaylist method result should be an array with key `tracks`, `id` and `href` string that contains 'playlists' word
     * @return void
     */
    public function testGetPlaylist()
    {
        $this->createSpotifyMock( ['request']);

        $willReturn = $this->getJsonFixture('get-playlist');
        $this->SpotifyApi->expects($this->once())->method('request')->with('GET', 'https://api.spotify.com/v1/playlists/testPlaylist', [])->willReturn($willReturn);

        $result = $this->SpotifyApi->getPlaylist('testPlaylist');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('tracks', $result);
        $this->assertStringContainsString('playlists', $result['href']);
        $this->assertEquals('testPlaylist', $result['id']);
    }

    /**
     * Test getPlaylistSnapshotId result should return a string
     * @return void
     */
    public function testGetPlaylistSnapshotId()
    {
        $this->createSpotifyMock( ['getPlaylist']);

        $this->SpotifyApi->expects($this->once())->method('getPlaylist')->with('testPlaylistId', 'snapshot_id')->willReturn(['snapshot_id' => 'testSnapshotId']);

        $this->assertEquals('testSnapshotId', $this->SpotifyApi->getPlaylistSnapshotId('testPlaylistId'));
    }

    /**
     * Test getPlaylistTracks result should be an array containing tracks 
     * @return void
     */
    public function testGetPlaylistTracks()
    {
        $httpClient = $this->createPartialMock(\Cake\Http\Client::class, ['buildUrl']);
        $httpClient->expects($this->once())->method('buildUrl')->with('https://api.spotify.com/v1/playlists/testPlaylist/tracks', $this->isArray())->willReturn('https://api.spotify.com/v1/playlists/testPlaylist/tracks');

        $this->createSpotifyMock(['_batchRetrieveData'], $httpClient);
        $getPlaylistTracksFixture = $this->getJsonFixture('get-playlist-tracks');

        $this->SpotifyApi->expects($this->once())->method('_batchRetrieveData')->with('https://api.spotify.com/v1/playlists/testPlaylist/tracks', 'GET')->willReturn($getPlaylistTracksFixture);

        $result = $this->SpotifyApi->getPlaylistTracks('testPlaylist');
        $this->assertIsArray($result);
    }

    /**
     * Test addTracksToPlaylist method result should be true when playlists are successfuly added
     * @return void
     */
    public function testAddTracksToPlaylist()
    {
        $this->createSpotifyMock(['_modifyPlaylistTracks']);
        $this->SpotifyApi->expects($this->once())->method('_modifyPlaylistTracks')->with('POST', 'testPlaylist', ['testTrack', 'testTrack2'], ['prepend' => false])->willReturn(true);

        $this->assertTrue($this->SpotifyApi->addTracksToPlaylist('testPlaylist', ['testTrack', 'testTrack2']));
    }

    /**
     * Test deleteTracksFromPlaylist method result should be true when playlists are successfuly deleted
     * @return void
     */
    public function testDeleteTracksFromPlaylist()
    {
        $this->createSpotifyMock(['_modifyPlaylistTracks']);
        $this->SpotifyApi->expects($this->once())->method('_modifyPlaylistTracks')->with('DELETE', 'testPlaylist', ['testTrack', 'testTrack2'])->willReturn(true);

        $this->assertTrue($this->SpotifyApi->deleteTracksFromPlaylist('testPlaylist', ['testTrack', 'testTrack2']));
    }

    /**
     * Test getTrack method result should be an array with `id` key
     * @return void
     */
    public function testGetTrack()
    {
        $this->createSpotifyMock(['request']);
        $trackFixture = $this->getJsonFixture('track');
        $this->SpotifyApi->expects($this->once())->method('request')->with('GET', 'https://api.spotify.com/v1/tracks/testTrack')->willReturn($trackFixture);


        $result = $this->SpotifyApi->getTrack('testTrack');
        $this->assertIsArray($result);
        $this->assertEquals('testTrack', $result['id']);
    }

    /**
     * Test getMultipleTracks method result should be an array of tracks
     * @return void
     */
    public function testGetMultipleTracks()
    {
        $this->createSpotifyMock(['request']);

        $tracks = $this->getJsonFixture('multiple-tracks');
        $tracksArg = array_map(fn($i) => 'testTrack' . $i, range(0, 50));
        $invokeCount = $this->exactly(2);

        $this->SpotifyApi->expects($invokeCount)->method('request')->willReturnCallback(function ($get, $url, $array) use ($invokeCount, $tracksArg, $tracks) {
            $parameters = [$get, $url, $array];
            if ($invokeCount->numberOfInvocations() == 1) {
                $this->assertSame(['GET', 'https://api.spotify.com/v1/tracks/', ['ids' => implode(',', array_chunk($tracksArg, 50)[0])]], $parameters);
                return ['tracks' => array_chunk($tracks, 50)[0]];
            }

            if ($invokeCount->numberOfInvocations() == 2) {
                $this->assertSame(['GET', 'https://api.spotify.com/v1/tracks/', ['ids' => 'testTrack50']], $parameters);
                return ['tracks' => array_chunk($tracks, 50)[1]];
            }
        });

        $result = $this->SpotifyApi->getMultipleTracks($tracksArg);

        $this->assertIsArray($result);
        $this->assertEquals($tracks, $result);
    }

    /**
     * Test getLeastPopularArtist method result should be an array with key `id` and `popularity` that equals $expectedPopularity
     * 
     * @var int $expectedPopularity
     * 
     * @return void
     
     */
    public function testGetLeastPopularArtist()
    {
        $clientMock = $this->createPartialMock(\Cake\Http\Client::class, ['buildUrl']);
        $clientMock->expects($this->once())->method('buildUrl')->with('https://api.spotify.com/v1/me/top/artists', ['time_range' => 'long_term', 'limit' => 50, 'offset' => 0])->willReturn('https://api.spotify.com/v1/me/top/artists?time_range=long_term&limit=50&offset=0');

        $this->createSpotifyMock(['_batchRetrieveData'], $clientMock);

        $leastPopularArtistsFixture = $this->getJsonFixture('least-popular-artists');
        $this->SpotifyApi->expects($this->once())->method('_batchRetrieveData')->with('https://api.spotify.com/v1/me/top/artists?time_range=long_term&limit=50&offset=0')->willReturn($leastPopularArtistsFixture);

        $expectedPopularity = 21;

        $result = $this->SpotifyApi->getLeastPopularArtist();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('id', $result);
        $this->assertEquals($expectedPopularity, $result['popularity']);
    }

    /**
     * Test getRecentlyPlayedTracks method result should be an array with elements having keys `track` and `played_at`
     * @return void
     
     */
    public function testGetRecentlyPlayedTracks()
    {
        $clientMock = $this->createPartialMock(\Cake\Http\Client::class, ['buildUrl']);
        $clientMock->expects($this->once())->method('buildUrl')->with('https://api.spotify.com/v1/me/player/recently-played', ['limit' => '20', 'before' => null, 'after' => 1734739200])->willReturn('https://api.spotify.com/v1/me/player/recently-played?limit=20&after=1734739200');

        $this->createSpotifyMock(['_batchRetrieveData'], $clientMock);

        $recentlyPlayedFixture = $this->getJsonFixture('recently-played');
        $this->SpotifyApi->expects($this->once())->method('_batchRetrieveData')->with('https://api.spotify.com/v1/me/player/recently-played?limit=20&after=1734739200')->willReturn($recentlyPlayedFixture);

        $result = $this->SpotifyApi->getRecentlyPlayedTracks(20, 1734739200);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('track', $result[0]);
        $this->assertArrayHasKey('played_at', $result[0]);
    }

    /**
     * Test if getRecentlyPlayedTracks method throws an exception
     * @return void
     */
    public function testGetRecentlyPlayedTracksWillThrowException()
    {
        $this->expectException(InvalidArgumentException::class);

        $this->SpotifyApi->getRecentlyPlayedTracks(20, 1734739200, 1734739200);
    }

    public function testCreatePlaylist()
    {
        $this->createSpotifyMock(['request', 'getProfile']);

        $this->SpotifyApi->expects($this->once())->method('getProfile')->willReturn(['id' => 'testUser']);

        $inputData = ['name' => 'testName', 'description' => 'testDescription', 'public' => true, 'collaborative' => true];
        $createPlaylistFixture = $this->getJsonFixture('create-playlist');
        $this->SpotifyApi->expects($this->once())->method('request')->with('POST', 'https://api.spotify.com/v1/users/testUser/playlists', json_encode($inputData))->willReturn($createPlaylistFixture);

        $result = $this->SpotifyApi->createPlaylist('testName', 'testDescription', true, true);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('id', $result);
        $this->assertEquals('testDescription', $result['description'], );
        $this->assertEquals('testName', $result['name']);
        $this->assertTrue($result['public']);
        $this->assertTrue($result['collaborative']);
        $this->assertEquals('testUser', $result['owner']['id']);
    }
}