<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\PushNotificationsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\PushNotificationsTable Test Case
 */
class PushNotificationsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\PushNotificationsTable
     */
    protected $PushNotifications;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.PushNotifications',
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
        $config = $this->getTableLocator()->exists('PushNotifications') ? [] : ['className' => PushNotificationsTable::class];
        $this->PushNotifications = $this->getTableLocator()->get('PushNotifications', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->PushNotifications);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\PushNotificationsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\PushNotificationsTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
