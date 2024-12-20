<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Cache\Cache;
use Cake\I18n\FrozenTime;

/**
 * @property \App\Model\Table\PlaylistMergerTable $PlaylistMerger
 */
class PlaylistMergerController extends PlaylistController
{
    /**
     * Render playlist merger edit view
     * 
     * @return \Cake\Http\Response|null
     */
    public function edit(int $id)
    {
        if(!$entity = $this->_getEntity($id))
        {
            $this->Flash->error(__('Access denied'));
            return $this->redirect(['action' => 'index']);
        }
            $entity->source_playlists = json_decode($entity->source_playlists, true);
            $entity->options = json_decode($entity->options, true);
            $this->set('userSavedData', $entity);
            $this->set('myPlaylists', $this->SpotifyApi->getAllPlaylists());
            $this->set('myOwnPlaylists', $this->getUserOwnPlaylists());
            $this->set('frequency', $entity->playlist_merger_cronjob->frequency ?? null);
    }

    /**
     * Render playlist merger add view
     * 
     * @return \Cake\Http\Response|null
     */
    public function add()
    {
        $this->set('myPlaylists', $this->SpotifyApi->getAllPlaylists());
        $this->set('myOwnPlaylists', $this->getUserOwnPlaylists());
    }

    /**
     * Get merge entity and check if it belongs to current user
     * @param int $id
     * @return \App\Model\Entity\PlaylistMerger|null
     */
    private function _getEntity(int $id)
    {
        $entity = $this->PlaylistMerger->get($id, ['contain' => 'PlaylistMergerCronjobs']);
        if($entity->user_id === $this->getRequest()->getSession()->read('user')['id'])
            return $entity;
        else
            return null;
    }

    /**
     * Render index view
     * @return void
     */
    public function index()
    {
        $playlists = $this->PlaylistMerger->findByUserId($this->getRequest()->getSession()->read('user')['id'])->select(['id', 'target_playlist_id', 'PlaylistMergerCronjobs.id'])->contain(['PlaylistMergerCronjobs']);

        $playlists = array_map( function($playlist){
            return [
                'id' => $playlist->id,
                'playlist' => $this->SpotifyApi->getPlaylist($playlist->target_playlist_id),
                'playlist_merger_cronjob' => $playlist->playlist_merger_cronjob
            ];
        }, $playlists->toArray());

        $this->set(compact('playlists'));
    }

    /**
     * Delete entity
     * @param int $id
     * @return \Cake\Http\Response|null
     */
    public function delete(int $id)
    {
        if(!$entity = $this->_getEntity($id))
            $this->Flash->error(__('Access denied'));
            
        else
        {
            if($this->PlaylistMerger->delete($entity))
                $this->Flash->success(__('Merge deleted'));
            else
                $this->Flash->error(__('An error occured while deleting'));
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Delete cronjob entity
     * @param int $id
     * @return \Cake\Http\Response|null
     */
    public function deleteCronjob(int $id)
    {
        if (!$entity = $this->_getEntity($id))
            $this->Flash->error(__('Access denied'));
        else {
            if ($this->PlaylistMerger->PlaylistMergerCronjobs->delete($entity->playlist_merger_cronjob))
                $this->Flash->success(__('Auto synchronization disabled'));
            else
                $this->Flash->error(__('An error occured while disabling auto synchronization'));
        }

        return $this->redirect(['action' => 'index']);
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

        $frequency = $this->getRequest()->getData('enable_synchronization') == true ? $this->getRequest()->getData('playlist_merger_cronjob')['frequency'] : null;

        $entity = $this->_saveEntity($this->getRequest()->getData('source-playlists'), $this->getRequest()->getData('target-playlist'), $options, (int) $this->getRequest()->getData('entity-id'), $frequency);
        
        if(!$entity)
        {
            $this->Flash->error(__('An error occured while saving playlists'));
            return $this->redirect(['action' => 'index']);  
        }

        $this->_mergePlaylists($entity);
        
        return $this->redirect(['action' => 'index']);
    }

    /**
     * Synchronie(merge) current user playlists
     * @return \Cake\Http\Response|null
     */
    public function synchronize()
    {
        $entities = $this->PlaylistMerger->findByUserId($this->getRequest()->getSession()->read('user')['id']);
        
        if($entities->count() > 0)
        {
            foreach($entities as $entity)
                $this->_mergePlaylists($entity);
        }
        else
            $this->Flash->error(__('Nothing to synchronize'));
            
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
        $playlist = $this->SpotifyApi->getPlaylist($entity->target_playlist_id);

        $sourceTracks = $this->_getTracksOfSourcePlaylists(json_decode($entity->source_playlists, true));
        $targetPlaylistTracks = $this->getTracksOfPlaylist($entity->target_playlist_id, $this->SpotifyApi->getPlaylistSnapshotId($entity->target_playlist_id));
        
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
            $this->Flash->info(__('Playlist {0} is already merged', $playlist['name']));
        else if(!$resultAdd || !$resultRemove)
            $this->Flash->error(__('There was a problem while merging playlist {0}', $playlist['name']));
        else
            $this->Flash->success(__('{0} merged successfuly', $playlist['name']));
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
        return $this->SpotifyApi->addTracksToPlaylist($playlistId, $this->_prepareTracksForRequest($tracksToAdd), true);
    }

    /**
     * Delete tracks from playlist using Spotify API
     * @param string $playlistId The Spotify ID of the playlist.
     * @param array $tracksToRemove Array of track URIs
     * @return bool|null
     */
    private function _deleteTracksFromPlaylist(string $playlistId, array $tracksToRemove)
    {
        return $this->SpotifyApi->deleteTracksFromPlaylist($playlistId, $this->_prepareTracksForRequest($tracksToRemove));
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
            $tracks = array_merge($tracks, $this->getTracksOfPlaylist($playlist, $this->SpotifyApi->getPlaylistSnapshotId($playlist)));
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
     * @param null|int $entityId ID of entity to edit
     * @param null|string $cronFrequency Frequency of cronjob
     * @return mixed
     */
    private function _saveEntity(array $sourcePlaylists, string $targetPlaylist, array $options, ?int $entityId = null, ?string $cronFrequency = null)
    {
        $entity = $entityId ? $this->PlaylistMerger->get($entityId, ['contain' => 'PlaylistMergerCronjobs']) : $this->PlaylistMerger->newEmptyEntity();
        $associated = [];

        
        $data = [
            'user_id' => $this->getRequest()->getSession()->read('user')['id'],
            'source_playlists' => json_encode($sourcePlaylists),
            'target_playlist_id' => $targetPlaylist,
            'options' => json_encode($options),
        ];

        if($cronFrequency){
            $associated = ['PlaylistMergerCronjobs'];
            $data['playlist_merger_cronjob'] = ['frequency' => $cronFrequency];
        }

        $entity = $this->PlaylistMerger->patchEntity($entity, $data, ['associated' => $associated]);
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
            return ['playlist_id' => $playlist, 'snapshot_id' => $this->SpotifyApi->getPlaylistSnapshotId($playlist)];
        }, $sourcePlaylists);
    }

    /**
     * Cronjob to synchronize playlists
     * @param string $frequency Can be:
     * - weekly
     * - once_daily
     * - twice_daily
     * - four_times_daily
     * 
     * @return \Cake\Http\Response
     */
    public function cron(string $frequency)
    {
        $this->autoRender = false;

        if($this->getRequest()->getQuery('token') !== 'qM7MO43sqOfYRFcDKlBwXuMRxFcTv0RTeURD1iFm0p5Lb7k3f0Fbx2jB8hAMGTX0')
            return $this->response->withStatus(401);

        if(!in_array($frequency, ['weekly', 'once_daily', 'twice_daily', 'four_times_daily']))
            return $this->response->withStatus(400);

        $users = $this->fetchTable('Users')->find('all')->contain(['PlaylistMerger' => ['PlaylistMergerCronjobs']])->matching('PlaylistMerger.PlaylistMergerCronjobs', function ($query) use($frequency){return $query->where(['frequency' => $frequency]);});

        foreach($users as $user)
        {
            if(!$user->playlist_merger)
                continue; 

            $this->SpotifyApi->setTokens(['access_token' => $user->access_token, 'refresh_token' => $user->refresh_token]);
            $this->getRequest()->getSession()->write('user', $user);

            foreach($user->playlist_merger as $entity)
            {
                $this->_mergePlaylists($entity);
            }
        }

        $this->getRequest()->getSession()->delete('user');

        return $this->response->withStatus(200);
    }

}