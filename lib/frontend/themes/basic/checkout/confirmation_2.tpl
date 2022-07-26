{use class = "yii\helpers\Html"}
{use class="frontend\design\Info"}
{use class="frontend\design\Block"}
{\frontend\design\Info::addBoxToCss('price-box')}
{\frontend\design\Info::addBoxToCss('multi-page-checkout')}

{if $noShipping}{$noShipping = '_no_shipping'}{/if}


{if Info::isAdmin()}
<div class="multi-page-checkout">
  <div class="checkout-step" id="shipping-step">
    <div class="checkout-heading">
      <span class="edit">{$smarty.const.EDIT}</span>
      <span class="count">1</span>
        {Block::widget(['name' => 'checkout_delivery_title'|cat:$noShipping, 'params' => ['type' => 'confirmation', 'params' => $params]])}
    </div>
    <div class="checkout-content" style="display: none"></div>
  </div>
  <div class="checkout-step" id="payment-step">
    <div class="checkout-heading">
      <span class="edit">{$smarty.const.EDIT}</span>
      <span class="count">2</span>
        {Block::widget(['name' => 'checkout_payment_title'|cat:$noShipping, 'params' => ['type' => 'confirmation', 'params' => $params]])}
    </div>
    <div class="checkout-content" style="display: none"></div>
  </div>
  <div class="checkout-step active" id="confirmation-step">
    <div class="checkout-heading"><span class="count">3</span>
        {Block::widget(['name' => 'checkout_confirmation_title'|cat:$noShipping, 'params' => ['type' => 'confirmation', 'params' => $params]])}
    </div>
    <div class="checkout-content">
{/if}



<div class="page-confirmation">
  {Html::beginForm($form_action_url, 'post', ['id' => 'frmCheckoutConfirm', 'name' => 'checkout_confirmation'], false)}


    {if $widgets}

        {Block::widget(['name' => 'checkout_confirmation'|cat:$noShipping, 'params' => ['type' => 'confirmation', 'params' => $params]])}

    {else}

        {if $is_shipable_order}
            <div class="col-left">
                <div class="heading-4">{$smarty.const.SHIPPING_ADDRESS}{*<a href="{$shipping_address_link}" class="edit">{$smarty.const.EDIT}</a>*}</div>
                <div class="confirm-info">
                    {$address_label_delivery}
                </div>
            </div>
        {/if}
        <div class="{if $is_shipable_order}col-right{else}col-left{/if}">
            <div class="heading-4">{$smarty.const.TEXT_BILLING_ADDRESS}{*<a href="{$billing_address_link}" class="edit">{$smarty.const.EDIT}</a>*}</div>
            <div class="confirm-info">
                {$address_label_billing}
            </div>
        </div>
        {if $is_shipable_order}
            <div class="col-left">
                <div class="heading-4">{$smarty.const.SHIPPING_METHOD}{*<a href="{$shipping_method_link}" class="edit">{$smarty.const.EDIT}</a>*}</div>
                <div class="confirm-info">
                    {$order->info['shipping_method']}
                </div>
            </div>
        {/if}
        <div class="col-right">
            <div class="heading-4">{$smarty.const.PAYMENT_METHOD}{*<a href="{$payment_method_link}" class="edit">{$smarty.const.EDIT}</a>*}</div>
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
            </div>
        </div>


        <div class="buttons">
            <div class="right-buttons">
                <button type="submit" class="btn-2">{$smarty.const.CONFIRM_ORDER}</button>
            </div>
        </div>

        <div class="heading-4">{$smarty.const.PRODUCT_S}{*<a href="{$cart_link}" class="edit">{$smarty.const.EDIT}</a>*}</div>

        {use class="frontend\design\boxes\cart\Products"}
        {Products::widget(['type'=> 3])}


        <div class="price-box">
            {include file="./totals.tpl"}
        </div>

        <div class="buttons" style="overflow: hidden">
            <div class="right-buttons">
                <button type="submit" class="btn-2">{$smarty.const.CONFIRM_ORDER}</button>
            </div>
        </div>

    {/if}




  {$payment_process_button_hidden}
  {Html::endForm()}
</div>


        {if Info::isAdmin()}
    </div>
  </div>
</div>
{/if}