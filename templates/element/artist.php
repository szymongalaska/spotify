<div class="row artist">
    <?php if($artist['images']): ?>
    <div class="column column-10">
        <?= $this->Html->image($artist['images'][2]['url'], ['alt' => $artist['name']]) ?>
    </div>
    <?php endif; ?>
    <div class="column" style="display: flex; flex-direction: column; justify-content: center">
        <div class="row">
            <span style="font-weight: 600; font-size: 2rem;"><?= h($artist['name']) ?></span>
        </div>
    </div>
</div>