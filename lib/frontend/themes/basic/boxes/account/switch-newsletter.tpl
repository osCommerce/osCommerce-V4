{use class = "yii\helpers\Html"}
{use class="frontend\design\Info"}
{if \common\helpers\Acl::checkExtensionAllowed('Subscribers', 'allowed') && defined('ENABLE_CUSTOMERS_NEWSLETTER') && ENABLE_CUSTOMERS_NEWSLETTER == 'true' }
{Html::beginForm(['account/switch-newsletter'], 'post', [])}
    <span class="info_span">{$smarty.const.TEXT_KEEP_UPTODATE_VIA_EMAIL}</span>
    <input
            type="checkbox"
            name="newsletter_general"
            value="{$params.customer.customers_id}"
            onchange="return gdprRegularOffers(this);"
            class="check-on-off"{if $params.customer.customers_newsletter == 1} checked{/if}>
    <div class="regular_offers_box"{if !$params.customer.customers_newsletter} style="display: none;"{/if}>
        <label for="regular_offers">{$smarty.const.RECEIVE_REGULAR_OFFERS_PERIOD}</label>
        {Html::dropDownList('regular_offers', $params.regular_offers, ['12' => '12 months', '24' => '24 months', '36' => '36 months', '60' => '60 months', '0' => 'indefinitely'], ['id' => "regular_offers", 'onchange' => 'gdprRegularOffersPeriod()'])}
    </div>
{Html::endForm()}
<script>
    function gdprRegularOffers(obj) {
        if ($(obj).prop('checked')) {
            $('.regular_offers_box').show();
        } else {
            $('.regular_offers_box').hide();
        }
        return true;
    }
    function gdprRegularOffersPeriod() {
        var newsletter_general = $('input[name="newsletter_general"]').prop('checked');
        switchStatement(0, newsletter_general);
    }

    tl('{Info::themeFile('/js/bootstrap-switch.js')}', function(){
        {\frontend\design\Info::addBoxToCss('switch')}
        $("#box-{$id} .check-on-off").bootstrapSwitch({
            onSwitchChange: function (element, arguments) {
                switchStatement(element.target.value, arguments);
                if (arguments){
                    {if \common\helpers\Acl::checkExtensionAllowed('BonusActions')}
                    {Yii::$app->controller->promoActionsObs->renderValidPromoAction('signing_newsletter')}
                    {/if}
                }
                return true;
            },
            offText: '{TEXT_NO}',
            onText: '{TEXT_YES}'
        });
    });

    function switchStatement(id, newsletter_general) {
        $.post("account/switch-newsletter", {
            'id': id,
            'newsletter_general' : newsletter_general,
            '_csrf' : $('#box-{$id} input[name="_csrf"]').val(),
            'regular_offers' : $('select[name="regular_offers"]').val()
        }, function(data, status){
            if (status == "success") {
                $('main').html(data);
            } else {
                alert("Request error.");
            }
        },"html");
    }
</script>
{else}
    {if !Info::isAdmin()}
        <script>
            tl(function(){
                {if $settings[0].hide_parents == 1}
                $('#box-{$id}').hide()
                {elseif $settings[0].hide_parents == 2}
                $('#box-{$id}').closest('.box-block').hide()
                {elseif $settings[0].hide_parents == 3}
                $('#box-{$id}').closest('.box-block').closest('.box-block').hide()
                {elseif $settings[0].hide_parents == 4}
                $('#box-{$id}').closest('.box-block').closest('.box-block').closest('.box-block').hide()
                {/if}
            })
        </script>
    {/if}
{/if}