<div class="row song" data-id="<?= $track['id'] ?>">
    <?php if($track['album']['images']): ?>
    <div class="column <?php echo isset($playing) && $playing == true ? 'column-20' : 'column-10' ?>">
        <?= $this->Html->image($track['album']['images'][0]['url'], ['alt' => $track['album']['name']]) ?>
    </div>
    <?php endif; ?>
    <div class="column" style="display: flex; flex-direction: column; justify-content: center">
        <div class="row">
            <span style="font-size: 1.2rem;"><?php
            foreach ($track['artists'] as $artist):
                $artists[] = $artist['name'];
                ?>
                    <?= h(implode(' // ', $artists)); ?>
                <?php endforeach; ?>
            </span>
        </div>
        <div class="row">
            <span style="font-weight: 600; font-size: 2rem;"><?= h($track['name']) ?></span>
        </div>
    </div>
</div>