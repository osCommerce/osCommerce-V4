{use class = "yii\helpers\Html"}
{use class = "frontend\design\Info"}
{Info::addBoxToCss('info')}
{Info::addBoxToCss('form')}
{Info::addBoxToCss('datepicker')}

{Html::beginForm($url, 'post', ['name' => 'product_edit'])}
<div class="gift-card-form form-inputs columns">
  {$messages}
  <div class="">
    <div class="col-2">
      <label>
        <span>{field_label const="SELECT_DESIGN" required_text=""}</span>
        <select name="gift_card_design" class="gift-card-design">
            {foreach $cardDesigns as $value => $title}
              <option value="{$value}" {if $gift->gift_card_design == $value}selected{/if}>{$title}</option>
            {/foreach}
        </select>
      </label>
    </div>
    <div class="col-2">
      <label>
        <span>{field_label const="SELECT_AMOUNT" required_text=""}</span>
        <select name="gift_card_price" class="gift-amount">
            {foreach $giftAmount as $value => $giftValue}
              <option value="{$value}" {if $gift->products_price == $value}selected{/if} data-value="{$giftValue['price']}" >{$giftValue['text']}</option>
            {/foreach}
        </select>
      </label>
    </div>
  </div>
  <div class="col-1" style="clear:both;">
    <label>
      <span>{field_label const="PERSONAL_MESSAGE" required_text=""}</span>
      <textarea maxlength="160" cols="30" rows="10" name="virtual_gift_card_message" class="gift-message">{$gift->virtual_gift_card_message}</textarea>
    </label>
    <div class="limitation">{sprintf($smarty.const.CHARACTERS_REMAINING, '160')}</div>
  </div>
  <div class="col-1">
    <label>
      <span>{field_label const="RECIPIENTS_NAME" required_text=""}</span>
      <input type="text" name="virtual_gift_card_recipients_name" value="{$gift->virtual_gift_card_recipients_name}" />
    </label>
  </div>
  <div>
    <div class="col-2">
      <label>
        <span>{field_label const="RECIPIENTS_EMAIL" required_text="*"}</span>
        {Html::input('text', 'virtual_gift_card_recipients_email', $gift->virtual_gift_card_recipients_email, ['data-required' => "{$smarty.const.EMAIL_REQUIRED}", 'id' => 'virtual_gift_card_recipients_email'])}
      </label>
    </div>
    <div class="col-2">
      <label>
        <span>{field_label const="TEXT_CONFIRM_EMAIL" required_text="*"}</span>
        {Html::input('text', 'virtual_gift_card_confirm_email', $gift->virtual_gift_card_recipients_email, ['data-required' => "{$smarty.const.EMAIL_REQUIRED}", 'data-confirmation' => "#virtual_gift_card_recipients_email"])}
      </label>
    </div>
  </div>
  <div class="col-1">
    <label>
      <span>{field_label const="TEXT_YOUR_NAME" required_text=""}</span>
      <input type="text" name="virtual_gift_card_senders_name" value="{$gift->virtual_gift_card_senders_name}"/>
    </label>
  </div>
  <div class="col-1">
    <label>
        <span>{field_label const="VIRTUAL_DATE_TO_SEND"}</span>
        {Html::radioList('send_card_date', $sendType, ['0' => TEXT_SEND_NOW, '1' => TEXT_SELECT_SEND_DATE])}
        {assign var="vars" value=['class' => 'datepicker', 'autocomplete' => 'off']}
        {if $sendType == 0}
            {$vars['disabled'] = 'disabled'}
        {/if}
        {Html::textInput('send_card_date_value', $gift->send_card_date, $vars)}
    </label>
  </div>  
  <div class="button"><button class="btn-2 add-to-cart">{$smarty.const.ADD_TO_CART}</button></div>
</div>
{Html::endForm()}
<script type="text/javascript">
    tl([
        '{Info::themeFile('/js/main.js')}',
        '{Info::themeFile('/js/bootstrap.min.js')}',
        '{Info::themeFile('/js/bootstrap-datepicker.js')}',
    ],function(){
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
    
        var box = $('#box-{$id}');

        var cardContainer = $('.gift-card-container');
        var giftCardDesign = $('.gift-card-design', box);
        var giftAmount = $('.gift-amount', box);
        var giftMessage = $('.gift-message', box);

        giftCardDesign.on('change', changeCard);
        var today = new Date();        
        var tomorrow = today.setDate(today.getDate() + 1);
        console.log(tomorrow);
        $('.datepicker').datepicker({
            startView: 0,
            format: '{$smarty.const.DATE_FORMAT_DATEPICKER}yy',
            language: 'current',
            autoclose: true,
            startDate: new Date(tomorrow),
        });
        
        $('input[name=virtual_gift_card_recipients_email], input[name=virtual_gift_card_confirm_email]').validate();
        
        $('input[name=send_card_date]:radio').change(function(){
            if ($(this).val() == 1){
                $('.datepicker').prop('disabled', false);
            } else {
                $('.datepicker').prop('disabled', true);
            }
        })

        function changeCard(){
            $.get('catalog/gift', {
                theme_name: '{$theme_name}',
                page_name: giftCardDesign.val()
            }, function(response){

                var html = $(response);
                //$('.box', html).removeClass('box');
                //$('.block', html).removeClass('block');
                //$('.box-block', html).removeClass('box-block');

                cardContainer.html(html);

                var amountView = $('.amount-view');
                var messageView = $('.message-view');
                var giftCodeView = $('.gift-code-view');

                amountView.html(giftAmount.find('option:selected').data('value'));
                giftAmount.off().on('change', function(){
                    amountView.html(giftAmount.find('option:selected').data('value'));
                });

                giftCodeView.html('XXXXX');
                messageView.text(giftMessage.val());
                giftMessage.off().on('keyup change', function(){
                    messageView.text(giftMessage.val());
                });

            })
        }
        changeCard();
    })
</script>