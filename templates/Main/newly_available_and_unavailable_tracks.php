<?php
/**
 * @var \App\View\AppView $this
 */
?>
<div id="new-and-available">

    <?php if (!empty($tracks)): ?>
        <div class="content list available-tracks">
            <div class="row">
                <h3><?= __('Available tracks') ?></h3>
            </div>
            <?php echo $this->element('tracks', ['topTracks' => $tracks['availableTracks']]); ?>
        </div>

        <div class="content list unavailable-tracks">
            <div class="row">
                <h3><?= __('Unavailable Tracks') ?></h3>
            </div>
            <?php echo $this->element('tracks', ['topTracks' => $tracks['unavailableTracks']]); ?>
        </div>

    <?php else: ?>
        <div class="content list">
            <p><?= __('No data available for comparison. Please try again in about 24 hours.') ?></p>
        </div>
    <?php endif; ?>

</div>