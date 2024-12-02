<?php
/**
 * @var \App\View\AppView $this
 */
?>
<div class="row playlist" <?php if(isset($playlist['id'])): ?>data-id="<?= $playlist['id'] ?>"<?php endif; ?>>
    <div class="image-column column <?php echo $column ?? 'column-25'?>">
        <?php if ($playlist['images']): ?>
            <?= $this->Html->image($playlist['images'][0]['url'], ['alt' => $playlist['name']]) ?>
        <?php else: ?>
            <span class="material-symbols-outlined library-music">library_music</span>
        <?php endif; ?>
    </div>
    <div class="column" style="display: flex; flex-direction: column; justify-content: center">
        <div class="row">
            <span style="font-size: 1.2rem;"><?= h($playlist['description']) ?></span>
        </div>
        <div class="row">
            <span style="font-weight: 600; font-size: 2rem;"><?= h($playlist['name']) ?></span>
        </div>
    </div>
</div>