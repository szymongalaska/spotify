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

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @link https://book.cakephp.org/5/en/controllers.html#the-app-controller
 */
class AppController extends Controller
{
     /**
     * Spotify API Object
     * @var \App\Utility\SpotifyApi
     */
    protected $_api;
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

        $this->_api = new SpotifyApi(env('CLIENT_ID'), env('CLIENT_SECRET'), env('REDIRECT_URI'));
        $this->loadComponent('Flash');

        /*
         * Enable the following component for recommended CakePHP form protection settings.
         * see https://book.cakephp.org/5/en/controllers/components/form-protection.html
         */
        //$this->loadComponent('FormProtection');
    }

    /**
     * 
     * @param \Cake\Event\EventInterface $event
     * @return \Cake\Http\Response|null
     */
    public function beforeFilter(\Cake\Event\EventInterface $event)
    {
        if(!in_array($this->getRequest()->getParam('action'), ['login', 'logout']) && !in_array($this->getRequest()->getParam('controller'), ['Pages']))
        {
            // Redirect to login screen if not logged in
            if(!$this->getRequest()->getSession()->check('user'))
                return $this->redirect(['controller' => 'Pages', 'action' => 'display', 'home']);

            // Set tokens
            if(!$this->getApi()->checkTokens())
                $this->getApi()->setTokensOfUser($this->getRequest()->getSession()->read('user')->access_token, $this->getRequest()->getSession()->read('user')->refresh_token);
        
            // Get current or last played song
            $this->set('user', $this->getRequest()->getSession()->read('user'));
            $this->set('current_song', $this->getApi()->getCurrentlyPlaying());
        }
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
}
