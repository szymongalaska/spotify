<?php
declare(strict_types=1);

namespace App\Controller;

use App\Utility\SpotifyApi;

class MainController extends AppController
{
    private $_api;

    public function initialize(): void
    {
        parent::initialize();
        $this->_api = new SpotifyApi(env('CLIENT_ID'), env('CLIENT_SECRET'), env('REDIRECT_URI'));
    }
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
        return $this->redirect($this->getApi()->getAuthorizeUrl(['show_dialog' => true, 'scope' => ['playlist-read-private', 'playlist-modify-private', 'playlist-read-collaborative', 'playlist-modify-public', 'user-library-read']]));
    }

    /**
     * Get API Object
     * 
     * @return SpotifyApi
     */
    public function getApi()
    {
        return $this->_api;
    }

    /**
     * Sets user data to session
     * 
     * @param string $accessToken Access Token acquired from Spotify API
     * @param string $refreshToken Refresh Token acquired from Spotify API
     * 
     * @return bool|\Cake\Http\Response|null
     */
    private function _setUser(string $accessToken, string $refreshToken)
    {
        $profile = $this->getApi()->getProfile();
        $user = $this->fetchTable('Users')->findBySpotifyId($profile['id'])->first() ?? $this->_saveUser($profile, $accessToken, $refreshToken);
        
        if($user === false)
        {
            $this->Flash->error(__('There was an error during saving your data.'));
            return $this->redirect(['controller' => 'Pages', 'action' => 'display', 'home']);
        }

        $this->getRequest()->getSession()->write('user', $user);

        return true;
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
    

}