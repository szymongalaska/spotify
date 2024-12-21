<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Cache\Cache;
use Cake\Http\Exception\BadRequestException;

class PlaylistController extends MainController
{

    /**
     * Render playlist content view
     * @param string $playlistId The Spotify ID of playlist
     * @return \Cake\Http\Response|null
     */
    public function view(string $playlistId)
    {
        try{
            $playlist = $this->SpotifyApi->getPlaylist($playlistId);
            $playlist['tracks'] = $this->SpotifyApi->getPlaylistTracks($playlistId);
            $this->set(compact('playlist'));
        }
        catch(BadRequestException $e)
        {   
            $this->Flash->error(__("Failed to find the playlist with the given ID. Please make sure the ID is correct and the playlist is not private."));
            return $this->redirect(['action' => 'find']);
        }
    }

    /**
     * Render view of playlists
     * @param string $playlistId The Spotify ID of playlist
     * @return \Cake\Http\Response|null
     */
    public function viewNotAvailable()
    {
        $this->set('playlists', $this->SpotifyApi->getAllPlaylists());
    }

    /**
     * Render view of not available tracks
     * @param string $playlistId The Spotify ID of playlist
     * @return \Cake\Http\Response|null
     */
    public function viewNotAvailableTracks(string $playlistId)
    {
        $this->SpotifyApi->setMarket(true);
        $playlist = $this->SpotifyApi->getPlaylist($playlistId);
        $tracks =  $this->SpotifyApi->getPlaylistTracks($playlistId);
        $playlist['tracks'] = $this->filterAvailableTracks($tracks);

        $this->set(compact('playlist'));
    }

    /**
     * Remove not available tracks from array of tracks
     * @param array $tracks Array of tracks
     * @return array Filtered array
     */
    protected function filterNotAvailableTracks(array $tracks)
    {
        return array_filter($tracks, function($track){

            if($track['track']['is_local'] === true)
                return true;

            return $track['track']['is_playable'];
        });
    }

    /**
     * Remove available tracks from array of tracks
     * @param array $tracks Array of tracks
     * @return array Filtered array
     */
    protected function filterAvailableTracks(array $tracks)
    {
        return array_filter($tracks, function($track){

            if($track['track']['is_local'] === true)
                return false;

            return !$track['track']['is_playable'];
        });
    }

    /**
     * Render playlist find view
     * @return void
     */
    public function find()
    {
        $this->set('playlists', $this->SpotifyApi->getAllPlaylists());
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
            return $this->SpotifyApi->getPlaylistTracks($playlistId, 'added_at,track(id)');
        }, '_spotify_');
    }

    /**
     * Create a playlist using SpotifyApi and return its ID
     * 
     * @param string $playlistName
     * @param null|string $playlistDescription
     * @param null|bool $public
     * @param null|bool $collaborative
     * 
     * @return string
     */
    protected function createPlaylist(string $playlistName, ?string $playlistDescription = null, ?bool $public = true, ?bool $collaborative = false)
    {
        $result = $this->SpotifyApi->createPlaylist($playlistName, $playlistDescription, $public, $collaborative)['id'];

        if($result)
            $this->Flash->success(__('Playlist {0} created', $playlistName));
        else
            $this->Flash->error(__('Error while creating playlist'));

        return $result;
    }
}