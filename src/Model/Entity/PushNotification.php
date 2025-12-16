<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * PushNotification Entity
 *
 * @property int $id
 * @property int $user_id
 * @property bool $library_new_and_available_changes
 *
 * @property \App\Model\Entity\User $user
 */
class PushNotification extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'user_id' => true,
        'library_new_and_available_changes' => true,
        'user' => true,
    ];
}
