<div class="sale-ends">
    <div class="ends">
        <span class="ends-date-text">{$smarty.const.SALE_ENDS_DATE_TEXT}</span>
        <span class="ends-date">{$expiresDate}</span>
    </div>

    <div class="left">
        <span class="left-text-left">{$smarty.const.SALE_LEFT_TEXT_LEFT}</span>
        <span class="left-count box-days">
            <span class="left-count-days">{$interval->format('%a')}</span>
            <span class="left-count-days-text">{$smarty.const.SALE_TEXT_DAYS}</span>
        </span>
        <span class="left-count box-hours">
            <span class="left-count-hours">{$interval->h}</span>
            <span class="left-count-hours-text">{$smarty.const.SALE_TEXT_HOURS}</span>
        </span>
        <span class="left-count box-minutes" style="display: none">
            <span class="left-count-minutes">{$interval->i}</span>
            <span class="left-count-minutes-text">{$smarty.const.SALE_TEXT_MINUTES}</span>
        </span>
        <span class="left-count box-seconds" style="display: none">
            <span class="left-count-seconds">{$interval->s}</span>
            <span class="left-count-seconds-text">{$smarty.const.SALE_TEXT_SECONDS}</span>
        </span>
        <span class="left-text-right">{$smarty.const.SALE_LEFT_TEXT_RIGHT}</span>
    </div>

</div>
<script type="text/javascript">
    tl('{\frontend\design\Info::themeFile('/js/main.js')}', function(){
        $('#box-{$id}').backCounter();

    });
</script>