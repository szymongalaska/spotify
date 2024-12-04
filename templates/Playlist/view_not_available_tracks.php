<?php
/**
 * @var \App\View\AppView $this
 * @var array $playlist Playlist array with tracks
 */
?>
<div class="playlist">
    <div class="content">
            <h3><?= __('Unavailable tracks in playlist') ?>: <?= count($playlist['tracks']) ?></h3>
        <?php echo $this->element('playlist-with-tracks', ['playlist' => $playlist]) ?>
    </div>
</div>