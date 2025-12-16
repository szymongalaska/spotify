<?php
/**
 * @var \App\View\AppView $this
 */
?>
<style>
    #options {
        justify-content: end;
    }

    div:has(> .switch) {
        display: flex;
        align-items: center;
        gap: .5rem;
    }
</style>
<div id="new-and-available">
    <div class="content list available-tracks">
        <div class="row" id="options">
            <div>
                Powiadomienia
                <label class="switch" for="push">
                    <input type="checkbox" id="push" name="push" value="1" <?= $push ? 'checked' : ''?> />
                    <div class="slider round"></div>
                </label>
            </div>
        </div>
        <?php if (empty($tracks)): ?>
            <p><?= __('No data available for comparison. Please try again in about 24 hours.') ?></p>
        <?php else: ?>
            <div class="row">
                <h3><?= __('Changes') ?></h3>
            </div>
            <div class="row">
                <div class="column column-50">
                    <h5><?= __('New tracks') ?></h5>
                    <?php echo $this->element('tracks', ['tracks' => $tracks['availableTracks']]); ?>
                </div>
                <div class="column column-50">
                    <h5>
                        <?= __('Unavailable tracks') ?>
                    </h5>
                    <?php echo $this->element('tracks', ['tracks' => $tracks['unavailableTracks']]); ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="content list unavailable-tracks">
        <div class="row">
            <h3><?= __('Unavailable tracks') ?></h3>
        </div>
        <?php echo $this->element('tracks', ['tracks' => $unavailableTracks]); ?>
    </div>
</div>
<!-- <script>
    $('#push').on('change', function(){
        $.post({
            headers: {
                'X-CSRF-Token': '<?= $this->getRequest()->getAttribute('csrfToken') ?>',
            },
            url: 'push-notifications/add',
            data: {'library_new_and_available_changes': $('#push').val()},
            success: function(response){

            }
        });
    });
</script> -->