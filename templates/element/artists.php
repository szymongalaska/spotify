<?php
/**
 * @var \App\View\AppView $this
 */
?>
<div id="artists">
    <ul>
        <?php foreach ($topArtists['items'] as $artist): ?>
            <li><?php echo $this->element('artist', ['artist' => $artist]); ?></li>
        <?php endforeach; ?>
    </ul>
</div>