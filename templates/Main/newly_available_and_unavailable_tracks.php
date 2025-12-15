<?php
/**
 * @var \App\View\AppView $this
 */
?>
<div id="new-and-available">
    <?php if (!empty($tracks)): ?>
        <div class="content list available-tracks">
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
                    <?= __('Unavailable Tracks') ?>
                </h5>
                <?php echo $this->element('tracks', ['tracks' => $tracks['unavailableTracks']]); ?>
            </div>
            </div>
        </div>

        <div class="content list unavailable-tracks">
            <div class="row">
                <h3><?= __('Unavailable Tracks') ?></h3>
            </div>
                            <?php echo $this->element('tracks', ['tracks' => $unavailableTracks]); ?>
        </div>

    <?php else: ?>
        <div class="content list">
            <p><?= __('No data available for comparison. Please try again in about 24 hours.') ?></p>
        </div>
    <?php endif; ?>

</div>