<?php
/**
 * @var \App\View\AppView $this
 */
$this->disableAutoLayout();
?>
<!DOCTYPE html>
<html>

<head>
    <?= $this->Html->charset() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>
        Account Manager - <?= __('Login') ?>
    </title>
    <?= $this->Html->meta('icon') ?>

    <?= $this->Html->css(['normalize.min', 'milligram.min', 'fonts', 'cake', 'home']) ?>

    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>
    <?= $this->fetch('script') ?>

    <meta name="google-site-verification" content="Pnvk-gAuRMhsPQbQnyX5oJgFyTmZCQv5FudPbTTKNgA" />
</head>

<body id="home-page">
    <main class="main">
        <div class="container text-center" style="margin-bottom: 2rem;">
            <div class="content">
                <div class="row">
                    <div class="column">
                        <p><?= __('Account Manager - check your statistics and manage account')?></p>
                        <p><small><?= __('Note: this is a project not affiliated with official Spotify.') ?></small></p>
                    </div>
                </div>
            </div>
        </div>
        <div class="container">
            <?= $this->Flash->render() ?>
            <div class="content">
                <div class="row">
                    <div class="column text-center">
                        <h3><?= __('Login to continue') ?></h3>
                    </div>
                </div>
                <div class="row">
                    <div class="column" style="display: flex; flex-direction: column; justify-content: center; align-items: center;">
                        <?php /* $this->Html->link(__('Login'), ['controller' => 'main', 'action' => 'login'], ['class' => 'button'])  */ ?>
                        <?= $this->Html->link(__('Login as test user'), ['controller' => 'main', 'action' => 'loginAsGuest'], ['class' => 'button', 'style' => 'font-size: 0.8rem']) ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>

</html>