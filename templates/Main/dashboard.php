<?php
/**
 * @var \App\View\AppView $this
 */
?>
<div class="dashboard">

    <div class="content">
        <div class="row">
            <div class="column">
                <h3><?= __('Today you have listened to music that is mostly: ') ?></h3>
            </div>
        </div>
        <div id="todays-mood">
        </div>
    </div>

    <div class="content list top-tracks">
        <div class="row">
            <h3><?= __('Top tracks') ?></h3>
            <p>
                <?= $this->Form->select('term', ['short_term' => __("{0,plural,=1{Last month} other{Last # months}}", 1), 'medium_term' => __("{0,plural,=1{Last month} other{Last # months}}", 6), 'long_term' => __('{0,plural,=1{Last year} other{Last # years}}', 1)], ['default' => 'medium_term']); ?>
            </p>
        </div>
        <?php echo $this->element('tracks', ['topTracks' => $topTracks]); ?>
    </div>

    <div class="content list top-artists">
        <div class="row">
            <h3><?= __('Top artists') ?></h3>
            <p>
                <?= $this->Form->select('term', ['short_term' => __("{0,plural,=1{Last month} other{Last # months}}", 1), 'medium_term' => __("{0,plural,=1{Last month} other{Last # months}}", 6), 'long_term' => __('{0,plural,=1{Last year} other{Last # years}}', 1)], ['default' => 'medium_term']); ?>
            </p>
        </div>
        <?php echo $this->element('artists', ['topArtists' => $topArtists]); ?>
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

        $.ajax({
            'url': '<?= $this->Url->build(['action' => 'ajaxGetTodaysMood']) ?>',
            'method' : 'GET',
            beforeSend: function(){
                loader('div#todays-mood');
            },
            success: function(response){
                loaderStop('div#todays-mood');
                $('div#todays-mood').html(response);
            }
        });
        
    });
</script>