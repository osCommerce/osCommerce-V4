{\frontend\design\Info::addBlockToWidgetsList('cart-listing')}
<div class="cart-listing w-cart-listing">
    {if $tax_groups > 1}
        <div class="headings">
            <div class="head image">{$smarty.const.HEADING_PRODUCTS}</div>
            <div class="head name"></div>
            <div class="head qty">{$smarty.const.HEADING_TAX}</div>
            <div class="head price">{$smarty.const.HEADING_TOTAL}</div>
        </div>
    {else}
        <div class="headings">
            <div class="head image">{$smarty.const.HEADING_PRODUCTS}</div>
            <div class="head name"></div>
            <div class="head qty"></div>
            <div class="head price">{$smarty.const.HEADING_PRICE}</div>
        </div>
    {/if}
    {foreach $order_product as $order_product_array}
        <div class="item">
            <div class="image">{if $order_product_array.product_info_link}
                    <a href="{$order_product_array.product_info_link}" title="{$order_product_array.order_product_name|escape:'html'}"><img src="{$order_product_array.products_image}" alt="{$order_product_array.order_product_name|escape:'html'}"></a>
                {else}
                    <img src="{$order_product_array.products_image}" alt="{$order_product_array.order_product_name|escape:'html'}">
                {/if}
            </div>

            <div class="name">
                {$order_product_array.order_product_qty} x
                {if $order_product_array.product_info_link}
                    <a href="{$order_product_array.product_info_link}" title="{$order_product_array.order_product_name|escape:'html'}">{$order_product_array.order_product_name}</a>
                {else}
                    {$order_product_array.order_product_name}
                {/if}
                {foreach \common\helpers\Hooks::getList('account/order_history_info', 'order-product') as $filename}{*deprecated*}
                    {include file=$filename}
                {/foreach}
                {foreach \common\helpers\Hooks::getList('box/account/order-products', 'order-product') as $filename}
                    {include file=$filename}
                {/foreach}
                {if ($ext = \common\helpers\Acl::checkExtensionAllowed('ProductAssets', 'allowed'))}
                    <div class="orderProductAsset">{$ext::renderOrderProductAsset($order_product_array['orders_products_id'], true)}</div>
                {/if}
                {if count($order_product_array['attr_array'])>0}
                    <div class="history_attr">
                        {foreach $order_product_array['attr_array'] as $info_attr}
                            {if $info_attr.order_pr_option}
                                <div><strong>{$info_attr.order_pr_option}:</strong><span>{$info_attr.order_pr_value}</span></div>
                            {/if}
                        {/foreach}
                        {if $order_product_array.gift_card_pdf}
                            <div class=""><a href="{$order_product_array.gift_card_pdf}" target="_blank">{$smarty.const.TEXT_DOWNLOAD_GIFT_CARD_PDF}</a></div>
                        {/if}
                    </div>
                {/if}
                {if $order_info_status == 'Delivered'}
                    <div><a class="view_link popup" href="{tep_href_link('reviews/write', 'products_id='|cat:$order_product_array.id, 'SSL')}">{$smarty.const.IMAGE_BUTTON_WRITE_REVIEW}</a></div>
                {/if}
            </div>
                <div class="qty">
                    {if $tax_groups > 1}
                        {$order_product_array.order_products_tax}
                    {/if}
                    &nbsp;
                </div>
                <div class="price">{if $smarty.const.GROUPS_IS_SHOW_PRICE !== false}{$order_product_array.final_price}{/if}</div>
        </div>
    {/foreach}
</div>