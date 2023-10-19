{use class="frontend\design\Info"}
{use class="frontend\design\boxes\ReCaptchaWidget"}
{use class = "yii\helpers\Html"}
{\frontend\design\Info::addBoxToCss('form')}
{\frontend\design\Info::addBoxToCss('info')}
{\frontend\design\Info::addBoxToCss('datepicker')}
{\frontend\design\Info::addBoxToCss('switch')}
{if $info|count > 0}
  {foreach $info as $_info}
  <div class="info">{$_info}</div>
  {/foreach}
{/if}

{if $action == 'success'}

  <div style="text-align: center; font-size: 20px; margin: 20px 0 100px">{$smarty.const.TEXT_MESSAGE_IS_SENT}</div>

{else}
  {\frontend\design\Info::addBoxToCss('form')}
{Html::beginForm(['', 'action' => 'send', 'info_id' => $info_id], 'post', ['id' => 'contact-form'])}
<div class="contact-info form-inputs">
  <div class="col-full">
    <label>
      <span>{field_label const="TEXT_NAME" required_text="*"}</span>
      {Html::activeTextInput($contact, 'name', ['data-required' => "{$smarty.const.NAME_REQUIRED}"])}
    </label>
  </div>
  <div class="col-full">
    <label>
      <span>{field_label const="TEXT_EMAIL" required_text="*"}</span>
      {Html::activeInput('email', $contact, 'email_address', ['data-required' => "{$smarty.const.EMAIL_REQUIRED}", 'data-pattern' => "email"])}
    </label>
  </div>
  {if $ext = \common\helpers\Extensions::isAllowed('CustomerAdditionalFields')}
    {$ext::contactBlock()}
  {/if}
  <div class="col-full">
    <label>
      <span>{field_label const="TEXT_ENQUIRY" required_text="*"}</span>
      {Html::activeTextarea($contact, 'content', ['data-required' => "{$smarty.const.ENQUIRY_REQUIRED}", 'cols' => "30", 'rows' => "10"])}
    </label>
  </div>

    {if in_array(ACCOUNT_DOB, ['required_register', 'visible_register']) && !$contact->customer_id}
      <div class="col-full-padding">
        <div class="col-left col-full-margin" style="padding-top: 5px">
          <label for="gdpr" style="display: inline;">
            {Html::activeCheckbox($contact, 'gdpr', ['class' => "candlestick gdpr", 'label' => {$smarty.const.TEXT_AGE_OVER}, 'value' => $contact->gdpr])}
          </label>
        </div>

        <div class="col-right dob-hide" style="display: none;">
          <div class="col-left" style="padding-top: 5px"><label for="dob2">{field_label const="ENTRY_DATE_OF_BIRTH" configuration="ACCOUNT_DOB"} </label></div>
          <div class="col-right">
            <div style="position: relative">
                {assign var="options" value = ['class' => "datepicker dobTmp"]}
                {if ACCOUNT_DOB == 'required_register'} {$options['data-required'] = "{$smarty.const.ENTRY_DATE_OF_BIRTH_ERROR}"}{/if}
                {Html::activeTextInput($contact, 'dobTmp', $options)}
                {Html::activeHiddenInput($contact, 'dob', ['class' => 'dob-res'])}
            </div>
          </div>

        </div>

      </div>
    {/if}

    {if !$contact->customer_id}
  <div class="col-full privacy-row">
    <div class="terms-login">
      {Html::activeCheckbox($contact, 'terms', ['class' => 'terms-conditions', 'value' => '1', 'label' => '', 'checked' => false])}{$smarty.const.TEXT_TERMS_CONDITIONS}
    </div>
  </div>
    {/if}

    {$contact->captcha_widget}
    
  <div class="buttons">
    <div class="right-buttons"><button type="submit" class="btn{if !$contact->customer_id} disabled-area{/if}">{$smarty.const.CONTINUE}</button></div>
  </div>
</div>
{Html::endForm()}

{/if}

<script type="text/javascript">

  tl(['{Info::themeFile('/js/bootstrap.min.js')}',
      '{Info::themeFile('/js/bootstrap-datepicker.js')}',
      '{Info::themeFile('/js/main.js')}',
      '{Info::themeFile('/js/bootstrap-switch.js')}',
      '{Info::themeFile('/js/hammer.js')}',
      '{Info::themeFile('/js/candlestick.js')}'
  ], function(){        
        var box = $('#box-{$id}');        
        var dob = $('.dobTmp', box);

        $('#contact-form input, #contact-form textarea', box).validate();

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