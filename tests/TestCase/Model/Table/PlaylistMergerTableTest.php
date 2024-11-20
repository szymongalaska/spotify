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
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @uses \App\Model\Table\PlaylistMergerTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
