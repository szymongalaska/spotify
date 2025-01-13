<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class Initial extends BaseMigration
{
    /**
     * Up Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-up-method
     * @return void
     */
    public function up(): void
    {
        $this->table('playlist_merger')
            ->addColumn('user_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => true,
            ])
            ->addColumn('source_playlists', 'text', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('target_playlist_id', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => false,
            ])
            ->addColumn('options', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addIndex(
                [
                    'target_playlist_id',
                ],
                [
                    'name' => 'target_playlist_id',
                    'unique' => true,
                ]
            )
            ->addIndex(
                [
                    'user_id',
                ],
                [
                    'name' => 'fk_playlist_merger_user',
                ]
            )
            ->create();

        $this->table('playlist_merger_cronjobs')
            ->addColumn('playlist_merger_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => true,
            ])
            ->addColumn('frequency', 'string', [
                'default' => 'weekly',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'playlist_merger_id',
                ],
                [
                    'name' => 'playlist_merger_id',
                ]
            )
            ->create();

        $this->table('users')
            ->addColumn('spotify_id', 'string', [
                'default' => null,
                'limit' => 100,
                'null' => true,
            ])
            ->addColumn('display_name', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('image_url', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('access_token', 'string', [
                'default' => null,
                'limit' => 500,
                'null' => true,
            ])
            ->addColumn('refresh_token', 'string', [
                'default' => null,
                'limit' => 500,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->create();

        $this->table('playlist_merger')
            ->addForeignKey(
                'user_id',
                'users',
                'id',
                [
                    'update' => 'CASCADE',
                    'delete' => 'CASCADE',
                    'constraint' => 'fk_playlist_merger_user'
                ]
            )
            ->update();

        $this->table('playlist_merger_cronjobs')
            ->addForeignKey(
                'playlist_merger_id',
                'playlist_merger',
                'id',
                [
                    'update' => 'NO_ACTION',
                    'delete' => 'NO_ACTION',
                    'constraint' => 'playlist_merger_cronjobs_ibfk_1'
                ]
            )
            ->update();
    }

    /**
     * Down Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-down-method
     * @return void
     */
    public function down(): void
    {
        $this->table('playlist_merger')
            ->dropForeignKey(
                'user_id'
            )->save();

        $this->table('playlist_merger_cronjobs')
            ->dropForeignKey(
                'playlist_merger_id'
            )->save();

        $this->table('playlist_merger')->drop()->save();
        $this->table('playlist_merger_cronjobs')->drop()->save();
        $this->table('users')->drop()->save();
    }
}
