<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * PlaylistMerger Entity
 *
 * @property int $id
 * @property int $user_id
 * @property string $source_playlists
 * @property string $target_playlist_id
 * @property string|null $target_playlist_last_snapshot
 *
 * @property \App\Model\Entity\User $user
 */
class PlaylistMerger extends Entity
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
        'source_playlists' => true,
        'target_playlist_id' => true,
        'target_playlist_last_snapshot' => true,
        'user' => true,
    ];
}
