{use class="frontend\design\Info"}
{use class="common\helpers\Html"}
{use class="Yii"}
{\frontend\design\Info::addBoxToCss('quantity')}
{\frontend\design\Info::addBlockToWidgetsList('cart-listing')}
{if count($products)==0}
    {$empty = true}
{else}
    <div class="cart-listing w-cart-listing">
        <div class="headings">
            <div class="head remove">{$smarty.const.TEXT_REMOVE_CART}</div>
            <div class="head image">{$smarty.const.PRODUCTS}</div>
            <div class="head name"></div>
            <div class="head qty">{$smarty.const.PRICE}</div>
            <div class="head price"></div>
        </div>
        {foreach $products as $product}
            <div class="item">
                <div class="remove">{if $product.remove_link}<a href="{$product.remove_link}" class="remove-btn"><span style="display: none">{$smarty.const.REMOVE}</span></a>{/if}</div>
                <div class="image">
                    {if $product.status}
                        <a href="{$product.link}"><img src="{$product.image}" alt="{$product.name|escape:'html'}" title="{$product.name|escape:'html'}"></a>
                    {else}
                        <img src="{$product.image}" alt="{$product.name|escape:'html'}" title="{$product.name|escape:'html'}">
                    {/if}
                </div>
                <div class="name">
                    {if $product.status}
                        <a href="{$product.link}">{$product.name}</a>
                    {else}
                        {$product.name}
                    {/if}
                    <div class="attributes">
                        {foreach $product.attr as $attr}
                            <div class="">
                                <strong>{$attr.products_options_name}:</strong>
                                <span>{$attr.products_options_values_name}</span>
                            </div>
                        {/foreach}
                    </div>
                    {if $product.is_bundle}
                        {foreach $product.bundles_info as $bundle_product }
                            <div class="bundle_product">
                                {$bundle_product.x_name}
                                {if $bundle_product.with_attr}
                                    <div class="attributes">
                                        {foreach $bundle_product.attr as $attr}
                                            <div class="">
                                                <strong>{$attr.products_options_name}:</strong>
                                                <span>{$attr.products_options_values_name}</span>
                                            </div>
                                        {/foreach}
                                    </div>
                                {/if}
                            </div>
                        {/foreach}
                    {/if}
                    {if $product.stock_info}
                        <div class="{$product.stock_info.text_stock_code}"><span class="{$product.stock_info.stock_code}-icon">&nbsp;</span>{$product.stock_info.stock_indicator_text}</div>
                    {/if}
                </div>
                    <div class="qty">
                        {$product.final_price_formatted}
                    </div>
                    <div class="price">
                        {if ($product.is_virtual || $product.stock_indicator.flags.can_add_to_cart || $settings[0].list_demo) && !GROUPS_DISABLE_CART}

                        {/if}
                        {if $product.status}
                            {if $product.stock_info}
                                {if $product.stock_info.flags.add_to_cart}
                                  {Html::beginForm("{$product.move_in_cart}", 'post', ['class' => 'form-whishlist item-form'])}
    <div class="qty-input"{if $product.product_in_cart} style="display: none"{/if}>
        {*<label>{output_label const="QTY"}</label>*}
        <input type="hidden" name="popup" value="1"/>
        <input
                type="text"
                name="qty"
                value="1"
                class="qty-inp"
                {if $product.stock_info.max_qty > 0 }
                    data-max="{$product.stock_info.max_qty}"
                {/if}
                {if $moq = \common\helpers\Extensions::isAllowed('MinimumOrderQty')}{$moq::setLimit($product.order_quantity_data)}{/if}
                {if $oqs = \common\helpers\Extensions::isAllowed('OrderQuantityStep')}{$oqs::setLimit($product.order_quantity_data)}{/if}
        />
    </div>
    <button type="submit" class="btn-1 btn-buy add-to-cart" title="{$smarty.const.ADD_TO_CART}"{if $product.product_in_cart} style="display: none"{/if}>{$smarty.const.ADD_TO_CART}</button>

                                    <a href="{tep_href_link(FILENAME_SHOPPING_CART)}" class=" btn-1 btn-cart in-cart" rel="nofollow" title="{$smarty.const.TEXT_IN_YOUR_CART}"{if !$product.product_in_cart} style="display: none"{/if}>{*$smarty.const.TEXT_IN_YOUR_CART*}</a>
                                    {Html::endForm()}
                                    <a href="{$product.move_in_cart}" class="view_link move-to-cart"{if $product.product_in_cart} style="display: none"{/if}>{$smarty.const.TEXT_MOVE_TO_CART_WISHLIST}</a>
                                {/if}
                          {else}
                            <a href="{$product.move_in_cart}" class="view_link"{if $product.product_in_cart} style="display: none"{/if}>{$smarty.const.TEXT_MOVE_TO_CART_WISHLIST}</a>
                          {/if}
                        {else}
                            {$smarty.const.TEXT_PRODUCT_DISABLED}
                        {/if}
                    </div>
            </div>
        {/foreach}
    </div>
{/if}

{if $empty && !\frontend\design\Info::isAdmin()}
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


<script type="text/javascript">
  tl(['{Info::themeFile('/js/main.js')}'] , function(){
    $('.form-whishlist').popUp({
      box_class: 'cart-popup',
      opened: function(obj){
          obj.closest('.item').find('.add-to-cart, .move-to-cart, .qty-input').hide();
          obj.closest('.item').find('.in-cart').show();
        }
    });
    
    
    $('input.qty-inp').quantity();

})

</script>
