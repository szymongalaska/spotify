<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Controller\PlaylistMergerController;
use Cake\Http\TestSuite\HttpClientTrait;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\PlaylistMergerController Test Case
 *
 * @uses \App\Controller\PlaylistMergerController
 */
class PlaylistMergerControllerTest extends TestCase
{
    use IntegrationTestTrait;

    use HttpClientTrait;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $usersTable = \Cake\ORM\TableRegistry::getTableLocator()->get('Users');
        $this->user = $usersTable->find('all')->first();
        $this->session(['user' => $this->user]);

        $this->mockClientGet('https://api.spotify.com/v1/me/player/currently-playing', $this->newClientResponse(200, []));
    }

    protected function tearDown(): void
    {
        unset($this->user);
        parent::tearDown();
    }

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.PlaylistMerger',
        'app.Users',
        'app.PlaylistMergerCronjobs',
    ];

    /**
     * Test edit method
     *
     * @return void
     * @uses \App\Controller\PlaylistMergerController::edit()
     */
    public function testEdit(): void
    {
        $this->mockClientGet('https://api.spotify.com/v1/me/playlists?limit=50', $this->newClientResponse(200, [], json_encode(['next' => null, 'items' => []])));
        $this->mockClientGet('https://api.spotify.com/v1/me', $this->newClientResponse(200, [], json_encode(['id' => 1])));

        $this->get('/playlist-merger/edit/1');

        $this->assertResponseOk();
    }

    public function testEditWithFlashError()
    {
        $this->enableRetainFlashMessages();

        $this->get('/playlist-merger/edit/2');

        $this->assertFlashMessage('Access denied');
        $this->assertRedirect(['action' => 'index']);
    }

    /**
     * Test add method
     *
     * @return void
     * @uses \App\Controller\PlaylistMergerController::add()
     */
    public function testAdd(): void
    {
        $this->mockClientGet('https://api.spotify.com/v1/me/playlists?limit=50', $this->newClientResponse(200, [], json_encode(['next' => null, 'items' => []])));
        $this->mockClientGet('https://api.spotify.com/v1/me', $this->newClientResponse(200, [], json_encode(['id' => 1])));

        $this->get('/playlist-merger/add');

        $this->assertResponseOk();
    }

    /**
     * Test index method
     *
     * @return void
     * @uses \App\Controller\PlaylistMergerController::index()
     */
    public function testIndex(): void
    {
        $playlist = file_get_contents(TESTS . 'Fixture' . DS . 'SpotifyApi' . DS . 'get-playlist' . '.json');
        $this->mockClientGet('https://api.spotify.com/v1/playlists/Lorem%20ipsum%20dolor%20sit%20amet', $this->newClientResponse(200, [], $playlist));
        
        $this->get('/playlist-merger');

        $this->assertResponseOk();
        $this->assertResponseContains('<div class="playlistMerger">');
    }

    /**
     * Test delete method
     *
     * @return void
     * @uses \App\Controller\PlaylistMergerController::delete()
     */
    public function testDeleteSuccess(): void
    {
        $this->enableRetainFlashMessages();
        $this->get('/playlist-merger/delete/1');

        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage('Merge deleted');
    }

    public function testDeleteWithAccessDeniedFlash()
    {
        $this->enableRetainFlashMessages();
        $this->get('/playlist-merger/delete/2');

        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage('Access denied');
    }

    /**
     * Test deleteCronjob method
     *
     * @return void
     * @uses \App\Controller\PlaylistMergerController::deleteCronjob()
     */
    public function testDeleteCronjob(): void
    {
        $this->enableRetainFlashMessages();
        $this->get('/playlist-merger/delete-cronjob/1');

        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage('Auto synchronization disabled');
    }

    /**
     * Test saveAndMerge method
     *
     * @return void
     * @uses \App\Controller\PlaylistMergerController::saveAndMerge()
     */
    public function testSaveAndMerge(): void
    {
        $this->enableCsrfToken();

        $tracks = json_decode(file_get_contents(TESTS . 'Fixture' . DS . 'SpotifyApi' . DS . 'get-playlist-tracks' . '.json'));

        $this->mockClientGet('https://api.spotify.com/v1/playlists/test', $this->newClientResponse(200));
        $this->mockClientGet('https://api.spotify.com/v1/playlists/test?fields=snapshot_id', $this->newClientResponse(200, [], json_encode(['snapshot_id' => 'testSnapshot'])));
        $this->mockClientGet('https://api.spotify.com/v1/playlists/test2?fields=snapshot_id', $this->newClientResponse(200, [], json_encode(['snapshot_id' => 'testSnapshot'])));
        $this->mockClientGet('https://api.spotify.com/v1/playlists/test3?fields=snapshot_id', $this->newClientResponse(200, [], json_encode(['snapshot_id' => 'testSnapshot'])));
        $this->mockClientGet('https://api.spotify.com/v1/playlists/test/tracks?fields=next%2C%20items%28added_at%2Ctrack%28id%29%29', $this->newClientResponse(200, [], json_encode(['next' => null, 'items' => $tracks])));
        $this->mockClientGet('https://api.spotify.com/v1/playlists/test2/tracks?fields=next%2C%20items%28added_at%2Ctrack%28id%29%29', $this->newClientResponse(200, [], json_encode(['next' => null, 'items' => $tracks])));
        $this->mockClientGet('https://api.spotify.com/v1/playlists/test3/tracks?fields=next%2C%20items%28added_at%2Ctrack%28id%29%29', $this->newClientResponse(200, [], json_encode(['next' => null, 'items' => $tracks])));

        $this->post('/playlist-merger/save-and-merge', ['target-playlist' => 'test', 'source-playlists' => ['test2', 'test3']]);

        $this->assertRedirect(['action' => 'index']);
    }

    /**
     * Test saveAndMerge method
     *
     * @return void
     * @uses \App\Controller\PlaylistMergerController::saveAndMerge()
     */
    public function testSaveAndMergeFailWithoutQueryData(): void
    {
        $this->enableRetainFlashMessages();
        $this->get('/playlist-merger/save-and-merge');

        $this->assertRedirect(['action' => 'add']);
        $this->assertFlashMessage('Select source and target playlists');
    }
}
