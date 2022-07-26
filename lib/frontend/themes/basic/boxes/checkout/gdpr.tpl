{use class="Yii"}
{use class="frontend\design\Info"}
{use class="yii\helpers\Html"}


{Info::addBoxToCss('switch')}
{Info::addBoxToCss('datepicker')}
{$model = $manager->getCustomerContactForm()}
<div class="columns">
	<div class="col-left">
		{if in_array(ACCOUNT_DOB, ['required_register', 'visible_register']) && ACCOUNT_GDPR == 'true'}
            <div class="col-full">
                <div class="col-full">
                    <label for="gdpr" style="display: inline;" class="slim">
                        {Html::activeCheckbox($model, 'gdpr', ['class' => "candlestick gdpr", 'label' => {$smarty.const.TEXT_AGE_OVER}, 'value' => ''])}
                        <span class="checkbox-span"></span>
                    </label>
                </div>
                <div class="col-full dob-hide columns form-inputs" style="display: none;">
                    <label for="dob"><span>{field_label const="ENTRY_DATE_OF_BIRTH" configuration="ACCOUNT_DOB"}</span></label>
                    <div class="" style="position: relative">
                        {assign var="options" value = ['class' => "datepicker dobTmp"]}
                        {if ACCOUNT_DOB == 'required_register'} {$options['data-required'] = "{$smarty.const.ENTRY_DATE_OF_BIRTH_ERROR}"}{/if}
                        {Html::activeTextInput($model, 'dobTmp', $options)}
                        {Html::activeHiddenInput($model, 'dob', ['class' => 'dob-res'])}
                    </div>
                </div>
            </div>
        {elseif in_array(ACCOUNT_DOB, ['required_register', 'visible_register'])}
            <div class="col-full">
                <label for="dob">{field_label const="ENTRY_DATE_OF_BIRTH" configuration="ACCOUNT_DOB"} </label>
                <div class="" style="position: relative">
                    {assign var="options" value = ['class' => "datepicker dobTmp"]}
                    {if ACCOUNT_DOB == 'required_register'} {$options['data-required'] = "{$smarty.const.ENTRY_DATE_OF_BIRTH_ERROR}"}{/if}
                    {Html::activeTextInput($model, 'dobTmp', $options)}
                    {Html::activeHiddenInput($model, 'dob', ['class' => 'dob-res'])}
                </div>
            </div>
        {/if}
	</div>
	<div class="col-right">
		<div class="col-full privacy-row">
            <div class="terms-login">
                {Html::activeCheckbox($model, 'terms', ['class' => 'terms-conditions', 'value' => '1', 'label' => ''])}{$smarty.const.TEXT_TERMS_CONDITIONS}
            </div>
        </div>
	</div>
</div>
<script type="text/javascript">
    var ageStatement = 'default';    
    {if $model->gdpr == '1' || ACCOUNT_GDPR != 'true'}
        ageStatement = 'on';
    {else if $model->gdpr == '0'}
        ageStatement = 'off';
    {/if};
    var offersStatement = 'default';

    tl([
        '{Info::themeFile('/js/main.js')}',
        '{Info::themeFile('/js/bootstrap-switch.js')}',
        '{Info::themeFile('/js/hammer.js')}',
        '{Info::themeFile('/js/candlestick.js')}',
        '{Info::themeFile('/js/bootstrap.min.js')}',
        '{Info::themeFile('/js/bootstrap-datepicker.js')}',
    ], function () {
        var box = $('#box-{$id}');
        var dob = $('.dobTmp', box);
        var termsConditions = false;
        {if $model->terms}
            termsConditions = true;
        {/if}

        $('head').prepend('<link rel="stylesheet" href="{Info::themeFile('/css/bootstrap-datepicker.css')}">');

        $.fn.datepicker.dates.current={
            days:["{$smarty.const.TEXT_SUNDAY}","{$smarty.const.TEXT_MONDAY}","{$smarty.const.TEXT_TUESDAY}","{$smarty.const.TEXT_WEDNESDAY}","{$smarty.const.TEXT_THURSDAY}","{$smarty.const.TEXT_FRIDAY}","{$smarty.const.TEXT_SATURDAY}"],
            daysShort:["{$smarty.const.DATEPICKER_DAY_SUN}","{$smarty.const.DATEPICKER_DAY_MON}","{$smarty.const.DATEPICKER_DAY_TUE}","{$smarty.const.DATEPICKER_DAY_WED}","{$smarty.const.DATEPICKER_DAY_THU}","{$smarty.const.DATEPICKER_DAY_FRI}","{$smarty.const.DATEPICKER_DAY_SAT}"],
            daysMin:["{$smarty.const.DATEPICKER_DAY_SU}","{$smarty.const.DATEPICKER_DAY_MO}","{$smarty.const.DATEPICKER_DAY_TU}","{$smarty.const.DATEPICKER_DAY_WE}","{$smarty.const.DATEPICKER_DAY_TH}","{$smarty.const.DATEPICKER_DAY_FR}","{$smarty.const.DATEPICKER_DAY_SA}"],
            months:["{$smarty.const.DATEPICKER_MONTH_JANUARY}","{$smarty.const.DATEPICKER_MONTH_FEBRUARY}","{$smarty.const.DATEPICKER_MONTH_MARCH}","{$smarty.const.DATEPICKER_MONTH_APRIL}","{$smarty.const.DATEPICKER_MONTH_MAY}","{$smarty.const.DATEPICKER_MONTH_JUNE}","{$smarty.const.DATEPICKER_MONTH_JULY}","{$smarty.const.DATEPICKER_MONTH_AUGUST}","{$smarty.const.DATEPICKER_MONTH_SEPTEMBER}","{$smarty.const.DATEPICKER_MONTH_OCTOBER}","{$smarty.const.DATEPICKER_MONTH_NOVEMBER}","{$smarty.const.DATEPICKER_MONTH_DECEMBER}"],
            monthsShort:["{$smarty.const.DATEPICKER_MONTH_JAN}","{$smarty.const.DATEPICKER_MONTH_FEB}","{$smarty.const.DATEPICKER_MONTH_MAR}","{$smarty.const.DATEPICKER_MONTH_APR}","{$smarty.const.DATEPICKER_MONTH_MAY}","{$smarty.const.DATEPICKER_MONTH_JUN}","{$smarty.const.DATEPICKER_MONTH_JUL}","{$smarty.const.DATEPICKER_MONTH_AUG}","{$smarty.const.DATEPICKER_MONTH_SEP}","{$smarty.const.DATEPICKER_MONTH_OCT}","{$smarty.const.DATEPICKER_MONTH_NOV}","{$smarty.const.DATEPICKER_MONTH_DEC}"],
            today:"{$smarty.const.TEXT_TODAY|strip}",
            clear:"{$smarty.const.TEXT_CLEAR|strip}",
            weekStart:1
        };

        dob.datepicker({
            startView: 3,
            format: '{$smarty.const.DATE_FORMAT_DATEPICKER}yy',
            language: 'current',
            autoclose: true
        }).on('changeDate', function(e){
            var date = e.date;
            $('.dob-res', box).val(new Date(date.getTime() - (date.getTimezoneOffset() * 60000)).toISOString());
        }).removeClass('required-error').next('.required-message-wrap').remove();


        $('.pop-up-link').popUp();

        var disableButton = function(e){
            //e.preventDefault();
            //return false;
        };

        //$('.disabled-area', box).on('click', disableButton);

        $(".check-on-off", box).bootstrapSwitch({
            offText: '{$smarty.const.TEXT_NO}',
            onText: '{$smarty.const.TEXT_YES}',
            onSwitchChange: function () {
                $(this).closest('form').trigger('cart-change')
            }
        });
        var form = $('button[type="submit"]').closest('form');
        {if !$model->hasErrors()}
		//$('button[type="submit"]', form).addClass('disabled-area').on('click', disableButton);
        {/if}
        $(".terms-conditions", box).bootstrapSwitch({
            offText: '{$smarty.const.TEXT_NO}',
            onText: '{$smarty.const.TEXT_YES}',
            onSwitchChange: function (d, e) {
                var form = $(this).closest('form');
                form.trigger('cart-change');
                if(e){
                    termsConditions = true;
                    //$('button[type="submit"]', form).removeClass('disabled-area').off('click', disableButton);
                    switchHidingBox()
                }else{
                    termsConditions = false;
                    //$('button[type="submit"]', form).addClass('disabled-area').on('click', disableButton);
                    switchHidingBox()
                }
            }
        });  
        
        $('.candlestick', box).candlestick({
            afterAction: function(obj, wrap, val) {
                if ($(obj).hasClass('newsletter')) {
                    offersStatement = val;
                    if (val === 'on') {
                        $('.regular_offers_box', box).show();
                    } else {
                        $('.regular_offers_box', box).hide();
                    }
                }
                if ($(obj).hasClass('gdpr')) {
                    ageStatement = val;
                    if (val === 'on') {
                        dob.attr('disabled', 'disabled').addClass('skip-validation');
                        $('.dob-hide', box).hide();
                        switchHidingBox()
                    } else if (val === 'default') {
                        dob.removeAttr('disabled').removeClass('skip-validation');
                        $('.dob-hide', box).hide();
                        switchHidingBox()
                    } else {
                        dob.removeAttr('disabled').removeClass('skip-validation');
                        $('.dob-hide', box).show();
                        switchHidingBox()
                    }
                }
            },
        }).candlestick(ageStatement);
        
        let gdpr = $('.w-checkout-gdpr');
        let fields = $('input, select', gdpr);
        fields.validate();

        var count = 0;
        $('form', box).on('submit', function(e){
            if (!document.register.querySelector('.terms-conditions').checked){
                alertMessage('{$smarty.const.TEXT_PLEASE_TERMS}');
                return false;
            }            
{if in_array(ACCOUNT_DOB, ['required_register', 'visible_register']) && ACCOUNT_GDPR == 'true'}
            if (ageStatement === 'default') {
                alertMessage('{$smarty.const.TEXT_PLEASE_AGE}');
                return false;
            }
{/if}            
            if (count > 0){
                setTimeout(function(){
                    count = 0
                }, 1000);
                e.preventDefault();
                return false;
            }
            count++;
        });

        function switchHidingBox() {
            var hidingBox = $('.hiding-box');
            if (ageStatement !== 'default' && termsConditions) {
                hidingBox.removeClass('hide-box')
            } else {
                hidingBox.addClass('hide-box')
            }
        }
        switchHidingBox()
    })

</script>