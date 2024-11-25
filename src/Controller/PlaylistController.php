<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Cache\Cache;
use Cake\Http\Exception\BadRequestException;

class PlaylistController extends AppController
{

    /**
     * Render playlist content view
     * @param string $playlistId The Spotify ID of playlist
     * @return \Cake\Http\Response|null
     */
    public function view(string $playlistId)
    {
        try{
            $playlist = $this->getApi()->getPlaylist($playlistId);
            $playlist['tracks'] = $this->getApi()->getPlaylistTracks($playlistId);
            $this->set(compact('playlist'));
        }
        catch(BadRequestException $e)
        {   
            $this->Flash->error(__("Failed to find the playlist with the given ID. Please make sure the ID is correct and the playlist is not private."));
            return $this->redirect(['action' => 'find']);
        }
    }

    /**
     * Render playlist find view
     * @return void
     */
    public function find()
    {
        $this->set('playlists', $this->getUserPlaylists());
    }


    /**
     * Get snapshot ID of a playlist
     * @param string $playlistId The Spotify ID of playlist
     * @return string
     */
    protected function getPlaylistSnapshotId(string $playlistId)
    {
        return $this->getApi()->getPlaylistSnapshotId($playlistId);
    }

    /**
     * Get a list of all playlists owned or followed by the current Spotify user.
     * 
     * @return array
     */
    protected function getUserPlaylists()
    {
        return $this->getApi()->getAllPlaylists();
    }

    /**
     * Get a list of all playlists owned by the current Spotify user.
     * 
     * @return array
     */
    protected function getUserOwnPlaylists()
    {
        return $this->getApi()->getOwnedPlaylists($this->getRequest()->getSession()->read('user')['spotify_id']);
    }

    /**
     * Get a list of tracks from a specified playlist, retrieve from cache if availabe otherwise save it for next time
     * @param string $playlistId ID of Spotify playlist
     * @param string $snapshotId Snapshot ID of Spotify playlist
     * @return array
     */
    protected function getTracksOfPlaylist(string $playlistId, string $snapshotId)
    {
        $cacheKey = $this->makeCacheKey([$this->getRequest()->getSession()->read('user')['id'], 'playlistTracks', $playlistId, $snapshotId]);
        return Cache::remember($cacheKey, function () use ($playlistId) {
            return $this->getApi()->getPlaylistTracks($playlistId, 'added_at,track(id)');
        }, '_spotify_');
    }
}