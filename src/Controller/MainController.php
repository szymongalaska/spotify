<?php
declare(strict_types=1);

namespace App\Controller;
use ArrayIterator;
use Cake\I18n\FrozenTime;
use Cake\Cache\Cache;

class MainController extends AppController
{

    protected const array KEYS = [
        0 => 'C',
        1 => 'C#/Db',
        2 => 'D',
        3 => 'D#/Eb',
        4 => 'E',
        5 => 'F',
        6 => 'F#/Gb',
        7 => 'G',
        8 => 'G#/Ab',
        9 => 'A',
        10 => 'A#/Bb',
        11 => 'B'
    ];

    protected const array MODE = [0 => 'minor', 1 => 'major'];

    protected const array MOODS = [
        'Cheerful, joyful, light' => ['C-major', 'G-major', 'D-major', 'A-major', 'E-major'],
        'Reflective, gentle' => ['F-major', 'A#/Bb-major', 'G#/Ab-major'],
        'Melancholic, sad' => ['D-minor', 'G-minor', 'A-minor', 'E-minor'],
        'Dramatic, intense' => ['C-minor', 'F-minor', 'A#/Bb-minor', 'G#/Ab-minor'],
        'Mysterious, unsettling' => ['C#/Db-minor', 'F#/Gb-minor', 'D#/Eb-minor'],
    ];

    protected const array TEMPO = [
        0 => ['bpm' => ['min' => 0, 'max' => 60], 'tempo' => 'very slow'],
        1 => ['bpm' => ['min' => 61, 'max' => 90], 'tempo' => 'slow'],
        2 => ['bpm' => ['min' => 91, 'max' => 120], 'tempo' => 'medium'],
        3 => ['bpm' => ['min' => 121, 'max' => 140], 'tempo' => 'fast'],
        4 => ['bpm' => ['min' => 141, 'max' => 500], 'tempo' => 'very fast'],
    ];
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
            $this->getApi()->requestAccessToken($this->getRequest()->getQuery('code'));
            $this->_setUser($this->getApi()->getAccessToken(), $this->getApi()->getRefreshToken());


            return $this->redirect(['action' => 'dashboard']);
        }

        // Deny
        if ($this->getRequest()->getQuery('error')) {
            $this->Flash->error(__($this->getRequest()->getQuery('error')));
            return $this->redirect(['controller' => 'Pages', 'action' => 'display', 'home']);
        }

        // Redirect to login through Spotify - returns Authorization Code
        return $this->redirect($this->getApi()->getAuthorizeUrl(['show_dialog' => true, 'scope' => ['user-top-read', 'user-read-currently-playing', 'user-read-recently-played', 'playlist-read-private', 'playlist-modify-private', 'playlist-read-collaborative', 'playlist-modify-public', 'user-library-read']]));
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

    public function ajaxGetTodaysMood()
    {
        $this->set('todaysMood', $this->getUserTodaysMood());
        $this->render('/element/mood', 'ajax');
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

    public function test()
    {
        $this->getTodaysRecommendation();
    }

    public function getTodaysRecommendation()
    {

        $userMood = $this->getUserTodaysMood();
        $difference = abs(rand(0, 2));

        $tempoKey = $this->_findKeyOfTempo($userMood['tempo']);

        $tempo = $this->_getElementByProximity($tempoKey, self::TEMPO, $difference);
        $mood = $this->_getElementByProximity($userMood['mood'], self::MOODS, $difference);

        // Recalculate difference so key can be randomized
        if($difference == 0)
            $difference = abs(rand(0, count($mood)));

        $key = $this->_getElementByProximity(0, $mood, $difference);
        $key = $this->_convertKey($key);

        $data = ['target_key' => $key['key'], 'target_mode' => $key['mode'], 'min_tempo' => $tempo['bpm']['min'], 'max_tempo' => $tempo['bpm']['max']];
        if($data['min_tempo'] == 0)
            unset($data['min_tempo']);
        if($data['max_tempo'] == 500)
            unset($data['max_tempo']);
        
        $a = $this->getApi()->getRecommendations(['genres' => 'rock,techno,trance'], 1, $data);
        dd([$data, $a]);
    }

    /**
     * Find elements from the array based on starting key and proximity
     * 
     * @param string|int $key The base key in the array to start from
     * @param array $array Array from which elements are selected
     * @param int $proximity The number of steps to move in both directions
     * 
     * @return mixed Randomly chosen element from matching set
     */
    private function _getElementByProximity(string|int $key, array $array, int $proximity)
    {
        if(is_string($key))
            $key = array_search($key, array_keys($array));

        $iterator = new ArrayIterator($array);

        //Reverse array to iterate 'backwards'
        $reversedIterator = new ArrayIterator(array_reverse($array));

        $iterator->seek($key);

        // Return first element if 
        if($proximity == 0)
               return $iterator->current();

        $reversedIterator->seek(count($array) - 1 - $key);

        for($i = 0; $i < $proximity; $i++)
        {
            $iterator->next();
            $reversedIterator->next();

            if(!$iterator->valid())
                $iterator->rewind();

            if(!$reversedIterator->valid())
                $reversedIterator->rewind();

        }

        $elements = [$iterator->current(), $reversedIterator->current()];

        return $elements[array_rand($elements)];
    }

    /**
     * Convert key to array with values of corresponding Spotify API numbers
     * @param string $key  
     * @return array{key: int, mode: int}
     */
    private function _convertKey($key)
    {
        $key = explode('-', $key);
        return [
            'key' => array_search($key[0], self::KEYS),
            'mode' => array_search($key[1], self::MODE)
        ];
    }

    /**
     * Find key of tempo
     * @param string $tempo
     * @return bool|int|string
     */
    private function _findKeyOfTempo(string $tempo)
    {
        return array_search($tempo, array_column(self::TEMPO, 'tempo'));
    }

    /**
     * Get user's mood of todays tracks
     * 
     * @return array
     */
    public function getUserTodaysMood()
    {
        return Cache::remember($this->getRequest()->getSession()->read('user')['id'] . '-mood', function () {
            $tracks = $this->_getRecentlyPlayedTracks();
            foreach ($tracks as $track) {
                $track = $this->getApi()->getTracksAudioFeatures($track['track']['id']);
                $tempo[] = $track['tempo'];
                $keys[] = [$track['key'], $track['mode']];
            }

            $mood = $this->_calculateMood($keys);
            $tempo = array_sum($tempo) / count($tempo);
            $tempo = $this->_getTempo($tempo);

            return ['mood' => $mood, 'tempo' => $tempo];
        });
    }

    /**
     * Get speed associated with tempo
     * 
     * @param float $tempo
     * 
     * @return string|null
     */
    private function _getTempo(float $tempo)
    {
        foreach (self::TEMPO as $row) {
            if ($tempo >= $row['bpm']['min'] && $tempo <= $row['bpm']['max'])
                return $row['tempo'];
        }

        return null;
    }

    /**
     * Finds the most common music key in array
     * 
     * @param array $keys Array of music keys
     * 
     * @return bool|string[]
     */
    private function _getMostCommonKey(array $keys)
    {
        $keys = array_map(
            function ($item) {
                return $item[0] . '|' . $item[1];
            },
            $keys
        );

        $values = array_count_values($keys);
        $mostCommon = max($values);
        $keys = array_keys($values, $mostCommon);

        $keys = array_map(function ($item) {
            return explode('|', $item);
        }, $keys);

        return $keys[0];
    }

    /**
     * Calculate mood of tracks by keys
     * 
     * @param array $keys Array of music keys
     * 
     * @return string|null
     */
    private function _calculateMood(array $keys)
    {
        $key = $this->_getMostCommonKey($keys);

        $key = self::KEYS[$key[0]] . '-' . self::MODE[$key[1]];

        return $this->_getMoodByTonality($key);
    }

    /**
     * Get mood associated with key
     * 
     * @param string $tonality
     * 
     * @return string|null
     */
    private function _getMoodByTonality(string $tonality)
    {
        foreach (self::MOODS as $mood => $tonalities) {
            if (in_array($tonality, $tonalities))
                return $mood;
        }

        return null;
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

        $items = $this->getApi()->getRecentlyPlayedTracks(50, $after);

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

        $currentSong = $this->getApi()->getCurrentlyPlaying();
        if ($currentSong === null) {
            $this->autoRender = false;
            return null;
        }

        $this->set('track', $currentSong['item']);
        $this->set('playing', true);
        $this->render('/element/song');
    }
}