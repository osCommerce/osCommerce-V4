
<div class="timeline">
    <div class="inform">Only <b>{call format number = $bar['next']-$bar['current']} left</b> to get <b>free gift</b></div>
    <div class="start-label">{call format number = 0}</div>

    <div class="progress-bar">
        <div class="progress-bar-content" style="width:{ceil($bar['current']/$bar['next']*100)}%"></div>
    </div>

    <div class="end-label">{call format number = $bar['next']}</div>

    {if $bar['time_end']}
        <div class="end-date">end date: <b>{$bar['time_end']}</b></div>
    {/if}
</div>
<script>
    tl(function(){
        $(window).one('cart_change', function(){
            var id = $('.timeline').parent().attr('id').substring(4);
            $.get("{tep_href_link('get-widget/one')}", {
                id: id,
                action: 'main'
            }, function (d) {
                $('#box-' + id).html(d)
            })
        })
    })
</script>
{function format}
{$currencies->format($number, true, $currency, $currencies->currencies[$currency]->value)}
{/function}