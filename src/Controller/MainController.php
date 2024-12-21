<?php
declare(strict_types=1);

namespace App\Controller;
use Cake\I18n\FrozenTime;

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
        if ($this->getRequest()->getSession()->read('user.id'))
            return $this->redirect(['action' => 'dashboard']);

        // Approval
        if ($this->request->getQuery('code')) {
            $this->SpotifyApi->setTokensByCode($this->getRequest()->getQuery('code'));
            $this->_setUser($this->SpotifyApi->getAccessToken(), $this->SpotifyApi->getRefreshToken());


            return $this->redirect(['action' => 'dashboard']);
        }

        // Deny
        if ($this->getRequest()->getQuery('error')) {
            $this->Flash->error(__($this->getRequest()->getQuery('error')));
            return $this->redirect(['controller' => 'Pages', 'action' => 'display', 'home']);
        }

        // Redirect to login through Spotify - returns Authorization Code
        return $this->redirect($this->SpotifyApi->getAuthorizeUrl(['show_dialog' => env('SHOW_DIALOG', null), 'scope' => ['user-top-read', 'user-read-currently-playing', 'user-read-recently-played', 'playlist-read-private', 'playlist-modify-private', 'playlist-read-collaborative', 'playlist-modify-public', 'user-library-read']]));
    }

    /**
     * Sets user data to session
     * 
     * @param string $accessToken Access Token acquired from Spotify API
     * @param string $refreshToken Refresh Token acquired from Spotify API
     * 
     * @return \Cake\Http\Response|null
     */
    protected function _setUser(string $accessToken, string $refreshToken)
    {
        $profile = $this->SpotifyApi->getProfile();

        if ($user = $this->fetchTable('Users')->findBySpotifyId($profile['id'])->first())
            $user = $this->fetchTable('Users')->updateUserTokens($user, $accessToken, $refreshToken);
        else
            $user = $this->_saveUser($profile, $accessToken, $refreshToken);

        if ($user === false) {
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
        return $this->SpotifyApi->getTop('tracks', $term, 5);
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
        return $this->SpotifyApi->getTop('artists', $term, 5);
    }

    /**
     * Get tracks that have been played today by user
     * 
     * @return array
     */
    private function _getRecentlyPlayedTracks()
    {
        $date = new FrozenTime();
        $date = $date->startOfDay();

        $after = (int) $date->toUnixString();

        $items = $this->SpotifyApi->getRecentlyPlayedTracks(50, $after);

        return array_filter($items, function ($item) use ($date) {
            $playedAt = new FrozenTime($item['played_at']);
            if ($date->isSameDay($playedAt))
                return true;
        });

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

        $currentSong = $this->SpotifyApi->getCurrentlyPlaying();
        if ($currentSong === null) {
            $this->autoRender = false;
            return null;
        }

        $this->set('track', $currentSong['item']);
        $this->set('playing', true);
        $this->render('/element/song');
    }
}