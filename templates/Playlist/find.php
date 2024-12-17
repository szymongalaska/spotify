<?php
/**
 * @var \App\View\AppView $this
 * @var array $playlists Array of playlists owned or followed by user
 */
?>
<div class="content list playlists">
    <p class="description"><?= __('You can select a playlist from the list to preview it or enter any playlist ID to view its contents. Please note that large playlists may take longer to load.') ?></p>
    <?= $this->Form->create(null, ['action' => 'view', 'method' => 'GET']) ?>
    <?= $this->Form->control('playlist', ['type' => 'text', 'label' => false]) ?>
    <?= $this->Form->submit(__('Submit')) ?>
    <?= $this->Form->end(); ?>
    <?php if(!empty($playlists)): ?>
    <ul>
        <?php foreach($playlists as $playlist): ?>
            <li><a href="<?= $this->Url->build(['action' => 'view', $playlist['id']]) ?>"><?php echo $this->element('playlist', ['playlist' => $playlist, 'column' => 'column-10']) ?></a></li>
        <?php endforeach; ?>
    </ul>
    <?php else: ?>
        <p class="message"><?= __('There are no playlists to display') ?></p>
    <?php endif; ?>
</div>

<script>
    $(function(){
        $('form').on('submit', function(event){
            event.preventDefault();
            let id = $('form input').val();
            let url = '<?= $this->Url->build(['action' => 'view']) ?>';
            window.location.href = url + '/' + id;
        });
    });
</script>