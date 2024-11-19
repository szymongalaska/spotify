<div class="content top-tracks">
    <div class="row">
    <h3><?= __('Top tracks') ?></h3>
    <p>
        <?= $this->Form->select('term', ['short_term' => __("{0,plural,=1{Last month} other{Last # months}}", 1), 'medium_term' => __("{0,plural,=1{Last month} other{Last # months}}", 6), 'long_term' => __('{0,plural,=1{Last year} other{Last # years}}', 1)], ['default' => 'medium_term']); ?>
    </p>
    </div>
    <?php echo $this->element('tracks', ['topTracks' => $topTracks]); ?>
</div>

<script>
    $(function(){
        $('div.top-tracks select').on('change', function(){
            loader('div#tracks');
            let div = $(this).closest('div.top-tracks');
            $.ajax({
                'method': 'GET',
                'url': "<?= $this->Url->build(['controller' => 'Main', 'action' => 'ajaxGetTopTracks']); ?>/"+$(this).val(),
                success: function(response){
                    loaderStop('div#tracks');
                    $('div#tracks').remove();
                    $(div).append(response);
                }
            })
        });
    });
</script>