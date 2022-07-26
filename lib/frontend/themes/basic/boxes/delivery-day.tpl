
{foreach $deliveryEnds as $delivery}
<div class="delivery-ends delivery-ends-{$delivery@index}">
    <div class="title">{$delivery['title']}</div>

    <div class="time-left">
        <span class="left-text-left">{$smarty.const.SALE_LEFT_TEXT_LEFT}</span>
        {if $delivery['interval']->format('%a')}
            <span class="left-count box-days">
                <span class="left-count-days">{$delivery['interval']->format('%a')}</span>
                <span class="left-count-days-text">{$smarty.const.SALE_TEXT_DAYS}</span>
            </span>
        {/if}
        <span class="left-count box-hours">
            <span class="left-count-hours">{$delivery['interval']->h}</span>
            <span class="left-count-hours-text">{$smarty.const.SALE_TEXT_HOURS}</span>
        </span>
        <span class="left-count box-minutes">
            <span class="left-count-minutes">{$delivery['interval']->i}</span>
            <span class="left-count-minutes-text">{$smarty.const.SALE_TEXT_MINUTES}</span>
        </span>
        <span class="left-count box-seconds">
            <span class="left-count-seconds">{$delivery['interval']->s}</span>
            <span class="left-count-seconds-text">{$smarty.const.SALE_TEXT_SECONDS}</span>
        </span>
        <span class="left-text-right">{$smarty.const.SALE_LEFT_TEXT_RIGHT}</span>
    </div>

</div>
{/foreach}
<script type="text/javascript">
    tl('{\frontend\design\Info::themeFile('/js/main.js')}', function(){
        var $box = $('#box-{$id}');

        {foreach $deliveryEnds as $delivery}
        $('.delivery-ends-{$delivery@index}', $box).backCounter();
        {/foreach}
    });
</script>