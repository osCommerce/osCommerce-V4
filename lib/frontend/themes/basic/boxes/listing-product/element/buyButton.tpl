{use class="Yii"}
{use class="frontend\design\Info"}
{if $product.cart_button == 0 || (Yii::$app->user->isGuest && \common\helpers\PlatformConfig::getFieldValue('platform_please_login'))}

{else}
    {$product['buttonArray'][$product.products_id]['quantity']='$(this).closest(".item").find(".qty-inp").val()'}
    <a href="{$product.link_buy}"
        class="btn-1 btn-buy {$product['buttonArray'][$product.products_id]['buttonId']}"
        rel="nofollow"
        {\common\components\google\widgets\GoogleTagmanger::onclickAddToCart($product.products_id, '$(this).closest(".item").find(".qty-inp").val()')}
        title="{if !empty($product.stock_indicator.preorder_only) }{$smarty.const.BUTTON_TEXT_PREORDER|escape:'html'}{else}{$smarty.const.ADD_TO_CART|escape:'html'}{/if}"
        {if $product.product_in_cart && Info::themeSetting('show_in_cart_button') != 'no' || (isset($product.stock_indicator.flags.notify_instock) && $product.stock_indicator.flags.notify_instock)}style="display: none"{/if}>{if !empty($product.stock_indicator.preorder_only) }{$smarty.const.BUTTON_TEXT_PREORDER}{else}{$smarty.const.ADD_TO_CART}{/if}</a>

    {if isset($product.stock_indicator.flags.out_stock_action) && $product.stock_indicator.flags.out_stock_action == 2}
        {$contactName = Info::themeSetting('contact_name')}
        {if !$contactName}{$contactName = 'contact'}{/if}
        <a class="btn btn-notify" style="display:none;" href="{Yii::$app->urlManager->createUrl([$contactName, 'products_id' => $product.products_id])}">{$smarty.const.CONTACT_ABOUT_PRODUCT}</a>
    {else}
        <span class="btn btn-notify btn-notify-form" style="display:none;">{$smarty.const.NOTIFY_WHEN_STOCK}</span>
    {/if}

    <a href="{tep_href_link(FILENAME_SHOPPING_CART)}"
       class=" btn btn-in-cart"
       rel="nofollow"
       title="{$smarty.const.TEXT_IN_YOUR_CART|escape:'html'}"
       {if !$product.product_in_cart || Info::themeSetting('show_in_cart_button') == 'no'} style="display: none"{/if}>{$smarty.const.TEXT_IN_YOUR_CART}</a>

    <a href="{$product.link}"
       class=" btn btn-choose-options"
       title="{$smarty.const.TEXT_CHOOSE_OPTIONS|escape:'html'}"
       style="display: none">{$smarty.const.TEXT_CHOOSE_OPTIONS}</a>

    <span class="btn-1 btn-preloader" style="display: none"></span>

    {if isset($element.settings[0].show_added) && $element.settings[0].show_added}
    <div class="loaded-qty"{if !$product.product_in_cart} style="display: none"{/if}>(<span>{$product.product_in_cart}</span> {$smarty.const.TEXT_LISTING_ADDED})</div>
    {/if}

    {foreach \common\helpers\Hooks::getList('box/product-listing', 'button-buy-attribute') as $filename}
        {include file=$filename}
    {/foreach}
{/if}
