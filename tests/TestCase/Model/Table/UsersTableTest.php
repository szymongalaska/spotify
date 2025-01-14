<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\UsersTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\UsersTable Test Case
 */
class UsersTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\UsersTable
     */
    protected $Users;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
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
        $config = $this->getTableLocator()->exists('Users') ? [] : ['className' => UsersTable::class];
        $this->Users = $this->getTableLocator()->get('Users', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Users);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\UsersTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $data = [
            'spotify_id' => 'validId',
            'display_name' => 'validName',
            'image_url' => 'https://valid.image.url',
            'access_token' => 'validAccessToken',
            'refresh_token' => 'validRefreshToken'
        ];

        $user = $this->Users->newEntity($data);
        $this->assertEmpty($user->getErrors());

        $data['spotify_id'] = null;

        $user = $this->Users->newEntity($data);
        $this->assertNotEmpty($user->getErrors());
    }

    public function testHasManyRelationship(): void
    {
        $this->assertTrue($this->Users->hasAssociation('PlaylistMerger'));
    }

    public function testUpdateUserTokens(): void
    {
        $user = $this->Users->get(1);

        $newAccessToken = 'new_access_token';
        $newRefreshToken = 'new_refresh_token';

        $user = $this->Users->updateUserTokens($user, $newAccessToken, $newRefreshToken);

        $this->assertSame($newAccessToken, $user->access_token);
        $this->assertSame($newRefreshToken, $user->refresh_token);
    }
}
