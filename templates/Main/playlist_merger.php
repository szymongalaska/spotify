<?php
/**
 * @var \App\View\AppView $this
 */
?>
<div class="playlistMerger">
    <div class="content list playlists">
        <div class="row">
            <h3><?= __('Your playlists') ?></h3>
        </div>
        <ul>
            <?php foreach ($myPlaylists as $playlist): ?>
                <li><?php echo $this->element('playlist', ['playlist' => $playlist]) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>