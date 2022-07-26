{if $productsPersonalCatalog }
{use class = "yii\helpers\Html"}
    <h4 class="order-table-title order_wishlist">{$smarty.const.TEXT_PERSONAL_CATALOG}</h4>
    <div class="w-cart-listing{\frontend\design\Info::addBlockToWidgetsList('cart-listing')}">

        <div class="headings">
            <div class="head remove">{$smarty.const.TEXT_REMOVE_CART}</div>
            <div class="head image">{$smarty.const.PRODUCTS}</div>
            <div class="head name"></div>
            <div class="head price">{$smarty.const.PRICE}</div>
            <div class="head qty"></div>
        </div>

            {Html::beginForm(['catalog/personal-catalog-action'], 'post', ['id' => 'product-form'])}
            {foreach $productsPersonalCatalog as $productPC}
                <div class="item">
                    <div class="remove"><a class="remove-btn pc-delete-item" data-id="{$productPC.id}" href="javascript:void(0)"></a></div>
                    <div class="image">
                        {if $productPC.status}
                            <a href="{$productPC.link}"><img src="{$productPC.image}" alt="{$productPC.name|escape:'html'}" title="{$productPC.name|escape:'html'}"></a>
                        {else}
                            <img src="{$productPC.image}" alt="{$productPC.name|escape:'html'}" title="{$productPC.name|escape:'html'}">
                        {/if}
                    </div>
                    <div class="name">
                        {if $productPC.status}
                            <a href="{$productPC.link}">{$productPC.name}</a>
                        {else}
                            {$productPC.name}
                        {/if}
                        <div class="attributes">
                            {foreach $productPC.attr as $attr}
                                <div class="">
                                    <strong>{$attr.products_options_name}:</strong>
                                    <span>{$attr.products_options_values_name}</span>
                                </div>
                            {/foreach}
                        </div>
                    </div>
                    <div class="price">
                      {if $productPC.stock_info.flags.request_for_quote}
                        {$smarty.const.HEADING_REQUEST_FOR_QUOTE}
                      {else}
                        {$productPC.final_price_formatted}
                      {/if}
                    </div>
                    <div class="qty">
                      {if $productPC.status}
                        {if $productPC.oos}
                          {$smarty.const.TEXT_PRODUCT_OUT_STOCK}
                        {else}
                            <a class="view_link pc-send-shop" data-qty="{$productPC.add_qty}" data-id="{$productPC.id}" data-cart="{if $productPC.stock_info.flags.request_for_quote}quote{else}cart{/if}" href="javascript:void(0)">{$smarty.const.BOX_WISHLIST_MOVE_TO_CART}</a>
                        {/if}
                      {else}
                        {$smarty.const.TEXT_PRODUCT_DISABLED}
                      {/if}
                    </div>
                </div>
            {/foreach}
            {Html::endForm()}
    </div>
    <div class="address_book_center bottom_adc view-all"><a class="btn" href="{tep_href_link($smarty.const.FILENAME_PERSONAL_CATALOG, '', 'SSL')}">{$smarty.const.BOX_INFORMATION_ALLPRODS}</a></div>
{/if}
<script type="text/javascript">
    tl([] , function(){

        var product_form = $('#product-form');

        $('body').on('click','.pc-delete-item', function() {
            actionPersonalCatalog('del_to_personal_catalog',$(this).attr('data-id'),'');
        });
        $('body').on('click','.pc-send-shop', function() {
            actionPersonalCatalog('move_to_shopping_cart',$(this).attr('data-id'),$(this).attr('data-cart'),$(this).attr('data-qty'));
        });

        function actionPersonalCatalog(action,id,in_cart)
        {
            $.post('{Yii::$app->urlManager->createUrl('catalog/personal-catalog-action')}',
                {
                    _csrf: $('meta[name="csrf-token"]').attr('content'),
                    personal_catalog:action,
                    products_id:id,
                    check_attribute:0,
                    pc_in_cart:in_cart,
                }
                , function(d){
                    alertMessage(d.message);
                    setTimeout(window.location.reload(), 3000);
                });
        }
    });
</script>
