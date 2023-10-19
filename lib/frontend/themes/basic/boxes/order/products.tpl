<div class="cart-listing w-cart-listing{\frontend\design\Info::addBlockToWidgetsList('cart-listing')}">
    <div class="headings">
        <div class="head image">{$smarty.const.HEADING_PRODUCTS}</div>
        <div class="head name"></div>
        <div class="head qty"></div>
        <div class="head price">{$smarty.const.HEADING_PRICE}</div>
    </div>
    {foreach $order_product as $order_product_array}
        <div class="item{if $order_product_array.parent_product} sub-product{/if}">
            <div class="image">
                {if $order_product_array.product_info_link}
                    <a href="{$order_product_array.product_info_link}" target="_blank" title="{$order_product_array.order_product_name|escape:'html'}"><img src="{$order_product_array.products_image}" alt="{$order_product_array.order_product_name|escape:'html'}"></a>
                {else}
                    <img src="{$order_product_array.products_image}" alt="{$order_product_array.order_product_name|escape:'html'}">
                {/if}
            </div>
            <div class="name">
                {if $order_product_array.product_info_link}
                    <a href="{$order_product_array.product_info_link}" target="_blank" title="{$order_product_array.order_product_name|escape:'html'}">{$order_product_array.order_product_name}</a>
                {else}
                    {$order_product_array.order_product_name}
                {/if}

                {if count($order_product_array['attr_array'])>0}
                    <div class="history_attr">
                        {foreach $order_product_array['attr_array'] as $info_attr}
                            {if $info_attr.order_pr_option}
                                <div><strong>{$info_attr.order_pr_option}:</strong><span>{$info_attr.order_pr_value}</span></div>
                            {/if}
                        {/foreach}
                    </div>
                {/if}
            </div>

            <div class="qty">

                {$order_product_array.order_product_qty}

            </div>
            <div class="price">{$order_product_array.final_price}</div>

        </div>
    {/foreach}
</div>