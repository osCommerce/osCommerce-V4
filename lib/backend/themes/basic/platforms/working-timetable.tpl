{use class="common\helpers\Html"}
{use class="yii\helpers\Url"}
{\backend\assets\BDTPAsset::register($this)|void}

<link href="{$app->view->theme->baseUrl}/css/platforms.css" rel="stylesheet" type="text/css" />
{$message}

<div id="platforms_management_data" class="working-timetable">
    <form action="edit.tpl" name="save_item_form" id="save_item_form" enctype="multipart/form-data" onsubmit="return saveItem();">


        <div class="widget box box-no-shadow" style="min-height:183px;">
            <div class="widget-header widget-header-company"><h4>{$smarty.const.CATEGORY_OPEN_HOURS}</h4></div>
            <div class="widget-content">
                <div id="opening_hours_list">
                    {foreach $open_hours as $open_key => $open_hour}
                        <div class="w-line-row opening_hours">
                            <div>
                                <div class="hours_table">
                                    <label>{$smarty.const.ENTRY_DAYS}<span class="fieldRequired">*</span></label>
                                    <div class="col-md-10">
                                        {Html::dropDownList('open_days_'|cat:"$open_key", $open_hour->open_days|default:null, $days, ['class' => 'multiselect form-control', 'multiple' => 'multiple', 'data-role' => 'multiselect'])}
                                    </div>
                                </div>
                            </div>
                            <div class="time_int"><div class="time_int_1">
                                    <label>{$smarty.const.ENTRY_TIME}<span class="fieldRequired">*</span></label>
                                    <span class="time_title">{$smarty.const.ENTRY_TIME_FROM}</span>{Html::input('text', 'open_time_from[]', $open_hour->open_time_from|default:null, ['class' => 'pt-time form-control'])}</div>
                                <div class="time_int_2">
                                    <span class="time_title">{$smarty.const.ENTRY_TIME_TO}</span>{Html::input('text', 'open_time_to[]', $open_hour->open_time_to|default:null, ['class' => 'pt-time form-control'])}</div>
                                <div class="time_int_3">
                                    <a href="javascript:void(0)" onclick="return removeOpenHours(this);" class="btn">-</a>
                                </div>
                            </div>
                            {Html::input('hidden', 'platforms_open_hours_id[]', $open_hour->platforms_open_hours_id|default:null)}
                            {Html::input('hidden', 'platforms_open_hours_key[]', $open_key)}
                        </div>
                    {/foreach}
                </div>
                <div class="buttons_hours">
                    <a href="javascript:void(0)" onclick="return addOpenHours();" class="btn">{$smarty.const.BUTTON_ADD_MORE}</a>
                </div>
            </div>
        </div>


        <div class="widget box box-no-shadow" style="min-height:183px;">
            <div class="widget-header widget-header-company"><h4>{$smarty.const.CATEGORY_CUT_OFF_TIMES}</h4></div>
            <div class="widget-content">
                <div id="cut_off_times_list">
                    {foreach $cut_off_times as $cut_key => $cut_hour}
                        <div class="w-line-row opening_hours">
                            <div>
                                <div class="hours_table">
                                    <label>{$smarty.const.ENTRY_DAYS}<span class="fieldRequired">*</span></label>
                                    <div class="col-md-10">
                                        {Html::dropDownList('cut_off_times_days_'|cat:"$cut_key", $cut_hour->cut_off_times_days|default:null, $days, ['class' => 'multiselect form-control', 'multiple' => 'multiple', 'data-role' => 'multiselect'])}
                                    </div>
                                </div>
                            </div>
                            <div class="time_int">
                                <div class="time_int_1">
                                    <label>{$smarty.const.DAY_OF_WEEK}<span class="fieldRequired">*</span></label>
                                    <span class="time_title">{$smarty.const.TODAY_DELIVERY}</span>{Html::input('text', 'cut_off_times_today[]', $cut_hour->cut_off_times_today|default:null, ['class' => 'pt-time form-control'])}
                                </div>
                                <div class="time_int_2">
                                    <span class="time_title">{$smarty.const.NEXT_DAY_DELIVERY}</span>{Html::input('text', 'cut_off_times_next_day[]', $cut_hour->cut_off_times_next_day|default:null, ['class' => 'pt-time form-control'])}</div>
                                <div class="time_int_3">
                                    <a href="javascript:void(0)" onclick="return removeCutOffTimes(this);" class="btn">-</a>
                                </div>
                            </div>
                            {Html::input('hidden', 'platforms_cut_off_times_id[]', $cut_hour->platforms_cut_off_times_id|default:null)}
                            {Html::input('hidden', 'platforms_cut_off_times_key[]', $cut_key)}
                        </div>
                    {/foreach}
                </div>
                <div class="buttons_hours">
                    <a href="javascript:void(0)" onclick="return addCutOffTimes();" class="btn">{$smarty.const.BUTTON_ADD_MORE}</a>
                </div>
            </div>

        </div>

        <div class="widget box box-no-shadow" style="min-height:80px;">
            <div class="widget-header widget-header-company"><h4>{$smarty.const.BANK_HOLIDAYS}</h4></div>
            <div class="widget-content">
                <div class="buttons_holidays">
                    <a href="{\yii\helpers\Url::to(['platforms/holidays', 'platform_id' => $pInfo->platform_id])}" class="btn popup">{$smarty.const.BANK_HOLIDAYS}</a>
                </div>
            </div>
        </div>


        {Html::input('hidden', 'id', $pInfo->platform_id)}
        <div class="btn-bar">
            <div class="btn-left"><a href="javascript:void(0)" onclick="return backStatement();" class="btn btn-cancel-foot">{$smarty.const.IMAGE_CANCEL}</a></div>
            <div class="btn-right"><button type="submit" class="btn btn-confirm">{$smarty.const.IMAGE_SAVE}</button></div>
        </div>
    </form>

</div>

<div id="opening_hours_template" style="display: none;">
    <div class="w-line-row opening_hours">
        <div>
            <div class="hours_table">
                <label>{$smarty.const.ENTRY_DAYS}<span class="fieldRequired">*</span></label>
                <div class="col-md-10">
                    {Html::dropDownList('open_days_', '', $days, ['class' => 'form-control', 'multiple' => 'multiple', 'data-role' => 'multiselect-new'])}
                </div>
            </div>
        </div>
        <div class="time_int">
            <div class="time_int_1">
                <label>{$smarty.const.ENTRY_TIME}<span class="fieldRequired">*</span></label>
                <span class="time_title">{$smarty.const.ENTRY_TIME_FROM}</span>{Html::input('text', 'open_time_from[]', '', ['class' => 'pt-time-new form-control'])}
            </div>
            <div class="time_int_2">
                <span class="time_title">{$smarty.const.ENTRY_TIME_TO}</span>{Html::input('text', 'open_time_to[]', '', ['class' => 'pt-time-new form-control'])}
            </div>
            <div class="time_int_3">
                <a href="javascript:void(0)" onclick="return removeOpenHours(this);" class="btn">-</a>
            </div>
        </div>
        {Html::input('hidden', 'platforms_open_hours_id[]', '')}
        {Html::input('hidden', 'platforms_open_hours_key[]', '')}
    </div>
</div>
<div id="cut_off_times_template" style="display: none;">
    <div class="w-line-row opening_hours">
        <div>
            <div class="hours_table">
                <label>{$smarty.const.ENTRY_DAYS}<span class="fieldRequired">*</span></label>
                <div class="col-md-10">
                    {Html::dropDownList('cut_off_times_days_', '', $days, ['class' => 'form-control', 'multiple' => 'multiple', 'data-role' => 'multiselect-new'])}
                </div>
            </div>
        </div>
        <div class="time_int">
            <div class="time_int_1">
                <label>{$smarty.const.DAY_OF_WEEK}<span class="fieldRequired">*</span></label>
                <span class="time_title">{$smarty.const.TODAY_DELIVERY}</span>{Html::input('text', 'cut_off_times_today[]', '', ['class' => 'pt-time-new form-control'])}
            </div>
            <div class="time_int_2">
                <span class="time_title">{$smarty.const.NEXT_DAY_DELIVERY}</span>{Html::input('text', 'cut_off_times_next_day[]', '', ['class' => 'pt-time-new form-control'])}
            </div>
            <div class="time_int_3">
                <a href="javascript:void(0)" onclick="return removeCutOffTimes(this);" class="btn">-</a>
            </div>
        </div>
        {Html::input('hidden', 'platforms_cut_off_times_id[]', '')}
        {Html::input('hidden', 'platforms_cut_off_times_key[]', '')}
    </div>
</div>

<script>
    function saveItem() {
        $.post("{$app->urlManager->createUrl('platforms/working-timetable')}", $('#save_item_form').serialize(), function (data, status) {
            if (status == "success") {
                $('#platforms_management_data').html(data);
            } else {
                alert("Request error.");
            }
        }, "html");

        return false;
    }
    function backStatement() {
        window.history.back();
        return false;
    }

    const timeSettings = {
        display: {
            viewMode: 'clock',
            components: {
                calendar: false,
                date: false,
                month: false,
                year: false,
                decades: false,
            },
        },
        localization: {
            locale: 'en',
            format: 'h:mm T'
        }
    };

    $(document).ready(function(){
        $('.buttons_holidays .popup').popUp({
            box: "<div class='popup-box-wrap'><div class='around-pop-up'></div><div class='popup-box'><div class='pop-up-close pop-up-close-alert'></div><div class='popup-heading theme_choose'>{$smarty.const.BANK_HOLIDAYS}</div><div class='pop-up-content'><div class='preloader'></div></div></div></div>"
        })

        $('.pt-time').tempusDominus(timeSettings);
    });
    var nextKey = {$count_open_hours};
    function removeOpenHours(obj) {
        $(obj).parent('div').parent('div').parent('div.opening_hours').remove();
        return false;
    }
    function addOpenHours() {
        nextKey = nextKey +1;
        $('#opening_hours_template').find('select[name*="open_days"]').attr('name', 'open_days_'+nextKey+'[]');
        $('#opening_hours_template').find('input[name="platforms_open_hours_key[]"]').val(nextKey);
        $('#opening_hours_list').append($('#opening_hours_template').html());
        $("form select[data-role=multiselect-new]").attr('data-role', 'multiselect');
        $("form select[data-role=multiselect]").multiselect({
            selectedList: 1 // 0-based index
        });
        $('form .pt-time-new').tempusDominus(timeSettings);

        return false;
    }
    var nextDeliveryKey = {$count_cut_off_times};
    function addCutOffTimes() {
        nextDeliveryKey = nextDeliveryKey +1;
        $('#cut_off_times_template').find('select[name*="cut_off_times_days"]').attr('name', 'cut_off_times_days_'+nextDeliveryKey+'[]');
        $('#cut_off_times_template').find('input[name="platforms_cut_off_times_key[]"]').val(nextDeliveryKey);
        $('#cut_off_times_list').append($('#cut_off_times_template').html());
        $("form select[data-role=multiselect-new]").attr('data-role', 'multiselect');
        $("form select[data-role=multiselect]").multiselect({
            selectedList: 1 // 0-based index
        });
        $('form .pt-time-new').tempusDominus(timeSettings);
        return false;
    }
    function removeCutOffTimes(obj) {
        $(obj).parent('div').parent('div').parent('div.opening_hours').remove();
        return false;
    }
</script>