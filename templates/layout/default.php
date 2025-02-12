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
    <meta name="google-site-verification" content="Pnvk-gAuRMhsPQbQnyX5oJgFyTmZCQv5FudPbTTKNgA" />
</head>
<body>
    <div class="top-nav-links mobile-menu mobile">
        <?= $this->Html->link('<span class="material-symbols-outlined">arrow_menu_close</span>'.__('Hide menu'), '#', ['class' => 'mobile-menu-hide', 'escape' => false]) ?>
        <?php echo $this->element('menu') ?>
    </div>
    <nav class="top-nav">
        <div class="top-nav-links mobile">
            <?= $this->Html->link('<span class="material-symbols-outlined">menu</span>Menu', '#', ['class' => 'mobile-menu-show', 'escape' => false]) ?>
        </div>
        <div id="current-song" class="desktop">
            <?php if($current_song): ?>
                <?php echo $this->element('song', ['track' => $current_song['item'], 'playing' => true]) ?>
            <?php endif; ?>
        </div>
        <div class="top-nav-links desktop">
           <?php echo $this->element('menu') ?>
        </div>
        <div class="top-nav-user">
                <?php if($this->getRequest()->getSession()->read('user')['image_url']): ?>
                    <?= $this->Html->image($this->getRequest()->getSession()->read('user')['image_url'], ['class' => 'top-nav-profile-picture']); ?>
                <?php else: ?>
                    <span class="top-nav-profile-picture material-symbols-outlined">person</span>
                <?php endif; ?>
                <span><?= h($this->getRequest()->getSession()->read('user')['display_name']); ?></span>
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

                $(function(){
                    $('a:not([href="#"])').on('click', function(){
                        loader('body');
                    });

                    $('form').on('submit', function(){
                        loader('body');
                    });

                    $('a.mobile-menu-show').on('click', function(){
                        $('div.mobile-menu').show();
                        $('div.mobile-menu').animate({
                            width: '80%',
                        }, 200);
                    });

                    $('a.mobile-menu-hide').on('click', function(){
                        $('div.mobile-menu').animate({
                            width: 0,
                        }, 200, function(){
                            $(this).hide();
                        });
                    });
                })
        </script>
    </footer>
</body>
</html>
