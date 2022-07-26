{use class="Yii"}
{use class="frontend\design\Info"}
{use class="yii\helpers\Html"}
{\frontend\design\Info::addBoxToCss('info')}
{\frontend\design\Info::addBoxToCss('form')}
<h1>{$smarty.const.HEADING_TITLE}</h1>
{if !empty($messages)}
  <div class="middle-form">
      {$messages}
      {if !$mistake}
        <div class="buttons">
          <div class="right-buttons"><a class="btn-1" href="{Yii::$app->urlManager->createUrl('account/')}">{$smarty.const.IMAGE_BUTTON_CONTINUE}</a></div>
        </div>
    {/if}
  </div>
{/if}
{if !$error}
{Html::beginForm(['account/update', 'token' => $token], 'post', ['name' => 'rdpr_update', 'id' => 'rdpr_update', 'onsubmit' => 'return checkRDPR();'])}
  <div class="middle-form">

    <div class="col-full">
      <label>{field_label const="PROVIDE_DATE_OF_BIRTH" configuration="ACCOUNT_DOB"} </label>
      <input type="text" name="dobTmp" id="dob" value="{$customers_dob|escape:'html'}"{if ACCOUNT_DOB == 'required_register'} data-required="{$smarty.const.ENTRY_DATE_OF_BIRTH_ERROR}"{/if} />
      <input type="hidden" name="dob" id="dobRes" value="{$customers_dobTmp|escape:'html'}" />
    </div>

    
    {if \common\helpers\Acl::checkExtensionAllowed('Subscribers', 'allowed') && defined('ENABLE_CUSTOMERS_NEWSLETTER') && ENABLE_CUSTOMERS_NEWSLETTER == 'true' }
      <div class="col-left">
          <label class="slim">
              <input type="checkbox" name="newsletter" value="1" id="newsletter" class="checkbox-style" onchange="return gdprRegularOffers(this);" {if $customers_newsletter} checked="checked"{/if}/><span class="checkbox-span"></span>
              {$smarty.const.RECEIVE_REGULAR_OFFERS}
          </label>
      </div>
      <div class="col-right regular_offers_box"{if !$customers_newsletter} style="display: none;"{/if}>
          <label for="regular_offers">{$smarty.const.RECEIVE_REGULAR_OFFERS_PERIOD}</label>
          {Html::dropDownList('regular_offers', $regular_offers, ['12' => '12 months', '24' => '24 months', '36' => '36 months', '60' => '60 months', '0' => 'indefinitely'], ['id' => "regular_offers"])}
      </div>
    {/if}


      <div class="col-full privacy-row">
          <div class="terms-login">
              <input type="checkbox" name="rdpr" value="1" id="rdpr" class="check-on-off"/>{$smarty.const.TEXT_TERMS_CONDITIONS}
          </div>
      </div>


    <div class="center-buttons">
      <button class="btn-2 disabled-area" type="submit">{$smarty.const.TEXT_SAVE}</button>
    </div>

      <div class="info">
          <b>{$smarty.const.WHAT_IS_GDPR}</b><br>
          {$smarty.const.GDPR_INFO}
      </div>
  </div>
{Html::endForm()}
{/if}
<script type="text/javascript">
    function gdprRegularOffers(obj) {
        if ($(obj).prop('checked') == true) {
            $('.regular_offers_box').show();
        } else {
            $('.regular_offers_box').hide();
        }
        return true;
    }
function checkRDPR() {
    if (document.rdpr_update.rdpr.checked) {
            return true;
    }
    alert('{$smarty.const.TEXT_PLEASE_RDPR}');
    return false;
}
    tl(['{Info::themeFile('/js/bootstrap.min.js')}',
        '{Info::themeFile('/js/main.js')}',
        '{Info::themeFile('/js/bootstrap-datepicker.js')}',
        '{Info::themeFile('/js/bootstrap-switch.js')}'
    ], function(){
        $('head').prepend('<link rel="stylesheet" href="{Info::themeFile('/css/bootstrap.css')}">')
                 .prepend('<link rel="stylesheet" href="{Info::themeFile('/css/bootstrap-datepicker.css')}">');

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

      {\frontend\design\Info::addBoxToCss('datepicker')}
        $('#dob').datepicker({
            startView: 3,
            format: '{$smarty.const.DATE_FORMAT_DATEPICKER}yy',
            language: 'current',
            autoclose: true
        }).on('changeDate', function(ev){
            var date = $('#dob').datepicker("getDate");
            $('#dobRes').val(new Date(date.getTime() - (date.getTimezoneOffset() * 60000)).toISOString());
        });
        
         $('.pop-up-link').popUp();

        $('.middle-form input').validate();

        var disableButton = function(e){
            e.preventDefault();
            return false;
        };

        $('.disabled-area').on('click', disableButton);

        {\frontend\design\Info::addBoxToCss('switch')}
        $(".check-on-off").bootstrapSwitch({
            offText: '{$smarty.const.TEXT_NO}',
            onText: '{$smarty.const.TEXT_YES}',
            onSwitchChange: function (d, e) {
                var form = $(this).closest('form');
                if(e){
                    $('button[type="submit"]', form).removeClass('disabled-area').off('click', disableButton);
                }else{
                    $('button[type="submit"]', form).addClass('disabled-area').on('click', disableButton);
                }
            }
        });
    });
</script>