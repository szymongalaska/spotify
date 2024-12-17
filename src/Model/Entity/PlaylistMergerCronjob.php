<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * PlaylistMergerCronjob Entity
 *
 * @property int $id
 * @property int $playlist_merger_id
 * @property string $frequency
 *
 * @property \App\Model\Entity\PlaylistMerger $playlist_merger
 */
class PlaylistMergerCronjob extends Entity
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
        'playlist_merger_id' => true,
        'frequency' => true,
        'playlist_merger' => true,
    ];
}
