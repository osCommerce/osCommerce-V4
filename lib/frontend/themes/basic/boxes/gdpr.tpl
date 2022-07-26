{use class="frontend\design\Info"}
{use class="frontend\design\boxes\ReCaptchaWidget"}
{\frontend\design\Info::addBoxToCss('form')}
{\frontend\design\Info::addBoxToCss('info')}
{\frontend\design\Info::addBoxToCss('datepicker')}
{\frontend\design\Info::addBoxToCss('switch')}

{$rndId = rand()}
<div class="gdpr-form" data-id="{$rndId}">
<div class="col-full">
    <div class="col-left col-full-margin">
        <label for="gdpr" style="display: inline;">
            <input type="checkbox" name="gdpr" class="candlestick gdpr" id="candlestick_gdpr"/>
            <label for="">{$smarty.const.TEXT_AGE_OVER}</label>
        </label>
    </div>
    <div class="col-right dob-hide" style="display: none;">
        <div class="col-left">
            <label for="dob2">{field_label const="ENTRY_DATE_OF_BIRTH" configuration="ACCOUNT_DOB"} </label>
        </div>
        <div class="col-right">
            <div style="position: relative">
                <input type="text" name="dobTmp" class="datepicker dobTmp" {if ACCOUNT_DOB == 'required_register'} required {/if}/>
                <input type="hidden" name="dob" class="dob-res"/>
            </div>
        </div>
    </div>
</div>

    <div class="terms-login col-full">
        <input type="checkbox" name="terms" class="terms-conditions"/>
        {$smarty.const.TEXT_TERMS_CONDITIONS}
    </div>
</div>


<script type="text/javascript">

    tl(['{Info::themeFile('/js/bootstrap.min.js')}',
        '{Info::themeFile('/js/bootstrap-datepicker.js')}',
        '{Info::themeFile('/js/main.js')}',
        '{Info::themeFile('/js/bootstrap-switch.js')}',
        '{Info::themeFile('/js/hammer.js')}',
        '{Info::themeFile('/js/candlestick.js')}'
    ], function(){
        var box = $('.gdpr-form[data-id="{$rndId}"]');
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
            $('.dob-res', box).val(new Date(date.getTime() - (date.getTimezoneOffset() * 60000)).toISOString());
        }).removeClass('required-error').next('.required-message-wrap').remove();

        var disableButton = function(e){
            e.preventDefault();
            return false;
        };

        box.closest('form').find('button').addClass('disabled-area').on('click', disableButton);

        $(".terms-conditions", box).bootstrapSwitch({
            offText: '{$smarty.const.TEXT_NO}',
            onText: '{$smarty.const.TEXT_YES}',
            onSwitchChange: function (d, e) {
                var form = $(this).closest('form');
                form.trigger('cart-change');
                if(e){
                    $('button', form).removeClass('disabled-area').off('click', disableButton);
                }else{
                    $('button', form).addClass('disabled-area').on('click', disableButton);
                }
            }
        });

        $('.candlestick', box).candlestick({
            afterAction: function(obj, wrap, val) {
                if ($(obj).hasClass('gdpr')) {
                    ageStatement = val;
                    if (val === 'on') {
                        dob.attr('disabled', 'disabled').addClass('skip-validation');
                        $('.dob-hide', box).hide();
                    } else if (val === 'default') {
                        dob.removeAttr('disabled').removeClass('skip-validation');
                        $('.dob-hide', box).hide();
                    } else {
                        dob.removeAttr('disabled').removeClass('skip-validation');
                        $('.dob-hide', box).show();
                    }
                }
            },
        });
    });
</script>