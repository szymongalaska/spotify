<?php
/**
 * @var \App\View\AppView $this
 */
?>
<div class="row song <?php if(isset($track['is_playable']) && $track['is_playable'] == false) : ?>unavailable<?php endif; ?>" data-id="<?= $track['id'] ?>">
    <div class="image-column column <?php echo isset($playing) && $playing == true ? 'column-20' : 'column-10' ?>">
    <?php if($track['album']['images']): ?>
        <?= $this->Html->image($track['album']['images'][0]['url'], ['alt' => $track['album']['name']]) ?>
    <?php else: ?>
        <span class="material-symbols-outlined library-music">play_circle</span>
    <?php endif; ?>
    </div>
    <div class="column" style="display: flex; flex-direction: column; justify-content: center">
        <div class="row">
            <span class="artist-name"><?php
            foreach ($track['artists'] as $artist):
                $artists[] = $artist['name'];
                ?>
                    <?= h(implode(' // ', $artists)); ?>
                <?php endforeach; ?>
            </span>
        </div>
        <div class="row">
            <span class="track-name"><?= h($track['name']) ?></span>
        </div>
    </div>
</div>