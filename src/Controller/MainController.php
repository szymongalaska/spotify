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
        // Approval
        if ($this->request->getQuery('code')) 
        {
            $this->getApi()->requestAccessToken($this->getRequest()->getQuery('code'));
            $this->saveUser($this->getApi()->getAccessToken(), $this->getApi()->getRefreshToken());
            

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

    private function saveUser(string $accessToken, string $refreshToken)
    {
        dd($this->getApi()->getProfile());
    }
}