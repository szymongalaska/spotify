<?php
/**
 * @var \App\View\AppView $this
 * @var array $playlist Playlist array with tracks
 */
?>
<div class="playlist">
    <div class="content">
        <?php echo $this->element('playlist-with-tracks', ['playlist' => $playlist]) ?>
    </div>
</div>