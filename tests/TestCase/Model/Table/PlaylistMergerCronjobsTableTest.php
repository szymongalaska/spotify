<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\PlaylistMergerCronjobsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\PlaylistMergerCronjobsTable Test Case
 */
class PlaylistMergerCronjobsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\PlaylistMergerCronjobsTable
     */
    protected $PlaylistMergerCronjobs;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.PlaylistMergerCronjobs',
        'app.PlaylistMerger',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('PlaylistMergerCronjobs') ? [] : ['className' => PlaylistMergerCronjobsTable::class];
        $this->PlaylistMergerCronjobs = $this->getTableLocator()->get('PlaylistMergerCronjobs', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->PlaylistMergerCronjobs);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\PlaylistMergerCronjobsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @uses \App\Model\Table\PlaylistMergerCronjobsTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
