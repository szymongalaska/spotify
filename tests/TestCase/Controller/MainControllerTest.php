<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Controller\MainController;
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

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.Main',
    ];

    /**
     * Test login method
     *
     * @return void
     * @uses \App\Controller\MainController::login()
     */
    public function testLogin(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test dashboard method
     *
     * @return void
     * @uses \App\Controller\MainController::dashboard()
     */
    public function testDashboard(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test ajaxGetTopTracks method
     *
     * @return void
     * @uses \App\Controller\MainController::ajaxGetTopTracks()
     */
    public function testAjaxGetTopTracks(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test getUserTopTracks method
     *
     * @return void
     * @uses \App\Controller\MainController::getUserTopTracks()
     */
    public function testGetUserTopTracks(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test logout method
     *
     * @return void
     * @uses \App\Controller\MainController::logout()
     */
    public function testLogout(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test getUserTopArtists method
     *
     * @return void
     * @uses \App\Controller\MainController::getUserTopArtists()
     */
    public function testGetUserTopArtists(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test ajaxGetTopArtists method
     *
     * @return void
     * @uses \App\Controller\MainController::ajaxGetTopArtists()
     */
    public function testAjaxGetTopArtists(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test ajaxGetCurrentSong method
     *
     * @return void
     * @uses \App\Controller\MainController::ajaxGetCurrentSong()
     */
    public function testAjaxGetCurrentSong(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test test method
     *
     * @return void
     * @uses \App\Controller\MainController::test()
     */
    public function testTest(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
