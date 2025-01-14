<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Controller\PlaylistController;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\Http\TestSuite\HttpClientTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\PlaylistController Test Case
 *
 * @uses \App\Controller\PlaylistController
 */
class PlaylistControllerTest extends TestCase
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
        $this->session(['user' => $this->user]);
    }

    protected function tearDown(): void
    {
        unset($this->user);
        parent::tearDown();
    }

    protected function mockCurrentyPlayingGet()
    {
        $this->mockClientGet('https://api.spotify.com/v1/me/player/currently-playing', $this->newClientResponse(200, []));
    }
    /**
     * Test view method
     *
     * @return void
     * @uses \App\Controller\PlaylistController::view()
     */
    public function testView(): void
    {
        $tracks = file_get_contents(TESTS . 'Fixture' . DS . 'SpotifyApi' . DS . 'get-playlist-tracks' . '.json');
        $playlist = file_get_contents(TESTS . 'Fixture' . DS . 'SpotifyApi' . DS . 'get-playlist' . '.json');

        $this->mockClientGet('https://api.spotify.com/v1/playlists/1', $this->newClientResponse(200, [], $playlist));
        $this->mockClientGet('https://api.spotify.com/v1/playlists/1/tracks', $this->newClientResponse(200, [], json_encode(['next' => null, 'items' => json_decode($tracks)])));
        $this->mockCurrentyPlayingGet();
        $this->get('/playlist/view/1');

        $this->assertResponseOk();
    }

    public function testViewWithFlashError()
    {
        $this->enableRetainFlashMessages();
        $this->mockCurrentyPlayingGet();
        $this->mockClientGet('https://api.spotify.com/v1/playlists/1', $this->newClientResponse(400, []));

        $this->get('/playlist/view/1');
        $this->assertFlashElement('flash/error');
    }

    /**
     * Test viewNotAvailable method
     *
     * @return void
     * @uses \App\Controller\PlaylistController::viewNotAvailable()
     */
    public function testViewNotAvailable(): void
    {
        $this->mockCurrentyPlayingGet();
        $this->mockClientGet('https://api.spotify.com/v1/me/playlists?limit=50', $this->newClientResponse(200, [], json_encode(['next' => null, 'items' => []])));
        
        $this->get('/playlist/view-not-available');
        $this->assertResponseOk();
        $this->assertResponseContains('<div class="content list playlists">');
    }

    /**
     * Test viewNotAvailableTracks method
     *
     * @return void
     * @uses \App\Controller\PlaylistController::viewNotAvailableTracks()
     */
    public function testViewNotAvailableTracks(): void
    {
        $tracks = file_get_contents(TESTS . 'Fixture' . DS . 'SpotifyApi' . DS . 'get-playlist-tracks' . '.json');
        $playlist = file_get_contents(TESTS . 'Fixture' . DS . 'SpotifyApi' . DS . 'get-playlist' . '.json');

        $this->mockCurrentyPlayingGet();
        $this->mockClientGet('https://api.spotify.com/v1/playlists/1', $this->newClientResponse(200, [], $playlist));
        $this->mockClientGet('https://api.spotify.com/v1/playlists/1/tracks?market=from_token', $this->newClientResponse(200, [], json_encode(['next' => null, 'items' => json_decode($tracks)])));

        $this->get('/playlist/view-not-available-tracks/1');
        $this->assertResponseOk();
        $this->assertResponseContains('<div class="playlist">');
    }

    /**
     * Test find method
     *
     * @return void
     * @uses \App\Controller\PlaylistController::find()
     */
    public function testFind(): void
    {
        $this->mockCurrentyPlayingGet();
        $this->mockClientGet('https://api.spotify.com/v1/me/playlists?limit=50', $this->newClientResponse(200, [], json_encode(['next' => null, 'items' => []])));
        
        $this->get('/playlist/view-not-available');
        $this->assertResponseOk();
        $this->assertResponseContains('<div class="content list playlists">');
    }
}
