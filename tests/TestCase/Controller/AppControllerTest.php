<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Controller\AppController;
use Cake\Http\ServerRequest;
use Cake\Http\TestSuite\HttpClientTrait;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\AppController Test Case
 *
 * @uses \App\Controller\AppController
 */
class AppControllerTest extends TestCase
{
    use IntegrationTestTrait;
    use HttpClientTrait;

    /**
     * Test beforeFilter method
     *
     * @return void
     * @uses \App\Controller\AppController::beforeFilter()
     */
    public function testBeforeFilter(): void
    {
        $this->get('/playlist/view/1');

        $this->assertRedirect(['controller' => 'Pages', 'action' => 'display', 'home']);

        $this->get(['controller' => 'Pages', 'action' => 'display', 'home']);

        $this->assertNoRedirect();
        $this->assertResponseOk();
    }

    /**
     * Test makeCacheKey method
     *
     * @return void
     * @uses \App\Controller\AppController::makeCacheKey()
     */
    public function testMakeCacheKey(): void
    {
        $data = ['test', 'test2', 'test3'];
        $controller = new AppController(new ServerRequest());

        $actual = $controller->makeCacheKey($data);

        $this->assertSame('test-test2-test3', $actual);
    }
}
