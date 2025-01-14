<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * UsersFixture
 */
class UsersFixture extends TestFixture
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
                'spotify_id' => 1,
                'display_name' => 'Lorem ipsum dolor sit amet',
                'image_url' => 'Lorem ipsum dolor sit amet',
                'access_token' => 'Lorem ipsum dolor sit amet',
                'refresh_token' => 'Lorem ipsum dolor sit amet',
                'created' => 1731590444,
                'modified' => 1731590444,
            ],
            [
                'id' => 2,
                'spotify_id' => 2,
                'display_name' => 'Lorem ipsum dolor sit amet',
                'image_url' => 'Lorem ipsum dolor sit amet',
                'access_token' => 'Lorem ipsum dolor sit amet',
                'refresh_token' => 'Lorem ipsum dolor sit amet',
                'created' => 1731590444,
                'modified' => 1731590444,
            ],
            [
                'id' => 3,
                'spotify_id' => 3,
                'display_name' => 'Lorem ipsum dolor sit amet',
                'image_url' => 'Lorem ipsum dolor sit amet',
                'access_token' => 'Lorem ipsum dolor sit amet',
                'refresh_token' => 'Lorem ipsum dolor sit amet',
                'created' => 1731590444,
                'modified' => 1731590444,
            ],

        ];
        parent::init();
    }
}
