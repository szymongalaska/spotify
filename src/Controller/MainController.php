<?php
declare(strict_types=1);

namespace App\Controller;
use Cake\I18n\FrozenTime;
use App\Service\TrackService;
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
        if ($this->getRequest()->getSession()->read('user.id'))
            return $this->redirect(['action' => 'dashboard']);

        // Approval
        if ($this->request->getQuery('code')) {
            $this->SpotifyApi->setTokensByCode($this->getRequest()->getQuery('code'));
            $this->setUser($this->SpotifyApi->getAccessToken(), $this->SpotifyApi->getRefreshToken());


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

    public function loginAsGuest()
    {
        $user = $this->fetchTable('Users')->findByDisplayName('TEST ACCOUNT')->first();
        $this->getRequest()->getSession()->write('user', $user);
        return $this->redirect(['action' => 'dashboard']);
    }

    /**
     * Sets user data to session
     * 
     * @param string $accessToken Access Token acquired from Spotify API
     * @param string $refreshToken Refresh Token acquired from Spotify API
     * 
     * @return \Cake\Http\Response|null
     */
    protected function setUser(string $accessToken, string $refreshToken)
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

    /**
     * Get unavailable tracks from users library, store it to cache and then return
     * @return array
     */
    protected function GetUnvailableTracksFromLibrary()
    {
        $trackService = new TrackService();

        $allTracks = $this->getUserSavedTracks();
        $unavailableTracks = $trackService->filterAvailableTracks($allTracks);

        $cacheKey = $this->makeCacheKey([$this->getRequest()->getSession()->read('user')['id'], 'UnvailableTracks']);
        Cache::write($cacheKey, json_encode($unavailableTracks), '_spotify_');

        return $unavailableTracks;
    }

    /**
     * Compare previously cached unavailable tracks with freshly fetched and store the difference in a cache
     * @return void
     */
    protected function FilterNewlyAvailableAndUnavailableTracks()
    {
        $cacheKey = $this->makeCacheKey([$this->getRequest()->getSession()->read('user')['id'], 'UnvailableTracks']);
        $unavailableTracks = json_decode(Cache::read($cacheKey));

        $newUnavailableTracks = $this->GetUnvailableTracksFromLibrary();

        if (!$unavailableTracks) {
            $availableTracks = [];
            $unavailableTracks = $newUnavailableTracks;
        } else {
            $availableTracks = array_diff($unavailableTracks, $newUnavailableTracks);
            $unavailableTracks = array_diff($newUnavailableTracks, $unavailableTracks);
        }

        $cacheKey = $this->makeCacheKey([$this->getRequest()->getSession()->read('user')['id'], 'AvailableAndUnavailableTracksFromLibrary']);
        Cache::write($cacheKey, json_encode([
            'availableTracks' => $availableTracks,
            'unavailableTracks' => $unavailableTracks,
        ]), '_spotify_');
    }

    /**
     * Cronjob that runs `FilterNewlyAvailableAndUnavailableTracks`
     * @see MainController::FilterNewlyAvailableAndUnavailableTracks
     * @return \Cake\Http\Response
     */
    public function cronAvailableAndUnavailableTracks()
    {
        $this->autoRender = false;

        if ($this->getRequest()->getQuery('token') !== 'qM7MO43sqOfYRFcDKlBwXuMRxFcTv0RTeURD1iFm0p5Lb7k3f0Fbx2jB8hAMGTX0')
            return $this->getResponse()->withStatus(401);

        $users = $this->fetchTable('Users')->find('all');

        /** @var \App\Model\Entity\User $user */
        foreach ($users as $user) {
            if (!$user->playlist_merger)
                continue;

            $this->SpotifyApi->setTokens(['access_token' => $user->access_token, 'refresh_token' => $user->refresh_token]);
            $this->getRequest()->getSession()->write('user', $user);

            $this->FilterNewlyAvailableAndUnavailableTracks();
        }

        $this->getRequest()->getSession()->delete('user');

        return $this->response->withStatus(200);
    }

    /**
     * Render newly available and unavilable tracks from users library
     * @return void
     */
    public function NewlyUnavailableAndUnavailableTracks()
    {
        $cacheKey = $this->makeCacheKey([$this->getRequest()->getSession()->read('user')['id'], 'AvailableAndUnavailableTracksFromLibrary']);
        $this->set('tracks', json_decode(Cache::read($cacheKey)));
    }
}