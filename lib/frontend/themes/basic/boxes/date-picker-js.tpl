{use class="frontend\design\Info"}

{if $onlyDaysCurrent}
  {call daysCurrent}
{else}
<script type="text/javascript">
    tl(['{Info::themeFile('/js/bootstrap.min.js')}',
        '{Info::themeFile('/js/bootstrap-datepicker.js')}'
    ], function () {
        $('head').prepend('<link rel="stylesheet" href="{Info::themeFile('/css/bootstrap-datepicker.css')}">');
        {call daysCurrent}

        {\frontend\design\Info::addBoxToCss('datepicker')}

        $('{$selector}').datepicker({
            'format': '{$smarty.const.DATE_FORMAT_DATEPICKER|escape:'javascript'}',
            'language': 'current',
            {foreach $params as $key => $val}
            '{$key|escape:'javascript'}': '{$val|escape:'javascript'}',
            {/foreach}
        });
    });
</script>
{/if}
{function daysCurrent}
        $.fn.datepicker.dates.current = {
            days: [
                "{$smarty.const.TEXT_SUNDAY|escape:'javascript'}",
                "{$smarty.const.TEXT_MONDAY|escape:'javascript'}",
                "{$smarty.const.TEXT_TUESDAY|escape:'javascript'}",
                "{$smarty.const.TEXT_WEDNESDAY|escape:'javascript'}",
                "{$smarty.const.TEXT_THURSDAY|escape:'javascript'}",
                "{$smarty.const.TEXT_FRIDAY|escape:'javascript'}",
                "{$smarty.const.TEXT_SATURDAY|escape:'javascript'}"],
            daysShort: [
                "{$smarty.const.DATEPICKER_DAY_SUN|escape:'javascript'}",
                "{$smarty.const.DATEPICKER_DAY_MON|escape:'javascript'}",
                "{$smarty.const.DATEPICKER_DAY_TUE|escape:'javascript'}",
                "{$smarty.const.DATEPICKER_DAY_WED|escape:'javascript'}",
                "{$smarty.const.DATEPICKER_DAY_THU|escape:'javascript'}",
                "{$smarty.const.DATEPICKER_DAY_FRI|escape:'javascript'}",
                "{$smarty.const.DATEPICKER_DAY_SAT|escape:'javascript'}"],
            daysMin: [
                "{$smarty.const.DATEPICKER_DAY_SU|escape:'javascript'}",
                "{$smarty.const.DATEPICKER_DAY_MO|escape:'javascript'}",
                "{$smarty.const.DATEPICKER_DAY_TU|escape:'javascript'}",
                "{$smarty.const.DATEPICKER_DAY_WE|escape:'javascript'}",
                "{$smarty.const.DATEPICKER_DAY_TH|escape:'javascript'}",
                "{$smarty.const.DATEPICKER_DAY_FR|escape:'javascript'}",
                "{$smarty.const.DATEPICKER_DAY_SA|escape:'javascript'}"],
            months: [
                "{$smarty.const.DATEPICKER_MONTH_JANUARY|escape:'javascript'}",
                "{$smarty.const.DATEPICKER_MONTH_FEBRUARY|escape:'javascript'}",
                "{$smarty.const.DATEPICKER_MONTH_MARCH|escape:'javascript'}",
                "{$smarty.const.DATEPICKER_MONTH_APRIL|escape:'javascript'}",
                "{$smarty.const.DATEPICKER_MONTH_MAY|escape:'javascript'}",
                "{$smarty.const.DATEPICKER_MONTH_JUNE|escape:'javascript'}",
                "{$smarty.const.DATEPICKER_MONTH_JULY|escape:'javascript'}",
                "{$smarty.const.DATEPICKER_MONTH_AUGUST|escape:'javascript'}",
                "{$smarty.const.DATEPICKER_MONTH_SEPTEMBER|escape:'javascript'}",
                "{$smarty.const.DATEPICKER_MONTH_OCTOBER|escape:'javascript'}",
                "{$smarty.const.DATEPICKER_MONTH_NOVEMBER|escape:'javascript'}",
                "{$smarty.const.DATEPICKER_MONTH_DECEMBER|escape:'javascript'}"],
            monthsShort: [
                "{$smarty.const.DATEPICKER_MONTH_JAN|escape:'javascript'}",
                "{$smarty.const.DATEPICKER_MONTH_FEB|escape:'javascript'}",
                "{$smarty.const.DATEPICKER_MONTH_MAR|escape:'javascript'}",
                "{$smarty.const.DATEPICKER_MONTH_APR|escape:'javascript'}",
                "{$smarty.const.DATEPICKER_MONTH_MAY|escape:'javascript'}",
                "{$smarty.const.DATEPICKER_MONTH_JUN|escape:'javascript'}",
                "{$smarty.const.DATEPICKER_MONTH_JUL|escape:'javascript'}",
                "{$smarty.const.DATEPICKER_MONTH_AUG|escape:'javascript'}",
                "{$smarty.const.DATEPICKER_MONTH_SEP|escape:'javascript'}",
                "{$smarty.const.DATEPICKER_MONTH_OCT|escape:'javascript'}",
                "{$smarty.const.DATEPICKER_MONTH_NOV|escape:'javascript'}",
                "{$smarty.const.DATEPICKER_MONTH_DEC|escape:'javascript'}"],
            today: "{$smarty.const.TEXT_TODAY|escape:'javascript'}",
            clear: "{$smarty.const.TEXT_CLEAR|escape:'javascript'}",
            weekStart: 1
        };
{/function}
