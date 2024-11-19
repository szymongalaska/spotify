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
        <div class="top-nav-links">
            <?php echo $this->element('song', ['track' => $current_song['item'], 'playing' => true]) ?>
        </div>
        <div class="top-nav-user">
            <?= $this->Html->image($this->getRequest()->getSession()->read('user')['image_url'], ['class' => 'top-nav-profile-picture']); ?>
            <span><?= h($this->getRequest()->getSession()->read('user')['display_name']); ?></span>
            <div class="top-nav-links">
                <?= $this->Html->link('<i class="fa fa-right-from-bracket"></i>', ['controller' => 'Main', 'action' => 'logout'], ['escape' => false]); ?>
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
    </footer>
</body>
</html>
