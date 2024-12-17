<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * PlaylistMergerCronjobsFixture
 */
class PlaylistMergerCronjobsFixture extends TestFixture
{
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'id' => 1,
                'playlist_merger_id' => 1,
                'frequency' => 'Lorem ipsum dolor sit amet',
            ],
        ];
        parent::init();
    }
}
