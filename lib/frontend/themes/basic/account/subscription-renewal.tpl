{use class="Yii"}
{use class="frontend\design\Info"}
{use class="yii\helpers\Html"}
{\frontend\design\Info::addBoxToCss('info')}
{\frontend\design\Info::addBoxToCss('form')}
<h1>{$smarty.const.HEADING_TITLE}</h1>
{\frontend\design\Info::addBoxToCss('info')}
{\frontend\design\Info::addBoxToCss('form')}
{if !empty($messages)}
  <div class="middle-form">
      {$messages}
    <div class="buttons">
      <div class="right-buttons"><a class="btn-1" href="{Yii::$app->urlManager->createUrl('account/')}">{$smarty.const.IMAGE_BUTTON_CONTINUE}</a></div>
    </div>
  </div>
{else}
{Html::beginForm(['account/subscription-renewal', 'token' => $token], 'post', [])}
  <div class="middle-form">
      {$messages}
    <div style="margin-bottom: 20px">
      <p>{$smarty.const.TEXT_MAIN}</p>
    </div>
    
    {if \common\helpers\Acl::checkExtensionAllowed('Subscribers', 'allowed') && defined('ENABLE_CUSTOMERS_NEWSLETTER') && ENABLE_CUSTOMERS_NEWSLETTER == 'true' }
    <div class="col-full">
        <label for="newsletter">{$smarty.const.RECEIVE_REGULAR_OFFERS}</label>
        <input type="checkbox" name="newsletter" value="1" id="newsletter" class="check-on-off" onchange="return gdprRegularOffers(this);" {if $customers_newsletter} checked="checked"{/if}/>
        <div class="regular_offers_box"{if !$customers_newsletter} style="display: none;"{/if}>
            <label for="regular_offers">{$smarty.const.RECEIVE_REGULAR_OFFERS_PERIOD}</label>
            {Html::dropDownList('regular_offers', $regular_offers, ['12' => '12 months', '24' => '24 months', '36' => '36 months', '60' => '60 months', '0' => 'indefinitely'], ['id' => "regular_offers"])}
        </div>
    </div>
    {/if}
    
    <div class="buttons">
      <div class="right-buttons"><button class="btn-1" type="submit">{$smarty.const.IMAGE_BUTTON_CONTINUE}</button></div>
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
    tl(['{Info::themeFile('/js/bootstrap.min.js')}',
        '{Info::themeFile('/js/main.js')}',
        '{Info::themeFile('/js/bootstrap-switch.js')}'
    ], function(){
        $('head').prepend('<link rel="stylesheet" href="{Info::themeFile('/css/bootstrap.css')}">')
                 .prepend('<link rel="stylesheet" href="{Info::themeFile('/css/bootstrap-datepicker.css')}">');
         $('.pop-up-link').popUp();

        $('.middle-form input').validate();

        {\frontend\design\Info::addBoxToCss('switch')}
        $(".check-on-off").bootstrapSwitch({
            offText: '{$smarty.const.TEXT_NO}',
            onText: '{$smarty.const.TEXT_YES}',
            onSwitchChange: function () {
                $(this).closest('form').trigger('cart-change')
            }
        });
    });
</script>


