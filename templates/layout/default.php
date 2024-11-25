<?php
/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @since         0.10.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @var \App\View\AppView $this
 */
?>
<!DOCTYPE html>
<html>
<head>
    <?= $this->Html->charset() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>
        <?= __('Hello, {0}', $user['display_name']); ?>
    </title>
    <?= $this->Html->meta('icon') ?>

    <?= $this->Html->css(['normalize.min', 'milligram.min', 'fonts', 'cake', 'all']) ?>
    <?= $this->Html->script("https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js") ?>
    <?= $this->Html->script('scripts') ?>

    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>
    <?= $this->fetch('script') ?>
</head>
<body>
    <nav class="top-nav">
        <div id="current-song">
            <?php if($current_song): ?>
                <?php echo $this->element('song', ['track' => $current_song['item'], 'playing' => true]) ?>
            <?php endif; ?>
        </div>
        <div class="top-nav-links">
            <?= $this->Html->link('<span class="material-symbols-outlined">home</span>', ['controller' => 'Main', 'action' => 'dashboard'], ['escape' => false]) ?>
            <?= $this->Html->link('<span class="material-symbols-outlined">queue_music</span>', ['controller' => 'Playlist', 'action' => 'find'], ['escape' => false]) ?>
        </div>
        <div class="top-nav-user">
                <?= $this->Html->image($this->getRequest()->getSession()->read('user')['image_url'], ['class' => 'top-nav-profile-picture']); ?>
                <span><?= h($this->getRequest()->getSession()->read('user')['display_name']); ?></span>
            <div class="top-nav-links">
                <?= $this->Html->link('<span class="material-symbols-outlined">logout</span>', ['controller' => 'Main', 'action' => 'logout'], ['escape' => false]); ?>
            </div>
        </div>
    </nav>
    <main class="main">
        <div class="container">
            <?= $this->Flash->render() ?>
            <?= $this->fetch('content') ?>
        </div>
    </main>
    <footer>
        <script>
                setInterval(function(){
                    $.ajax({
                        'method': 'GET',
                        'url': '<?= $this->Url->build(['controller' => 'Main', 'action' => 'ajaxGetCurrentSong']) ?>',
                        success: function(response)
                        {
                            if($('div#current-song').children().length == 0 && response !== '')
                            {
                                $('div#current-song').append(response);
                            }
                            else if($('nav.top-nav div.row.song').data('id') !== $(response).data('id'))
                            {
                                $('nav.top-nav div.row.song').fadeOut(400, function(){
                                    $(this).replaceWith(response);
                                });
                            }
                        }
                    });
                }, 10000);
        </script>
    </footer>
</body>
</html>
