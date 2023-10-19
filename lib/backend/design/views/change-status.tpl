{use class="yii\helpers\Html"}
{\backend\assets\BDTPAsset::register($this)|void}

<div class="schedule-status-change">
{foreach $pageSwitchers as $pageSwitcher}
    <div class="switcher-form-row">
    {Html::dropDownList('page_status[action][]', $pageSwitcher['status'], $pageStatusActions, ['class' => 'form-control'])}
    {Html::dropDownList('page_status[period][]', $pageSwitcher['period'], $pageStatusPeriods, ['class' => 'form-control page-status-period'])}
        <div class="date-selector">
            {if $pageSwitcher['day'] == -1}
                {Html::input('hidden', 'page_status[day][]')}
            {else}
                {Html::dropDownList('page_status[day][]', $pageSwitcher['day'], $weekDays, ['class' => 'form-control page-status-week-day'])}
            {/if}
            {Html::input('text', 'page_status[date][]', $pageSwitcher['date'], ['class' => 'form-control datetimepicker'])}
        </div>
        {if !$pageSwitcher@first}
            <div class="remove" title="{$smarty.const.TEXT_REMOVE}"></div>
        {/if}
    </div>
{foreachelse}
    <div class="switcher-form-row">
        {Html::dropDownList('page_status[action][]', 0, $pageStatusActions, ['class' => 'form-control'])}
        {Html::dropDownList('page_status[period][]', 0, $pageStatusPeriods, ['class' => 'form-control page-status-period'])}
        <div class="date-selector">
            {Html::input('hidden', 'page_status[day][]')}
            {Html::input('text', 'page_status[date][]', '', ['class' => 'form-control datetimepicker'])}
        </div>
    </div>
{/foreach}
</div>

<div class="switcher-form-button"><span class="btn">{$smarty.const.SCHEDULE_MORE_CHANGE}</span></div>
<script type="text/javascript">
    $(function(){

        $('.switcher-form-row').each(applyRow);

        $('.switcher-form-button .btn').on('click', function(){
            const $newRow = $(`
<div class="switcher-form-row">
    {Html::dropDownList('page_status[action][]', 0, $pageStatusActions, ['class' => 'form-control'])}
    {Html::dropDownList('page_status[period][]', 0, $pageStatusPeriods, ['class' => 'form-control page-status-period'])}
    <div class="date-selector">
        {Html::input('hidden', 'page_status[day][]')}
        {Html::input('text', 'page_status[date][]', '', ['class' => 'form-control datetimepicker'])}
    </div>
    <div class="remove" title="{$smarty.const.TEXT_REMOVE}"></div>
</div>
            `);
            $('.schedule-status-change').append($newRow);
            applyRow.call($newRow)
        });

        function applyRow(){
            let $switcherFormRow = $(this);

            let $period = $('.page-status-period', $switcherFormRow);
            let $dateSelector = $('.date-selector', $switcherFormRow);

            $('.remove', $switcherFormRow).on('click', function(){
                $switcherFormRow.remove()
            });

            setPeriod();
            $period.on('change', function(){
                $('.datetimepicker', $switcherFormRow).dispose();
                if ($period.val() === 'week') {
                    $dateSelector.html(`
{Html::dropDownList('page_status[day][]', 0, $weekDays, ['class' => 'form-control page-status-week-day'])}
{Html::input('text', 'page_status[date][]', '', ['class' => 'form-control datetimepicker'])}
                    `);
                } else {
                    $dateSelector.html(`
{Html::input('hidden', 'page_status[day][]')}
{Html::input('text', 'page_status[date][]', '', ['class' => 'form-control datetimepicker'])}
                    `);
                }

                setPeriod()
            });

            function setPeriod(){
                $dateSelector.attr('class', 'date-selector period-' + $period.val());

                const defaultDate = new Date();
                let settings = { };
                settings.locale = 'en';
                //settings.debug = false;
                //settings.defaultDate = new Date(defaultDate.getFullYear(), defaultDate.getMonth(), defaultDate.getDate(), 0, 0);

                if ($period.val() === 'day') {
                    settings.format = 'h:mm T';
                } else if ($period.val() === 'week') {
                    settings.format = 'h:mm T';
                } else if ($period.val() === 'month') {
                    settings.format = 'dd h:mm T';
                    //settings.defaultDate = new Date('2020-03-01T00:00:00');
                    //settings.minDate = new Date('2020-03-01T00:00:00');
                    //settings.maxDate = new Date('2020-03-31T23:59:00');
                } else if ($period.val() === 'year') {
                    settings.format = 'dd MMM h:mm T';
                    //settings.dayViewHeaderFormat = 'MMMM';
                } else {
                    settings.format = 'dd MMM yyyy h:mm T';
                }

                $('.datetimepicker', $switcherFormRow).tempusDominus({
                    localization: settings
                });
            };
        }
    })
</script>