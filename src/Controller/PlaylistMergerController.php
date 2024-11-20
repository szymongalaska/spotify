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
        $this->set('myOwnPlaylists', $this->getUserOwnPlaylists());
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

    public function getUserOwnPlaylists()
    {
        return $this->getApi()->getOwnedPlaylists($this->getRequest()->getSession()->read('user')['spotify_id']);
    }
}