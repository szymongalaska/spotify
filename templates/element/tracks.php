<?php
/**
 * @var \App\View\AppView $this
 */
?>
<div id="tracks">
    <ul>
        <?php foreach ($tracks as $track): ?>
            <li><?php echo $this->element('song', ['track' => $track]); ?></li>
        <?php endforeach; ?>
    </ul>
</div>