<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class CreatePushNotifications extends BaseMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/migrations/4/en/migrations.html#the-change-method
     * @return void
     */
    public function change(): void
    {
        $this->table('push_notifications')
            ->addColumn('user_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => true,
            ])
            ->addColumn('library_new_and_available_changes', 'boolean')
            ->addIndex(
                [
                    'user_id'
                ],
                [
                    'name' => 'fk_push_notifications_user',
                ]
            )->create();

        $this->table('push_notifications')
            ->addForeignKey(
                'user_id',
                'users',
                'id',
                [
                    'update' => 'CASCADE',
                    'delete' => 'CASCADE',
                    'constraint' => 'fk_push_notifications_user'
                ]
            )->update();
    }

    public function down(): void
    {
        $this->table('push_notifications')
            ->dropForeignKey(
                'user_id'
            )->save();

        $this->table('push_notifications')->drop()->save();
    }
}
