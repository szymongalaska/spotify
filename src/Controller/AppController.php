<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Controller;

use App\Utility\SpotifyApi;
use Cake\Controller\Controller;
use Cake\Cache\Cache;
use Cake\I18n\I18n;
/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 * 
 * @property \App\Controller\Component\SpotifyApiComponent $SpotifyApi
 *
 * @link https://book.cakephp.org/5/en/controllers.html#the-app-controller
 */
class AppController extends Controller
{

    /**
     * Initialization hook method.
     *
     * Use this method to add common initialization code like loading components.
     *
     * e.g. `$this->loadComponent('FormProtection');`
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('SpotifyApi', ['clientId' => env('CLIENT_ID'), 'clientSecret' => env('CLIENT_SECRET'), 'redirectUri' => env('REDIRECT_URI'), 'client' => new \Cake\Http\Client(), 'useMarket' => false]);

        // $this->_api = new SpotifyApi(env('CLIENT_ID'), env('CLIENT_SECRET'), env('REDIRECT_URI'), new \Cake\Http\Client(), false);
        $this->loadComponent('Flash');

        /*
         * Enable the following component for recommended CakePHP form protection settings.
         * see https://book.cakephp.org/5/en/controllers/components/form-protection.html
         */
        //$this->loadComponent('FormProtection');
    }

    /**
     * Reads user prefered language from browser data or session
     * @return string
     */
    private function _getLanguage()
    {
        $supportedLanguages = ['pl', 'en'];
        if(!$this->getRequest()->getSession()->check('lang'))
        {
            $languages = $this->getRequest()->getHeaderLine('Accept-Language');
            $language = substr(explode(',', $languages)[0], 0, 2);

            $language = (in_array($language, $supportedLanguages)) ? $language : 'en';
        }
        else
            $language = $this->getRequest()->getSession()->read('lang');

        return $language;
    }

    /**
     * Sets app language
     * @return void
     */
    private function _setLanguage()
    {
        $language = $this->_getLanguage();
        $this->getRequest()->getSession()->write('lang', $language);
        I18n::setLocale($language);
    }

    /**
     * 
     * @param \Cake\Event\EventInterface $event
     * @return \Cake\Http\Response|null
     */
    public function beforeFilter(\Cake\Event\EventInterface $event)
    {
        $this->_setLanguage();
        
        if(!in_array($this->getRequest()->getParam('action'), ['login', 'logout', 'cron']) && !in_array($this->getRequest()->getParam('controller'), ['Pages']))
        {
            // Redirect to login screen if not logged in
            if(!$this->getRequest()->getSession()->check('user'))
                return $this->redirect(['controller' => 'Pages', 'action' => 'display', 'home']);

            // Set tokens
            if(!$this->SpotifyApi->checkTokens())
                $this->SpotifyApi->setTokens(['access_token' => $this->getRequest()->getSession()->read('user')->access_token, 'refresh_token' => $this->getRequest()->getSession()->read('user')->refresh_token]);
        
            // Get current or last played song
            $this->set('user', $this->getRequest()->getSession()->read('user'));
            $this->set('current_song', $this->SpotifyApi->getCurrentlyPlaying());
        }
    }


    /**
     * Helper function for making cache keys
     * @param array $values Values to create key from
     * @return string
     */
    public function makeCacheKey(array $values)
    {
        return implode('-', $values);
    }

    /**
     * Retrieve users saved tracks and save it to cache or retrieve if cache already exists
     * @return array
     */
    protected function getUserSavedTracks()
    {
        $cacheKey = $this->makeCacheKey([$this->getRequest()->getSession()->read('user')['id'],'savedTracks']);
        return Cache::remember($cacheKey, function(){
            return $this->SpotifyApi->getAllSavedTracks();
        }, '_spotify_');
    }
}
