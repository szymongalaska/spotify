<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Cache\Cache;

class PlaylistMergerController extends AppController
{
    /**
     * Render playlist merger
     * 
     * @return \Cake\Http\Response|null
     */
    public function index()
    {
        $this->set('myPlaylists', $this->getUserPlaylists());
    }

    /**
     * Get a list of all playlists owned or followed by the current Spotify user.
     * 
     * @return array
     */
    public function getUserPlaylists()
    {
        return $this->getApi()->getAllPlaylists();
    }
}