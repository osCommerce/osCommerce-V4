{use class = "yii\helpers\Html"}
{use class="frontend\design\Block"}
{\frontend\design\Info::addBoxToCss('form')}

<div class="page-confirmation">
{if isset($skipCsrf) && $skipCsrf}
  {$csrf=false}
{else}
  {$csrf=true}
{/if}

    {Html::beginForm($form_action_url, 'post', ['id' => 'frmCheckoutConfirm', 'name' => 'checkout_confirmation', 'csrf' => $csrf], false)}

{if $widgets}

    {if $noShipping}
        {Block::widget(['name' => 'confirmation_no_shipping', 'params' => ['type' => 'confirmation', 'params' => $params]])}
    {else}
        {Block::widget(['name' => 'confirmation', 'params' => ['type' => 'confirmation', 'params' => $params]])}
    {/if}

{else}

  <h1>{$smarty.const.ORDER_CONFIRMATION}</h1>
    {if $is_shipable_order}
  <div class="col-left">
    <div class="heading-4">{$smarty.const.SHIPPING_ADDRESS}<a href="{$shipping_address_link}" class="edit">{$smarty.const.EDIT}</a></div>
    <div class="confirm-info">
      {$address_label_delivery}
    </div>
  </div>
    {/if}
  <div class="{if $is_shipable_order}col-right{else}col-left{/if}">
    <div class="heading-4">{$smarty.const.TEXT_BILLING_ADDRESS}<a href="{$billing_address_link}" class="edit">{$smarty.const.EDIT}</a></div>
    <div class="confirm-info">
      {$address_label_billing}
    </div>
  </div>
    {if $is_shipable_order}
  <div class="col-left">
    <div class="heading-4">{$smarty.const.SHIPPING_METHOD}<a href="{$shipping_method_link}" class="edit">{$smarty.const.EDIT}</a></div>
    <div class="confirm-info">
      {$order->info['shipping_method']}
    </div>
    {if $shipping_additional_info_block}
      <div>
        {$shipping_additional_info_block}
      </div>
    {/if}
  </div>
    {/if}
  <div class="col-right">
    <div class="heading-4">{$smarty.const.PAYMENT_METHOD}<a href="{$payment_method_link}" class="edit">{$smarty.const.EDIT}</a></div>
    <div class="confirm-info">
      <strong>{$order->info['payment_method']}</strong>
      {if $payment_confirmation}
        <br>
        {if $payment_confirmation.title}
          {$payment_confirmation.title}<br>
        {/if}
        {if isset($payment_confirmation.fields) && is_array($payment_confirmation.fields)}
          <table>
          {foreach $payment_confirmation.fields as $payment_confirmation_field}
            <tr>
            <td>{$payment_confirmation_field.title}</td><td>{$payment_confirmation_field.field}</td>
            </tr>
          {/foreach}
          </table>
        {/if}
      {/if}
      {*Credit card<br>
      <strong>Credit Card:</strong> Visa Electron<br>
      <strong>Owner:</strong>	     Vladislav Malyshev<br>
      <strong>Number:</strong>        1111XXXXXXXXXX4444<br>
      <strong>Expiry Date:</strong>  January, 2023*}
    </div>
  </div>


  <div class="buttons">
    <div class="right-buttons">
      <button type="submit" class="btn-2">{$smarty.const.CONFIRM_ORDER}</button>
    </div>
  </div>

  <div class="heading-4">{$smarty.const.PRODUCT_S}<a href="{$cart_link}" class="edit">{$smarty.const.EDIT}</a></div>

  {use class="frontend\design\boxes\cart\Products"}
  {Products::widget(['type'=> 3, 'params' => ['bonus_points' => null] ])}



    {frontend\design\boxes\checkout\Totals::widget(['params' => $params])}

  <div class="buttons">
    <div class="right-buttons">
      <button type="submit" class="btn-2">{$smarty.const.CONFIRM_ORDER}</button>
    </div>
  </div>

    {/if}

    {$payment_process_button_hidden}
    {Html::endForm()}
</div>
<script type="text/javascript">
    tl(function(){
        var count = 0;
        $('#frmCheckoutConfirm').on('submit', function(e){
            if (count > 0){
                e.preventDefault();
                return false
            }
            count++;
        })
    });

    tl(function(){
        var orderSummary = $('.order-summary2');
        var top;
        var left;
        var width;
        var height;
        var bottom;
        var windowWidth;
        var setSizes = function(){
            top = orderSummary.parent().offset().top;
            left = orderSummary.offset().left;
            width = orderSummary.width();
            height = orderSummary.height();
            var parentBox = orderSummary.parent().parent();
            bottom = parentBox.offset().top + parentBox.height();
            windowWidth = $(window).width()
        };

        setTimeout(setSizes, 100);

        $(window).on('resize', setSizes);
        $(window).on('scroll', function(e){
            var scroll = $(window).scrollTop();
            if (scroll >= top - 20 && windowWidth > 1255) {
                orderSummary.css({
                    'position': 'fixed',
                    'width': width,
                    'left': left,
                    'top': 20,
                    'margin-left': 0,
                    'box-sizing': 'content-box'
                });
                if (scroll + height + 20 >= bottom) {
                    orderSummary.css({
                        'top': bottom - height - scroll
                    });
                }
            } else {
                orderSummary.css({
                    'position': '',
                    'width': '',
                    'left': '',
                    'top': '',
                    'margin-left': '',
                    'box-sizing': ''
                })
            }
        })
    })
</script>