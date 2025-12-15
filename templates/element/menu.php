<?php
/**
 * @var \App\View\AppView $this
 */
?>
<?= $this->Html->link('<span class="material-symbols-outlined">home</span>' . __('Home'), ['controller' => 'Main', 'action' => 'dashboard'], ['escape' => false]) ?>
<div class="dropdown">
    <?= $this->Html->link('<span class="material-symbols-outlined">library_music</span>' . __('Library'), '#', ['escape' => false]) ?>
    <div class="dropdown-menu">
        <?= $this->Html->link('<span class="material-symbols-outlined">compare_arrows</span>' . __('New and unavailable'), ['controller' => 'Main', 'action' => 'NewlyUnavailableAndUnavailableTracks'], ['escape' => false]); ?>
    </div>
</div>
<div class="dropdown">
    <?= $this->Html->link('<span class="material-symbols-outlined">queue_music</span>' . __('Playlists'), '#', ['escape' => false]) ?>
    <div class="dropdown-menu">
        <?= $this->Html->link('<span class="material-symbols-outlined">preview</span>' . __('View playlist'), ['controller' => 'Playlist', 'action' => 'find'], ['escape' => false]) ?>
        <?= $this->Html->link('<span class="material-symbols-outlined">play_disabled</span>' . __('List of unavailable tracks'), ['controller' => 'Playlist', 'action' => 'viewNotAvailable'], ['escape' => false]) ?>
        <?= $this->Html->link('<span class="material-symbols-outlined">merge</span>' . __('Merge playlists'), ['controller' => 'PlaylistMerger', 'action' => 'index'], ['escape' => false]) ?>
        <?= $this->Html->link('<span class="material-symbols-outlined">sync</span>' . __('Synchronize playlists'), ['controller' => 'PlaylistMerger', 'action' => 'synchronize'], ['escape' => false]) ?>
    </div>
</div>
<?= $this->Html->link('<span class="material-symbols-outlined">logout</span>' . __('Logout'), ['controller' => 'Main', 'action' => 'logout'], ['escape' => false]); ?>