<?php
declare(strict_types=1);

namespace App\Controller;


use Cake\Cache\Cache;

class MainController extends AppController
{
    /**
     * Request authorization from the user and redirect to corresponding page
     * 
     * @return \Cake\Http\Response|null
     */
    public function login()
    {
        // Already logged in
        if($this->getRequest()->getSession()->read('user.id'))
            return $this->redirect(['action' => 'dashboard']);

        // Approval
        if ($this->request->getQuery('code')) 
        {
            $this->getApi()->requestAccessToken($this->getRequest()->getQuery('code'));
            $this->_setUser($this->getApi()->getAccessToken(), $this->getApi()->getRefreshToken());
            

            return $this->redirect(['action' => 'dashboard']);
        }

        // Deny
        if($this->getRequest()->getQuery('error'))
        {
            $this->Flash->error(__($this->getRequest()->getQuery('error')));
            return $this->redirect(['controller' => 'Pages', 'action' => 'display', 'home']);
        }

        // Redirect to login through Spotify - returns Authorization Code
        return $this->redirect($this->getApi()->getAuthorizeUrl(['show_dialog' => true, 'scope' => ['user-top-read', 'user-read-currently-playing', 'playlist-read-private', 'playlist-modify-private', 'playlist-read-collaborative', 'playlist-modify-public', 'user-library-read']]));
    }

    /**
     * Sets user data to session
     * 
     * @param string $accessToken Access Token acquired from Spotify API
     * @param string $refreshToken Refresh Token acquired from Spotify API
     * 
     * @return \Cake\Http\Response|null
     */
    private function _setUser(string $accessToken, string $refreshToken)
    {
        $profile = $this->getApi()->getProfile();

        if($user = $this->fetchTable('Users')->findBySpotifyId($profile['id'])->first())
            $user = $this->fetchTable('Users')->updateUserTokens($user, $accessToken, $refreshToken);
        else
            $user = $this->_saveUser($profile, $accessToken, $refreshToken);
        
        if($user === false)
        {
            $this->Flash->error(__('There was an error during saving your data.'));
            return $this->redirect(['controller' => 'Pages', 'action' => 'display', 'home']);
        }

        $this->getRequest()->getSession()->write('user', $user);
    }

    /**
     * Saves user in database. Returns user or false if failed
     * 
     * @param array $profile Profile data from Spotify API
     * @param string $access_token Access Token acquired from Spotify API
     * @param string $refresh_token Referesh Token acquired from Spotify API
     * 
     * @return bool|\Cake\Datasource\EntityInterface
     */
    private function _saveUser(array $profile, string $access_token, string $refresh_token)
    {
        $user = $this->fetchTable('Users')->newEntity([
            'spotify_id' => $profile['id'],
            'display_name' => $profile['display_name'] ?? null,
            'image_url' => $profile['images'][0]['url'] ?? null,
            'access_token' => $access_token,
            'refresh_token' => $refresh_token
        ]);
        
        return $this->fetchTable('Users')->save($user);
    }
    
    /**
     * Render dashboard
     * 
     * @return \Cake\Http\Response|null
     */
    public function dashboard()
    {
        $this->set('topTracks', $this->getUserTopTracks());
        $this->set('topArtists', $this->getUserTopArtists());
    }

    /**
     * Render playlist merger
     * 
     * @return \Cake\Http\Response|null
     */
    public function playlistMerger()
    {
        $this->set('myPlaylists', $this->getUserPlaylists());
    }

    /**
     * Renders getUserTopTracks
     * @see getUserTopTracks
     * @param string $term
     * @return \Cake\Http\Response|null
     */
    public function ajaxGetTopTracks($term)
    {
        $this->set('topTracks', $this->getUserTopTracks($term));
        $this->render('/element/tracks', 'ajax');
    }

    /**
     * Get the current user's top tracks based on calculated affinity and selected time frame
     * @param string $term
     * @return array
     */
    public function getUserTopTracks($term = 'medium_term')
    {
        return $this->getApi()->getTop('tracks', $term, 5);
    }

    /**
     * Retrieve users saved tracks and save it to cache or retrieve if cache already exists
     * @return array
     */
    public function getUserSavedTracks()
    {
        return Cache::remember($this->getRequest()->getSession()->read('user')['id'].'-savedTracks', function(){
            return $this->getApi()->getAllSavedTracks();
        }, '_spotify_');
    }

    /**
     * Logout user by deleting session data
     * @return \Cake\Http\Response|null
     */
    public function logout()
    {
        $this->getRequest()->getSession()->delete('user');
        return $this->redirect(['controller' => 'Pages', 'action' => 'display', 'home']);
    }

    /**
     * Get the current user's top artists based on calculated affinity and selected time frame
     * @param string $term
     * @return array
     */
    public function getUserTopArtists($term = 'medium_term')
    {
        return $this->getApi()->getTop('artists', $term, 5);
    }

     /**
     * Renders getUserTopArtists
     * @see getUserTopArtists
     * @param string $term
     * @return \Cake\Http\Response|null
     */
    public function ajaxGetTopArtists($term)
    {
        $this->set('topArtists', $this->getUserTopArtists($term));
        $this->render('/element/artists', 'ajax');
    }

    /**
     * Renders current song element
     * @return \Cake\Http\Response|null
     */
    public function ajaxGetCurrentSong()
    {
        $this->viewBuilder()->setLayout('ajax');
        
        $currentSong = $this->getApi()->getCurrentlyPlaying();
        if($currentSong === null)
        {
            $this->autoRender = false;
            return null;
        }

        $this->set('track', $currentSong['item']);
        $this->set('playing', true);
        $this->render('/element/song');  
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