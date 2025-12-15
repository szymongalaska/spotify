<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Controller\Component\SpotifyApiComponent;
use App\Controller\MainController;
use Cake\Http\ServerRequest;
use Cake\Http\TestSuite\HttpClientTrait;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\MainController Test Case
 *
 * @uses \App\Controller\MainController
 */
class MainControllerTest extends TestCase
{
    use IntegrationTestTrait;
    use HttpClientTrait;

    protected $user;
    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.Users',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $usersTable = \Cake\ORM\TableRegistry::getTableLocator()->get('Users');
        $this->user = $usersTable->find('all')->first();
    }

    protected function mockCurrentlyPlayingGet()
    {
        $this->mockClientGet('https://api.spotify.com/v1/me/player/currently-playing', $this->newClientResponse(200, []));
    }

    protected function tearDown(): void
    {
        unset($this->user);
        parent::tearDown();
    }

    /**
     * Test login method
     *
     * @return void
     * @uses \App\Controller\MainController::login()
     */
    public function testLoginRedirectUserAlreadyLogged(): void
    {
        $this->session(['user' => $this->user]);
        $this->get('/login');
        $this->assertRedirect('/dashboard');
    }

    public function testLoginRedirectSuccesfulLogin()
    {
        $this->configRequest(['query' => ['code' => '1']]);

        $this->mockClientPost('https://accounts.spotify.com/api/token', $this->newClientResponse(200, [], json_encode(['access_token' => 'test', 'refresh_token' => 'test'])));
        $this->mockClientGet('https://api.spotify.com/v1/me', $this->newClientResponse(200, [], json_encode(['id' => '1'])));

        $this->get('/login');
        $this->assertRedirect('/dashboard');
    }

    public function testLoginRedirectWithError()
    {
        $this->configRequest(['query' => ['error' => 'testError']]);

        $this->get('/login');
        $this->assertFlashMessage('testError');
        $this->assertRedirect(['controller' => 'Pages', 'action' => 'display', 'home']);
    }

    public function testLoginRedirectToSpotifyApi()
    {
        $this->get('/login');
        $this->assertRedirectContains('https://accounts.spotify.com/authorize');
    }

    public function testDashboard()
    {
        $this->session(['user' => $this->user]);

        $this->mockCurrentlyPlayingGet();
        $this->mockClientGet('https://api.spotify.com/v1/me/top/artists?time_range=medium_term&limit=5&offset=0', $this->newClientResponse(200, [], file_get_contents(TESTS . 'Fixture' . DS . 'SpotifyApi' . DS . 'top-artists-and-tracks' . '.json')));
        $this->mockClientGet('https://api.spotify.com/v1/me/top/tracks?time_range=medium_term&limit=5&offset=0', $this->newClientResponse(200, [], json_encode(['data'])));

        $this->get('/dashboard');

        $this->assertResponseOk();
        $this->assertResponseContains('John Maus');
    }

    public function testAjaxGetTopTracks()
    {
        $this->session(['user' => $this->user]);

        $this->mockCurrentlyPlayingGet();
        $this->mockClientGet('https://api.spotify.com/v1/me/top/tracks?time_range=long_term&limit=5&offset=0', $this->newClientResponse(200, [], json_encode(['items' => []])));

        $this->get('/main/ajax-get-top-tracks/long_term');

        $this->assertResponseOk();
        $this->assertResponseContains('<div id="tracks">');
    }

    /**
     * Test getUserTopTracks method
     *
     * @return void
     * @uses \App\Controller\MainController::getUserTopTracks()
     */
    public function testGetUserTopTracks(): void
    {
        $spotifyApiMock = $this->createPartialMock(SpotifyApiComponent::class, ['getTop']);
        $spotifyApiMock->expects($this->once())->method('getTop')->with('tracks', 'medium_term', 5)->willReturn([]);

        $controller = new MainController(new ServerRequest());

        $controller->SpotifyApi = $spotifyApiMock;

        $result = $controller->getUserTopTracks();

        $this->assertIsArray($result);
    }

    /**
     * Test logout method
     *
     * @return void
     * @uses \App\Controller\MainController::logout()
     */
    public function testLogout(): void
    {
        $this->session(['user' => $this->user]);

        $this->get('/main/logout');

        $this->assertRedirect(['controller' => 'Pages', 'action' => 'display', 'home']);
        $this->assertSessionNotHasKey('user');
    }

    /**
     * Test getUserTopArtists method
     *
     * @return void
     * @uses \App\Controller\MainController::getUserTopArtists()
     */
    public function testGetUserTopArtists(): void
    {
        $spotifyApiMock = $this->createPartialMock(SpotifyApiComponent::class, ['getTop']);
        $spotifyApiMock->expects($this->once())->method('getTop')->with('artists', 'medium_term', 5)->willReturn([]);

        $controller = new MainController(new ServerRequest());

        $controller->SpotifyApi = $spotifyApiMock;

        $result = $controller->getUserTopArtists();

        $this->assertIsArray($result);
    }

    /**
     * Test ajaxGetTopArtists method
     *
     * @return void
     * @uses \App\Controller\MainController::ajaxGetTopArtists()
     */
    public function testAjaxGetTopArtists(): void
    {
        $this->session(['user' => $this->user]);

        $this->mockCurrentlyPlayingGet();
        $this->mockClientGet('https://api.spotify.com/v1/me/top/artists?time_range=long_term&limit=5&offset=0', $this->newClientResponse(200, [], json_encode(['items' => []])));

        $this->get('/main/ajax-get-top-artists/long_term');

        $this->assertResponseOk();
        $this->assertResponseContains('<div id="artists">');
    }

    /**
     * Test ajaxGetCurrentSong method
     *
     * @return void
     * @uses \App\Controller\MainController::ajaxGetCurrentSong()
     */
    public function testAjaxGetCurrentSong(): void
    {
        $this->session(['user' => $this->user]);

        $this->mockCurrentlyPlayingGet();

        $this->get('/main/ajax-get-current-song');

        $this->assertResponseOk();
    }

    public function testNewlyUnavailableAndUnavailableTracks(): void
    {
        $this->session(['user' => $this->user]);

        $this->mockCurrentlyPlayingGet();

        $this->get('/new-and-unavailable');

        $this->assertResponseOk();
        $this->assertResponseContains('<div id="new-and-available">');
    }
}
