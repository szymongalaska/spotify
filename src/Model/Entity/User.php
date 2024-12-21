<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * User Entity
 *
 * @property int $id
 * @property string $spotify_id
 * @property string $display_name
 * @property string|null $image_url
 * @property string|null $access_token
 * @property string|null $refresh_token
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 * 
 * @property \App\Model\Entity\PlaylistMerger[] $playlist_merger
 */
class User extends Entity
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
        'spotify_id' => true,
        'display_name' => true,
        'image_url' => true,
        'access_token' => true,
        'refresh_token' => true,
        'created' => true,
        'modified' => true,
    ];
}
