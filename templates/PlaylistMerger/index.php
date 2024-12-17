<?php
/**
 * @var \App\View\AppView $this
 */
?>
<div class="playlistMerger">
    <div class="content">
        <h3><?= __('Your merged playlists') ?></h3>
        <div class="list playlists">
            <ul>
                <?php foreach ($playlists as $playlist): ?>
                    <li>
                        <div class="row">
                            <div class="column"><a
                                    href="<?= $this->Url->build(['action' => 'edit', $playlist['id']]) ?>"><?php echo $this->element('playlist', ['playlist' => $playlist['playlist']]) ?></a>
                            </div>
                            <div class="column column-10">
                                <?php if (isset($playlist['playlist_merger_cronjob'])): ?>
                                    <?= $this->Form->postLink('<span class="material-symbols-outlined delete">sync_disabled</span>', ['action' => 'deleteCronjob', $playlist['id']], ['escape' => false, 'confirm' => __('Are you sure you want to disable auto synchronization of {0}?', $playlist['playlist']['name'])]) ?>
                                <?php endif; ?>
                                <?= $this->Form->postLink('<span class="material-symbols-outlined delete">delete</span>', ['action' => 'delete', $playlist['id']], ['escape' => false, 'confirm' => __('Are you sure you want to delete merging of {0}?', $playlist['playlist']['name'])]) ?>
                            </div>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="row">
            <div class="column" style="display: flex; justify-content: center; align-items: center;">
                <?= $this->Html->link(__('Create new merge'), ['action' => 'add'], ['class' => 'button']) ?>
            </div>
        </div>
    </div>
</div>