<?php
/**
 * @var \App\View\AppView $this
 */
?>
<div class="row playlist-header">
    <div class="column column-10">
        <?php if ($playlist['images']): ?>
            <?= $this->Html->image($playlist['images'][0]['url']) ?>
        <?php else: ?>
            <i class="fa fa-music"></i>
        <?php endif; ?>
    </div>
    <div class="column" style="display: flex;flex-direction: column;justify-content: center">
        <div class="row">
            <h3><?= h($playlist['name']) ?></h3>
        </div>
        <?php if (isset($playlist['description'])): ?>
            <div class="row">
                <span><?= h($playlist['description']) ?></span>
            </div>
        <?php endif; ?>
        <div class="row">
            <span><?= h($playlist['owner']['display_name']) ?></sap>
        </div>
    </div>
</div>
<div class="row">
    <div class="column">
        <ul>
            <?php foreach ($playlist['tracks'] as $track): ?>
                <li><?php echo $this->element('song', ['track' => $track['track']]) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>