<div id="tracks">
    <?php foreach ($topTracks['items'] as $track): ?>
        <?php echo $this->element('song', ['track' => $track]); ?>
    <?php endforeach; ?>
</div>