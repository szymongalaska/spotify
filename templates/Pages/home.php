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
        Spotify TEST APP - <?= __('Login') ?>
    </title>
    <?= $this->Html->meta('icon') ?>

    <?= $this->Html->css(['normalize.min', 'milligram.min', 'fonts', 'cake', 'home']) ?>

    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>
    <?= $this->fetch('script') ?>
</head>

<body id="home-page">
    <header>
        <div class="container text-center">
            <img alt="Spotify Logo"
                src="https://storage.googleapis.com/pr-newsroom-wp/1/2023/05/Spotify_Full_Logo_RGB_Green.png"
                width="350" />
            <h1>
            </h1>
        </div>
    </header>
    <main class="main">
        <div class="container text-center" style="margin-bottom: 2rem;">
            <div class="content">
                <div class="row">
                    <div class="column">
                        <p>Aplikacja pozwoli ci na sprawdzenie swoich statystyk oraz zarządzanie profilem Spotify.</p>
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
                    <div class="column" style="display: flex; justify-content: center; align-items: center;">
                        <?= $this->Html->link(__('Login'), ['controller' => 'main', 'action' => 'login'], ['class' => 'button']) ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>

</html>