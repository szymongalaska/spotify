<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Cache\Cache;

class PlaylistController extends AppController
{

    /**
     * View playlist
     * @param string $playlistId The Spotify ID of playlist
     * @return \Cake\Http\Response|null
     */
    public function view(string $playlistId)
    {
        $playlist = $this->getApi()->getPlaylist($playlistId);
        $playlist['tracks'] = $this->getApi()->getPlaylistTracks($playlistId);
        $this->set(compact('playlist'));
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