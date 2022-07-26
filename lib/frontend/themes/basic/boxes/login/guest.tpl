{use class="Yii"}
{use class="frontend\design\Info"}
{use class="yii\helpers\Html"}
{Info::addBoxToCss('info')}
{Info::addBoxToCss('form')}
{Info::addBoxToCss('datepicker')}
{*Info::addBoxToCss('pass-strength')*}
{Info::addBoxToCss('switch')}
<div class="login-box">
    {if isset($settings['tabsManually']) && $settings['tabsManually']}
        <div class="login-box-heading">{$smarty.const.CONTINUE_AS_GUEST}</div>
    {/if}
    {if false && !$settings.hideInfo}
    <div class="login-cols">
        <div class="login-col-1">
            <div class="info">
                {$smarty.const.TEXT_GUEST_MESSAGE_1}
            </div>
            <div class="info info-plus">
                {$smarty.const.TEXT_GUEST_MESSAGE_2}
            </div>
        </div>
        <div class="login-col-2">
    {/if}
          <div class="middle-form">

            {$messages_guest}

            {Html::beginForm($action, 'post', ['name' => 'loginguest'], false)}    
            {Html::hiddenInput('scenario', $guestModel->formName())}
            <label for="email_address-1">
                {field_label const="ENTRY_EMAIL_ADDRESS" required_text="*"}
            </label>    
            {Html::activeInput('email', $guestModel, 'email_address', ['data-required' => "{$smarty.const.EMAIL_REQUIRED}", 'data-pattern' => "email"])}

            {if in_array(ACCOUNT_DOB, ['required_register', 'visible_register']) && ACCOUNT_GDPR == 'true'}
                <div class="col-full-padding">
                    <div class="col-left col-full-margin" style="padding-top: 5px">
                        <label for="gdpr" style="display: inline;" class="slim">
                            {Html::activeCheckbox($guestModel, 'gdpr', ['class' => "candlestick gdpr", 'label' => {$smarty.const.TEXT_AGE_OVER}, value => $guestModel->gdpr])}
                            <span class="checkbox-span"></span>
                        </label>
                    </div>
                    <div class="col-right dob-hide2" style="display: none;">
                        <label for="dob">{field_label const="ENTRY_DATE_OF_BIRTH" configuration="ACCOUNT_DOB"} </label>
                        <div class="" style="position: relative">
                            {assign var="options" value = ['class' => "datepicker dobTmp"]}
                            {if ACCOUNT_DOB == 'required_register'} {$options['data-required'] = "{$smarty.const.ENTRY_DATE_OF_BIRTH_ERROR}"}{/if}
                            {Html::activeTextInput($guestModel, 'dobTmp', $options)}
                            {Html::activeHiddenInput($guestModel, 'dob', ['class' => 'dob-res'])}
                        </div>
                    </div>
                </div>
            {elseif in_array(ACCOUNT_DOB, ['required_register', 'visible_register'])}
                <div class="col-left">
                    <label for="dob">{field_label const="ENTRY_DATE_OF_BIRTH" configuration="ACCOUNT_DOB"} </label>
                    <div class="" style="position: relative">
                        {assign var="options" value = ['class' => "datepicker dobTmp"]}
                        {if ACCOUNT_DOB == 'required_register'} {$options['data-required'] = "{$smarty.const.ENTRY_DATE_OF_BIRTH_ERROR}"}{/if}
                        {Html::activeTextInput($guestModel, 'dobTmp', $options)}
                        {Html::activeHiddenInput($guestModel, 'dob', ['class' => 'dob-res'])}
                    </div>
                </div>
            {/if}
                
            <div class="col-full privacy-row">
                <div class="terms-login">{Html::activeCheckbox($guestModel, 'terms', ['class' => 'terms-conditions', 'value' => '1', 'label' => '', 'checked' => ''])}{$smarty.const.TEXT_TERMS_CONDITIONS}</div>
            </div>

            <div class="center-buttons">
                <button type="submit" class="btn-2 btnLoginSingle disabled-area">{$smarty.const.CONTINUE}</button>
            </div>

            {Html::endForm()}
          </div>
    {if false && !$settings.hideInfo}
          <div class="info">{$smarty.const.CAN_CREATE}</div>          
      </div>
    </div>
    {/if}
</div>

<script type="text/javascript">
    var offersStatement = 'default';
    var ageStatement2 = 'default';

    function checkTerms2(form) {
        if (!form.querySelector('.terms-conditions').checked) {
            alert('{$smarty.const.TEXT_PLEASE_TERMS}');
            return false;
        }
{if in_array(ACCOUNT_DOB, ['required_register', 'visible_register']) && ACCOUNT_GDPR == 'true'}
        if (ageStatement2 == 'default') {
            alert('{$smarty.const.TEXT_PLEASE_AGE}');
            return false;
        }
{/if}
        return true;
    }

    tl([
        '{Info::themeFile('/js/bootstrap.min.js')}',
        '{Info::themeFile('/js/bootstrap-datepicker.js')}',
        '{Info::themeFile('/js/main.js')}',
        '{Info::themeFile('/js/jquery.tabs.js')}',
        '{Info::themeFile('/js/bootstrap-switch.js')}',
        '{Info::themeFile('/js/hammer.js')}',
        '{Info::themeFile('/js/candlestick.js')}'
    ], function() {
        var box = $('#box-{$id}');
        var dob = $('.dobTmp', box);

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

        dob.datepicker({
            startView: 3,
            format: '{$smarty.const.DATE_FORMAT_DATEPICKER}yy',
            language: 'current',
            autoclose: true
        }).on('changeDate', function(e){
            var date = e.date;
            $('.dob-res').val(new Date(date.getTime() - (date.getTimezoneOffset() * 60000)).toISOString());
        }).removeClass('required-error').next('.required-message-wrap').remove();


        $('.middle-form input', box).validate();

        var disableButton = function(e){
            e.preventDefault();
            return false;
        };

        $('.disabled-area', box).on('click', disableButton);

        $(".terms-conditions", box).bootstrapSwitch({
            offText: '{$smarty.const.TEXT_NO}',
            onText: '{$smarty.const.TEXT_YES}',
            onSwitchChange: function (d, e) {
                var form = $(this).closest('form');
                form.trigger('cart-change');
                if(e){
                    $('button[type="submit"]', form).removeClass('disabled-area').off('click', disableButton);
                }else{
                    $('button[type="submit"]', form).addClass('disabled-area').on('click', disableButton);
                }
            }
        });

        $('.candlestick', box).candlestick({
            afterAction: function(obj, wrap, val) {
                ageStatement2 = val;
                if (val === 'on') {
                    dob.attr('disabled', 'disabled').addClass('skip-validation');
                    $('.dob-hide2', box).hide();
                } else if (val === 'default') {
                    dob.removeAttr('disabled').removeClass('skip-validation');
                    $('.dob-hide2', box).hide();
                } else {
                    dob.removeAttr('disabled').removeClass('skip-validation');
                    $('.dob-hide2', box).show();
                }
            }
        });

        var count = 0;
        $('form', box).on('submit', function(e){
            if (checkTerms2(e.target)){
                if (count > 0){
                    setTimeout(function(){
                        count = 0
                    }, 1000)
                    e.preventDefault();
                    return false
                }
                count++;
            }
        })

    });


</script>