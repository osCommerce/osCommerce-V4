{use class="frontend\design\Info"}
{use class="common\helpers\Acl"}
{assign var=re1 value='.{'}
{assign var=re2 value='}'}
{use class = "yii\helpers\Html"}
{\frontend\design\Info::addBoxToCss('info')}
{\frontend\design\Info::addBoxToCss('form')}

<div class="middle-form">
    {Html::beginForm($action, 'post', ['id' => 'accountEdit'])}

    <div class="messages"></div>
	<div class="formWrapBox">
    {if in_array(ACCOUNT_GENDER, ['required', 'required_register', 'visible', 'visible_register'])}
        <div class="col-full col-gender">
            <span>{field_label const="ENTRY_GENDER" configuration="ACCOUNT_GENDER"}</span>
            {assign var="options" value=[]}
            {if in_array(ACCOUNT_GENDER, ['required_register', 'required'])}{$options['required'] = 'required'}{/if}
            {Html::activeRadioList($editModel, 'gender', $editModel->getGenderList(), $options)}
        </div>
    {/if}
    {if in_array(ACCOUNT_FIRSTNAME, ['required', 'required_register', 'visible', 'visible_register'])}
        <div class="col-left">
            <label for="{$editModel->formName()}-firstname">{field_label const="ENTRY_FIRST_NAME" configuration="ACCOUNT_FIRSTNAME"}</label>
            {if in_array(ACCOUNT_FIRSTNAME, ['required', 'required_register'])}
                {Html::activeTextInput($editModel, 'firstname', ['data-pattern' => "{$re1}{$smarty.const.ENTRY_FIRST_NAME_MIN_LENGTH}{$re2}", 'data-required' => "{sprintf($smarty.const.ENTRY_FIRST_NAME_ERROR, $smarty.const.ENTRY_FIRST_NAME_MIN_LENGTH)}"])}
            {else}
                {Html::activeTextInput($editModel, 'firstname')}
            {/if}
        </div>
    {/if}
    {if in_array(ACCOUNT_LASTNAME, ['required', 'required_register', 'visible', 'visible_register'])}
        <div class="col-right">
            <label for="{$editModel->formName()}-lastname">{field_label const="ENTRY_LAST_NAME" configuration="ACCOUNT_LASTNAME"}</label>
            {if in_array(ACCOUNT_LASTNAME, ['required', 'required_register'])}
                {Html::activeTextInput($editModel, 'lastname', ['data-pattern' => "{$re1}{$smarty.const.ENTRY_LAST_NAME_MIN_LENGTH}{$re2}", 'data-required' => "{sprintf($smarty.const.ENTRY_LAST_NAME_ERROR, $smarty.const.ENTRY_LAST_NAME_MIN_LENGTH)}"])}
            {else}
                {Html::activeTextInput($editModel, 'lastname')}
            {/if}
        </div>
    {/if}

    {if in_array(ACCOUNT_TELEPHONE, ['required', 'required_register', 'visible', 'visible_register'])}
        <div class="col-left">
            <label for="{$editModel->formName()}-telephone">{field_label const="ENTRY_TELEPHONE_NUMBER" configuration="ACCOUNT_TELEPHONE"}</label>
            {if in_array(ACCOUNT_TELEPHONE, ['required', 'required_register'])}
                {Html::activeTextInput($editModel, 'telephone', ['data-required' => "{sprintf($smarty.const.ENTRY_TELEPHONE_NUMBER_ERROR, $smarty.const.ENTRY_TELEPHONE_MIN_LENGTH)}", 'data-pattern' => "{$re1}{$smarty.const.ENTRY_TELEPHONE_MIN_LENGTH}{$re2}"])}
            {else}
                {Html::activeTextInput($editModel, 'telephone')}
            {/if}
        </div>
    {/if}
	<div class="col-right">
        <label for="{$editModel->formName()}-email_address">{field_label const="ENTRY_EMAIL_ADDRESS" required_text="*"}</label>
        {Html::activeInput('email', $editModel, 'email_address', ['data-required' => "{$smarty.const.EMAIL_REQUIRED}", 'data-pattern' => "email"])}
    </div>
    {if in_array(ACCOUNT_LANDLINE, ['required', 'required_register', 'visible', 'visible_register'])}
        <div class="col-left">
            <label for="{$editModel->formName()}-landline">{field_label const="ENTRY_LANDLINE" configuration="ACCOUNT_LANDLINE"}</label>
            {if in_array(ACCOUNT_LANDLINE, ['required', 'required_register'])}
                {Html::activeTextInput($editModel, 'landline', ['data-required' => "{sprintf($smarty.const.ENTRY_LANDLINE_NUMBER_ERROR, $smarty.const.ENTRY_LANDLINE_MIN_LENGTH)}", 'data-pattern' => "{$re1}{$smarty.const.ENTRY_LANDLINE_MIN_LENGTH}{$re2}"])}
            {else}
                {Html::activeTextInput($editModel, 'landline')}
            {/if}
        </div>
    {/if}


    {if in_array(ACCOUNT_DOB, ['required', 'required_register', 'visible', 'visible_register'])}
        <div class="col-full dob-input">
            <label for="dob">{field_label const="ENTRY_DATE_OF_BIRTH" configuration="ACCOUNT_DOB"} </label>
            {assign var="options" value = ['class' => "datepicker dobTmp"]}
            {if in_array(ACCOUNT_DOB, ['required', 'required_register'])} {$options['data-required'] = "{$smarty.const.ENTRY_DATE_OF_BIRTH_ERROR}"}{$options['data-pattern'] = "{$re1}4{$re2}"}{/if}
            {Html::activeTextInput($editModel, 'dobTmp', $options)}
            {Html::activeHiddenInput($editModel, 'dob', ['class' => 'dob-res'])}
        </div>
    {/if}

    {foreach \common\helpers\Hooks::getList('box/account/account-edit', 'after-main-fields') as $filename}
        {include file=$filename}
    {/foreach}

    {if $CustomersMultiEmails = Acl::checkExtensionAllowed('CustomersMultiEmails', 'allowed')}
      <div class="col-full">{$CustomersMultiEmails::frontendViewCustomerEdit()}</div>
    {/if}

    <div class="required requiredM">{$smarty.const.FORM_REQUIRED_INFORMATION}</div>

    <div class="center-buttons"><button type="submit" class="btn-2"><span class="button">{$smarty.const.IMAGE_BUTTON_UPDATE}</span></button></div>
    {Html::endForm()}
	</div>
</div>



<script type="text/javascript">
    tl([
        '{Info::themeFile('/js/main.js')}'
    ], function(){
        var form = $('#box-{$id} form');
        $('input', form).validate();
        form.on('submit', function(){
            if ($('.required-error', form).length === 0){
                $.post(form.attr('action'), form.serialize(), function(data){
                    var messages = '';
                    $.each(data.messages, function(key, val){
                        messages += '<div class="message '+val['type']+'">'+val.text+'</div>';
                        if (val['.type'] === 'success') {
                            setTimeout(function(){
                                $('.pop-up-close').trigger('click')
                            }, 1000)
                        }
                    });
                    $('.messages', form).html(messages)
                }, 'json')
            }
            return false;
        });
    });


    {if in_array(ACCOUNT_DOB, ['required', 'required_register', 'visible', 'visible_register'])}

    tl(['{Info::themeFile('/js/bootstrap.min.js')}',
        '{Info::themeFile('/js/bootstrap-datepicker.js')}'
    ], function(){
        $('head').prepend('<link rel="stylesheet" href="{Info::themeFile('/css/bootstrap-datepicker.css')}">');

        $.fn.datepicker.dates.current={
            days:["{$smarty.const.TEXT_SUNDAY}","{$smarty.const.TEXT_MONDAY}","{$smarty.const.TEXT_TUESDAY}","{$smarty.const.TEXT_WEDNESDAY}","{$smarty.const.TEXT_THURSDAY}","{$smarty.const.TEXT_FRIDAY}","{$smarty.const.TEXT_SATURDAY}"],
            daysShort:["{$smarty.const.DATEPICKER_DAY_SUN}","{$smarty.const.DATEPICKER_DAY_MON}","{$smarty.const.DATEPICKER_DAY_TUE}","{$smarty.const.DATEPICKER_DAY_WED}","{$smarty.const.DATEPICKER_DAY_THU}","{$smarty.const.DATEPICKER_DAY_FRI}","{$smarty.const.DATEPICKER_DAY_SAT}"],
            daysMin:["{$smarty.const.DATEPICKER_DAY_SU}","{$smarty.const.DATEPICKER_DAY_MO}","{$smarty.const.DATEPICKER_DAY_TU}","{$smarty.const.DATEPICKER_DAY_WE}","{$smarty.const.DATEPICKER_DAY_TH}","{$smarty.const.DATEPICKER_DAY_FR}","{$smarty.const.DATEPICKER_DAY_SA}"],
            months:["{$smarty.const.DATEPICKER_MONTH_JANUARY}","{$smarty.const.DATEPICKER_MONTH_FEBRUARY}","{$smarty.const.DATEPICKER_MONTH_MARCH}","{$smarty.const.DATEPICKER_MONTH_APRIL}","{$smarty.const.DATEPICKER_MONTH_MAY}","{$smarty.const.DATEPICKER_MONTH_JUNE}","{$smarty.const.DATEPICKER_MONTH_JULY}","{$smarty.const.DATEPICKER_MONTH_AUGUST}","{$smarty.const.DATEPICKER_MONTH_SEPTEMBER}","{$smarty.const.DATEPICKER_MONTH_OCTOBER}","{$smarty.const.DATEPICKER_MONTH_NOVEMBER}","{$smarty.const.DATEPICKER_MONTH_DECEMBER}"],
            monthsShort:["{$smarty.const.DATEPICKER_MONTH_JAN}","{$smarty.const.DATEPICKER_MONTH_FEB}","{$smarty.const.DATEPICKER_MONTH_MAR}","{$smarty.const.DATEPICKER_MONTH_APR}","{$smarty.const.DATEPICKER_MONTH_MAY}","{$smarty.const.DATEPICKER_MONTH_JUN}","{$smarty.const.DATEPICKER_MONTH_JUL}","{$smarty.const.DATEPICKER_MONTH_AUG}","{$smarty.const.DATEPICKER_MONTH_SEP}","{$smarty.const.DATEPICKER_MONTH_OCT}","{$smarty.const.DATEPICKER_MONTH_NOV}","{$smarty.const.DATEPICKER_MONTH_DEC}"],
            today:"{$smarty.const.TEXT_TODAY}",
            clear:"{$smarty.const.TEXT_CLEAR}",
            weekStart:1
        };

        {\frontend\design\Info::addBoxToCss('datepicker')}
        $('.dobTmp').datepicker({
            startView: 3,
            format: '{$smarty.const.DATE_FORMAT_DATEPICKER}yy',
            language: 'current',
            autoclose: true
        }).on('changeDate', function(e){
            var date = e.date;
            $('.dob-res').val(new Date(date.getTime() - (date.getTimezoneOffset() * 60000)).toISOString());
        });
    });

    {/if}
</script>