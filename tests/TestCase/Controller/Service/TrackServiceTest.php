<?php
declare(strict_types=1);

namespace App\Test\TestCase\Service;

use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use App\Service\TrackService;

/**
 * App\Service\TrackService Test Case
 *
 * @uses \App\Service\TrackService
 */
class TrackServiceTest extends TestCase
{
    use IntegrationTestTrait;

    protected $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new TrackService();
    }

    public function testFilterNotAvailableTracks(): void
    {
        $tracks = json_decode(file_get_contents(TESTS . 'Fixture' . DS . 'SpotifyApi' . DS . 'all-saved-tracks' . '.json'), true);
        $actual = $this->service->filterNotAvailableTracks($tracks['items']);

        $this->assertSame(1, \count($actual));
        $this->assertSame('Mass Anasthesia', $actual[0]['track']['name']);
    }

    public function testFilterAvailableTracks(): void
    {
        $tracks = json_decode(file_get_contents(TESTS . 'Fixture' . DS . 'SpotifyApi' . DS . 'all-saved-tracks' . '.json'), true);
        $actual = $this->service->filterAvailableTracks($tracks['items']);
        
        $this->assertEmpty($actual);
    }
}