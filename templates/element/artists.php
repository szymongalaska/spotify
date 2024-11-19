<div id="artists">
    <?php foreach ($topArtists['items'] as $artist): ?>
        <?php echo $this->element('artist', ['artist' => $artist]); ?>
    <?php endforeach; ?>
</div>