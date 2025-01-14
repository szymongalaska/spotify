<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\PlaylistMergerTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\PlaylistMergerTable Test Case
 */
class PlaylistMergerTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\PlaylistMergerTable
     */
    protected $PlaylistMerger;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.PlaylistMerger',
        'app.Users',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('PlaylistMerger') ? [] : ['className' => PlaylistMergerTable::class];
        $this->PlaylistMerger = $this->getTableLocator()->get('PlaylistMerger', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->PlaylistMerger);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\PlaylistMergerTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $data = [
            'user_id' => 1,
            'source_playlists' => 'testSourcePlaylists',
            'target_playlist_id' => 'testTargetPlaylistId',
        ];

        $playlistMerger = $this->PlaylistMerger->newEntity($data);
        $this->PlaylistMerger->save($playlistMerger);

        $this->assertEmpty($playlistMerger->getErrors());
        

        $data['source_playlists'] = 'testSourcePlaylists2';

        $playlistMerger = $this->PlaylistMerger->newEntity($data);

        $this->assertNotEmpty($playlistMerger->getErrors());
    }

    public function testHasOneRelationship(): void
    {
        $this->assertTrue($this->PlaylistMerger->hasAssociation('PlaylistMergerCronjobs'));
    }

    public function testBelongsToRelationship(): void
    {
        $this->assertTrue($this->PlaylistMerger->hasAssociation('Users'));
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @uses \App\Model\Table\PlaylistMergerTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $data = [
            'user_id' => 1,
            'source_playlists' => 'testSourcePlaylists',
            'target_playlist_id' => 'testTargetPlaylistId',
        ];

        $playlistMerger = $this->PlaylistMerger->newEntity($data);
        $this->PlaylistMerger->save($playlistMerger);

        $playlistMerger = $this->PlaylistMerger->newEntity($data);

        $this->assertFalse($this->PlaylistMerger->save($playlistMerger));
        $this->assertArrayHasKey('unique', $playlistMerger->getError('target_playlist_id'));

        $data['user_id'] = 9996474669;
        $data['target_playlist_id'] = 'testTargetPlaylistId2';

        $playlistMerger = $this->PlaylistMerger->newEntity($data);
        
        $this->assertFalse($this->PlaylistMerger->save($playlistMerger));
        $this->assertArrayHasKey('_existsIn', $playlistMerger->getError('user_id'));
    }
}
