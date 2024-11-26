<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Cache\Cache;
use Cake\I18n\FrozenTime;

/**
 * @param \App\Model\Table\PlaylistMergerTable $PlaylistMerger
 */
class PlaylistMergerController extends PlaylistController
{
    /**
     * Render playlist merger
     * 
     * @return \Cake\Http\Response|null
     */
    public function index()
    {
        if($entity = $this->PlaylistMerger->findByUserId($this->getRequest()->getSession()->read('user')['id'])->first())
        {
            $entity->source_playlists = json_decode($entity->source_playlists, true);
            $entity->options = json_decode($entity->options, true);
            $this->set('userSavedData', $entity);
        }
            
        $this->set('myPlaylists', $this->getUserPlaylists());
        $this->set('myOwnPlaylists', $this->getUserOwnPlaylists());
    }

    /**
     * Save form data and merge playlists
     * @return \Cake\Http\Response|null
     */
    public function saveAndMerge()
    {
        $options = [
            'prepend' => $this->getRequest()->getData('prepend'),
            'savedTracks' => $this->getRequest()->getData('savedTracks'),
        ];

        $entity = $this->_saveEntity($this->getRequest()->getData('source-playlists'), $this->getRequest()->getData('target-playlist'), $options);
        
        if(!$entity)
        {
            $this->Flash->error(__('There was an error while saving playlists'));
            return $this->redirect(['action' => 'index']);  
        }

        $this->_mergePlaylists($entity);
        
        return $this->redirect(['controller' => 'Main', 'action' => 'dashboard']);
    }

    /**
     * Synchronie(merge) current user playlists
     * @return \Cake\Http\Response|null
     */
    public function synchronize()
    {
        $entity = $this->PlaylistMerger->findByUserId($this->getRequest()->getSession()->read('user')['id'])->first();
        
        if($entity)
            $this->_mergePlaylists($entity);
            
        return $this->redirect($this->getRequest()->referer());
    }

    /**
     * Merge playlists
     * @param \App\Model\Entity\PlaylistMerger $entity Entity data with source and target playlists
     * @return void
     */
    private function _mergePlaylists(\App\Model\Entity\PlaylistMerger $entity)
    { 
        $options = json_decode($entity->options, true);

        $sourceTracks = $this->_getTracksOfSourcePlaylists(json_decode($entity->source_playlists, true));
        $targetPlaylistTracks = $this->getTracksOfPlaylist($entity->target_playlist_id, $this->getPlaylistSnapshotId($entity->target_playlist_id));
        
        // Get IDs of both arrays
        $targetPlaylistTracksIds = array_column(array_column($targetPlaylistTracks, 'track'), 'id');
        $sourceTracksIds = array_column(array_column($sourceTracks, 'track'), 'id');

        // Filter both arrays so they only contain IDs to add/delete
        $tracksToAdd = array_filter($sourceTracks, function($track) use ($targetPlaylistTracksIds){
            return !in_array($track['track']['id'], $targetPlaylistTracksIds);
        });

        $tracksToRemove = array_filter($targetPlaylistTracks, function($track) use($sourceTracksIds){
            if($track['track']['id'] !== null)
                return !in_array($track['track']['id'], $sourceTracksIds);
        });

        // Remove/add tracks that are not in Saved Tracks
        if($options['savedTracks'] == true){
            $savedTracks = $this->getUserSavedTracks(); 
            $savedTracksIds = array_column(array_column($savedTracks, 'track'),'id');

            $tracksToAdd = array_filter($tracksToAdd, function($track) use ($savedTracksIds){
                    return in_array($track['track']['id'], $savedTracksIds);
            });

            $tracksToRemove = array_merge($tracksToRemove, array_filter($targetPlaylistTracks, function($track) use ($savedTracksIds){
                if($track['track']['id'] !== null)
                    return !in_array($track['track']['id'], $savedTracksIds);
            }));
        }

        if(!empty($tracksToAdd))
        {
            if($options['prepend'] == true)
                $tracksToAdd = $this->_sortTracks($tracksToAdd);

            $resultAdd = $this->_addTracksToPlaylist($entity->target_playlist_id, $tracksToAdd);
        }
        else
            $resultAdd = true;

        if(!empty($tracksToRemove))
            $resultRemove = $this->_deleteTracksFromPlaylist($entity->target_playlist_id, $tracksToRemove);
        else
            $resultRemove = true;


        if(empty($tracksToAdd) && empty($tracksToRemove))
            $this->Flash->info('Playlists are already merged');
        else if(!$resultAdd || !$resultRemove)
            $this->Flash->error(__('There was a problem while merging playlists'));
        else
            $this->Flash->success(__('Playlists merged successfuly'));
    }

    /**
     * Convert array of tracks to format accepted in Spotify API
     * @param array $tracks
     * @return string[]
     */
    private function _prepareTracksForRequest(array $tracks)
    {
        return array_map(function($item){
            return 'spotify:track:'.$item['track']['id'];
        }, $tracks);
    }

    /**
     * Sort tracks so they can be prepend
     * @param array $tracks Array of Tracks
     * @return array
     */
    private function _sortTracks(array $tracks)
    {
        $tracksChunk = array_chunk($tracks, 100);
        $tracks = [];

        foreach($tracksChunk as $chunk)
        {
            usort($chunk, function($a, $b){
                return new FrozenTime($b['added_at']) <=> new FrozenTime($a['added_at']);
            });

            $tracks = array_merge($tracks, $chunk);
        }
        
        return $tracks;
    }

    /**
     * Add tracks to playlist using Spotify API
     * @param string $playlistId The Spotify ID of the playlist.
     * @param array $tracksToAdd Array of track URIs
     * @return bool|null
     */
    private function _addTracksToPlaylist(string $playlistId, array $tracksToAdd)
    {
        return $this->getApi()->addTracksToPlaylist($playlistId, $this->_prepareTracksForRequest($tracksToAdd), true);
    }

    /**
     * Delete tracks from playlist using Spotify API
     * @param string $playlistId The Spotify ID of the playlist.
     * @param array $tracksToRemove Array of track URIs
     * @return bool|null
     */
    private function _deleteTracksFromPlaylist(string $playlistId, array $tracksToRemove)
    {
        return $this->getApi()->deleteTracksFromPlaylist($playlistId, $this->_prepareTracksForRequest($tracksToRemove));
    }

    /**
     * Merges tracks from all source playlists into one array
     * @param array $sourcePlaylists Array of source playlists
     * @return array
     */
    private function _getTracksOfSourcePlaylists(array $sourcePlaylists)
    {
        $tracks = [];

        foreach($sourcePlaylists as $playlist)
        {
            $tracks = array_merge($tracks, $this->getTracksOfPlaylist($playlist, $this->getPlaylistSnapshotId($playlist)));
        }

        // Sort all tracks by add date ascending
        usort($tracks, function($a, $b){
            return new FrozenTime($a['added_at']) <=> new FrozenTime($b['added_at']);
        });
        
        
        // Remove duplicates
        $tracks = array_reduce($tracks, function ($carry, $item) {
            if (isset($item['track']['id']) && $item['track']['id'] !== null && !isset($carry[$item['track']['id']])) {
                $carry[$item['track']['id']] = $item;
            }
            return $carry;
        }, []);
        
        return array_values($tracks);
    }

    /**
     * Saves data of playlists to database
     * @param array[string] $sourcePlaylists Array with IDs of source playlists
     * @param string $targetPlaylist ID of target playlist
     * @param array $options
     * @return mixed
     */
    private function _saveEntity(array $sourcePlaylists, string $targetPlaylist, array $options)
    {
        $entity = $this->PlaylistMerger->findByUserId($this->getRequest()->getSession()->read('user')['id'])->first() ?? $this->PlaylistMerger->newEmptyEntity();

        $data = [
            'user_id' => $this->getRequest()->getSession()->read('user')['id'],
            'source_playlists' => json_encode($sourcePlaylists),
            'target_playlist_id' => $targetPlaylist,
            'options' => json_encode($options),
        ];

        $entity = $this->PlaylistMerger->patchEntity($entity, $data);
        return $this->PlaylistMerger->save($entity);
    }

    /**
     * Prepares an array of source playlists to save
     * @param array $sourcePlaylists Array of source playlists ids
     * @return array{playlist_id: string, snapshot_id: string}
     */
    private function _prepareSourcePlaylists(array $sourcePlaylists)
    {
        return array_map(function($playlist){
            return ['playlist_id' => $playlist, 'snapshot_id' => $this->getPlaylistSnapshotId($playlist)];
        }, $sourcePlaylists);
    }
}