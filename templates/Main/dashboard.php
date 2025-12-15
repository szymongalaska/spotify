<?php
/**
 * @var \App\View\AppView $this
 */
?>
<div class="dashboard">

    <div class="content list top-tracks">
        <div class="row">
            <h3><?= __('Top tracks') ?></h3>
            <p>
                <?= $this->Form->select('term', ['short_term' => __("{0,plural,=1{Last month} other{Last # months}}", 1), 'medium_term' => __("{0,plural,=1{Last month} other{Last # months}}", 6), 'long_term' => __('{0,plural,=1{Last year} other{Last # years}}', 1)], ['default' => 'medium_term']); ?>
            </p>
        </div>
        <?php if(!empty($topTracks['items'])): ?>
            <?php echo $this->element('tracks', ['tracks' => $topTracks['items']]); ?>
        <?php else: ?>
            <p class="message"><?= __('Your top tracks list is empty - listen to some music!') ?></p>
        <?php endif; ?>
    </div>

    <div class="content list top-artists">
        <div class="row">
            <h3><?= __('Top artists') ?></h3>
            <p>
                <?= $this->Form->select('term', ['short_term' => __("{0,plural,=1{Last month} other{Last # months}}", 1), 'medium_term' => __("{0,plural,=1{Last month} other{Last # months}}", 6), 'long_term' => __('{0,plural,=1{Last year} other{Last # years}}', 1)], ['default' => 'medium_term']); ?>
            </p>
        </div>
        <?php if(!empty($topArtists['items'])): ?>
            <?php echo $this->element('artists', ['topArtists' => $topArtists]); ?>
        <?php else: ?>
            <p class="message"><?= __('Your top artists list is empty - listen to some music!') ?></p>
        <?php endif; ?>
    </div>

</div>
<script>
    $(function () {
        $('div.list select').on('change', function () {
            var variant = $(this).closest('div.list').hasClass('top-tracks') ? 'tracks' : 'artists';
            var div = $(this).closest('div.top-' + variant);
            $.ajax({
                'method': 'GET',
                'url': "/main/ajax-get-top-" + variant + "/" + $(this).val(),
                beforeSend: function(){
                    loader('div#' + variant);
                },
                success: function (response) {
                    loaderStop('div#' + variant);
                    $('div#' + variant).remove();
                    $(div).append(response);
                }
            })
        });
    });
</script>