<?php
/**
 * @var \App\View\AppView $this
 */
?>
<div class="playlist">
    <div class="content">
        <?php echo $this->element('playlist-with-tracks', ['playlist' => $playlist]) ?>
    </div>
</div>