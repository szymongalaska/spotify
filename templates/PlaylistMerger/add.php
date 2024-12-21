<?php
/**
 * @var \App\View\AppView $this
 * @var array $myPlaylists
 * @var array $myOwnPlaylists
 */
?>
<div class="playlistMerger">
    <div class="content">
        <p class="description"><?= __('Merge multiple playlists into one. Target playlist must be your own.') ?></p>
    </div>
    <?php if (!empty($myOwnPlaylists) && !empty($myPlaylists)): ?>
        <div class="playlists-container">
            <div class="content list playlists">
                <div class="row">
                    <h3><?= __('Select source playlists') ?></h3>
                </div>
                <ul id="source-playlists[]">
                    <?php foreach ($myPlaylists as $playlist): ?>
                        <li><?php echo $this->element('playlist', ['playlist' => $playlist]) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="content list playlists">
                <div class="row">
                    <h3><?= __('Select target playlist') ?></h3>
                </div>
                <ul id="target-playlist">
                    <li>
                        <div class="row playlist" data-id="create-new">
                            <div class="image-column column <?php echo $column ?? 'column-25' ?>">
                                <span class="material-symbols-outlined library-music">library_music</span>
                            </div>
                            <div class="column" style="display: flex; flex-direction: column; justify-content: center">

                                <div class="row">
                                    <?= __('Create new playlist') ?>
                                </div>
                            </div>
                        </div>
                    </li>
                    <?php foreach ($myOwnPlaylists as $playlist): ?>
                        <li><?php echo $this->element('playlist', ['playlist' => $playlist]) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <?= $this->Form->create(null, ['url' => ['action' => 'saveAndMerge']]) ?>
        <div class="content">
            <fieldset>
                <?= $this->Form->control('savedTracks', ['type' => 'checkbox', 'label' => __('Include only songs added to the library (Saved Tracks)')]) ?>
                <?= $this->Form->control('prepend', ['type' => 'checkbox', 'label' => __('Add newest songs on top of playlist')]) ?>
                <?= $this->Form->control('enable_synchronization', ['type' => 'checkbox', 'label' => __('Enable auto synchronization')]) ?>
                <?= $this->Form->control('playlist_merger_cronjob.frequency', ['type' => 'select', 'label' => __('Synchronization frequency'), 'options' => ['weekly' => __('Once a week (at Sunday)'), 'once_daily' => __('Once a day'), 'twice_daily' => __('Twice a day'), 'four_times_daily' => __('Every six hours'),]]) ?>

                <div class="hidden border-box" id="create-playlist">
                    <p><?= __('Create new playlist') ?></p>
                    <?= $this->Form->control('newPlaylist.name', ['type' => 'text', 'placeholder' => __('Name of playlist')]) ?>
                    <?= $this->Form->control('newPlaylist.description', ['type' => 'text', 'placeholder' => __('Description of playlist')]) ?>
                    <?= $this->Form->control('newPlaylist.public', ['type' => 'checkbox', 'label' => __('Public playlist'), 'checked' => true]) ?>
                </div>

                <div class="hidden">
                    <select name="source-playlists[]" multiple="multiple">
                        <?php foreach ($myPlaylists as $playlist): ?>
                            <option value="<?= $playlist['id'] ?>"><?= h($playlist['name']) ?></option>
                        <?php endforeach; ?>
                    </select>

                    <select name="target-playlist">
                        <option value="create-new"><?= __('Create new playlist') ?></option>
                        <?php foreach ($myOwnPlaylists as $playlist): ?>
                            <option value="<?= $playlist['id'] ?>"><?= h($playlist['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </fieldset>
        </div>
        <?= $this->Form->submit(__('Submit')) ?>
        <?= $this->Form->end() ?>
    <?php else: ?>
        <p class="message error"><?= __('There are not enough playlists to merge') ?></p>
    <?php endif; ?>
</div>

<script>
    $(function () {
        $('ul li').on('click', function () {

            let id = $(this).parent().attr('id');
            let val = $(this).children('div.row.playlist').data('id');
            let option = 'select[name="' + id + '"] option[value="' + val + '"]';
            let prop = $(option).prop('selected');

            $(option).prop('selected', !prop);

            if (id == 'target-playlist') {
                $('ul#target-playlist li.selected').each(function () {
                    $(this).removeClass('selected');
                });
            }

            $(this).toggleClass('selected');
            $('select[name="' + id + '"]').trigger('change');
        });

        $('form').on('submit', function(e){
            if($('ul#target-playlist li.selected').length == 0)
                $('select[name="target-playlist"]').val(null);
        });

        $('input[name="enable_synchronization"]').on('change', function () {
            $('select#playlist-merger-cronjob-frequency').parent().toggle();
        });

        $('select[name="target-playlist"]').on('change', function () {
            console.log($(this).val());
            if ($(this).val() == 'create-new') {
                $('input[name="newPlaylist[name]').prop('required', 'required');
                $('div#create-playlist').show();
            }
            else {
                $('input[name="newPlaylist[name]').prop('required', false);
                $('div#create-playlist').hide();
            }
        });
    });
</script>