<?php
/**
 * @var \App\View\AppView $this
 * @var array $playlists Array of playlists owned or followed by user
 */
?>
<div class="content list playlists">
    <h3><?= __('Select playlist') ?></h3>
    <ul>
        <?php foreach ($playlists as $playlist): ?>
            <li><a
                    href="<?= $this->Url->build(['action' => 'viewNotAvailableTracks', $playlist['id']]) ?>"><?php echo $this->element('playlist', ['playlist' => $playlist, 'column' => 'column-10']) ?></a>
            </li>
        <?php endforeach; ?>
    </ul>
</div>