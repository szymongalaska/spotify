<div class="content">
    <h3><?= __('Top tracks') ?></h3><p><?= __('Last 6 months') ?></p>
    <?php foreach($topTracks['items'] as $track): ?>
        <?php echo $this->element('song', ['track' => $track]); ?>
    <?php endforeach; ?>
</div>