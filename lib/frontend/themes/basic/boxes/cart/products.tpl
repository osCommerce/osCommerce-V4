{use class="frontend\design\Info"}
{use class = "Yii"}
{use class = "yii\helpers\Html"}
{use class="\frontend\design\boxes\product\Packs"}
{\frontend\design\Info::addBlockToWidgetsList('cart-listing')}

<div class="cart-listing w-cart-listing">
    <div class="{if !$popupMode && $multiCart['enabled']}multi-cart{/if}{if $smarty.const.GROUPS_IS_SHOW_PRICE === false} no-price{/if}">
        {if $promoMessage}{$promoMessage}{/if}
        <div class="headings">
            {if !$multiCart['enabled'] || $popupMode}
                <div class="head remove">{$smarty.const.REMOVE}</div>
            {/if}
            <div class="head image">{$smarty.const.PRODUCTS}</div>
            <div class="head name"></div>
            <div class="head qty">{$smarty.const.QTY}</div>
            <div class="head price">{if $smarty.const.GROUPS_IS_SHOW_PRICE !== false}{$smarty.const.PRICE}{/if}</div>

        </div>

        {foreach $products as $product}
            <div class="item{if strlen($product.parent) > 0} subitem{/if}" data-id="{$product.id}">

                {if $popupMode || !$multiCart['enabled']}
                    <div class="remove">
                        {if isset($product.remove_link) && $product.remove_link}
                            <a href="{$product.remove_link}" class="remove-btn">
                                <span style="display: none">{$smarty.const.REMOVE}</span>
                            </a>
                        {/if}
                    </div>
                {/if}

                {if \common\helpers\Acl::checkExtensionAllowed('Promotions')}
                {\common\extensions\Promotions\widgets\PromotionIcons\PromotionIcons::widget(['params' => ['product' => $product]])}
                {/if}
                <div class="image">
                    {if isset($product._status) && $product._status}
                        <a href="{$product.link}"><img src="{$product.image}" alt="{$product.name}"></a>
                    {else}
                        <span><img src="{$product.image}" alt="{$product.name}"></span>
                    {/if}
                </div>


                <div class="name">
                    <table class="wrapper"><tr><td>
                        {if isset($product._status) && $product._status}
                            <a href="{$product.link}">{$product.name}</a>
                        {else}
                            <span>{$product.name}</span>
                        {/if}
                    </td></tr></table>

                    {if $product.stock_info.order_instock_bound && $smarty.const.TEXT_INSTOCK_BOUND_MARKER}
                        <span class="attention_mark">{$smarty.const.TEXT_INSTOCK_BOUND_MARKER}</span>
                    {/if}

                    {if $product.stock_info}
                        <div class="{$product.stock_info.text_stock_code}"><span class="{$product.stock_info.stock_code}-icon">&nbsp;</span>{$product.stock_info.stock_indicator_text}</div>
                    {/if}

                    {Packs::widget(['product' => $product])}
                    <div class="attributes">
                        {if isset($product.attr)}
                        {foreach $product.attr as $attr}
                            <div class="">
                                <strong>{$attr.products_options_name}:</strong>
                                {if $attr.products_options_values_text}
                                    <span>{$attr.products_options_values_text}</span>
                                {else}
                                    <span>{$attr.products_options_values_name}</span>
                                {/if}
                            </div>
                        {/foreach}
                        {/if}
                    </div>

                    {if isset($product.is_bundle) && $product.is_bundle}
                        {foreach $product.bundles_info as $bundle_product }
                            <div class="bundle_product">
                                <table class="wrapper"><tr><td>{$bundle_product.x_name}</td></tr></table>
                                {if $bundle_product.with_attr}
                                    <div class="attributes">
                                        {foreach $bundle_product.attr as $attr}
                                            <div class="attributes-item">
                                                <strong>{$attr.products_options_name}:</strong>
                                                <span>{$attr.products_options_values_name}</span>
                                            </div>
                                        {/foreach}
                                    </div>
                                {/if}
                            </div>
                        {/foreach}
                    {/if}
                </div>


                <div class="qty">
                    {if isset($product.parent) && $product.parent == ''}
                        {if $product.ga}
                            <input type="hidden" name="cart_quantity[]" value="{$product.quantity}"/>
                            <span class="qty-readonly">{$product.quantity}</span>
                        {else}
                            {if isset($product.is_pack) && $product.is_pack > 0 }
                                <input type="hidden" name="cart_quantity[]" value="{$product.quantity}"/>
                                <div class="qty_cart_colors">
                                    <span class="qc_title">{$smarty.const.UNIT_QTY}: </span>
                                    <input type="text" name="cart_quantity_[{$product.id}][0]" value="{$product.units}" class="qty-inp-s" data-min="0"{if $product.stock_info.quantity_max != false} data-max="{$product.stock_info.quantity_max}"{/if}{if $oqs = \common\helpers\Extensions::isAllowed('OrderQuantityStep')}{$oqs::setLimit($product.order_quantity_data)}{/if}/>
                                </div>
                                {if $product.order_quantity_data.pack_unit > 0}
                                <div class="qty_cart_colors">
                                    <span class="qc_title">{$smarty.const.PACK_QTY}: <small>({$product.order_quantity_data.pack_unit} items)</small></span>
                                    <input type="text" name="cart_quantity_[{$product.id}][1]" value="{$product.packs}" class="qty-inp-s" data-min="0"{if $product.stock_info.quantity_max != false} data-max="{floor($product.stock_info.quantity_max/$product.order_quantity_data.pack_unit)}"{/if}/>
                                </div>
                                {/if}
                                {if $product.order_quantity_data.packaging > 0}
                                <div class="qty_cart_colors">
                                    <span class="qc_title">{$smarty.const.CARTON_QTY}: <small>({$product.order_quantity_data.pack_unit*$product.order_quantity_data.packaging} items)</small></span>
                                    <input type="text" name="cart_quantity_[{$product.id}][2]" value="{$product.packagings}" class="qty-inp-s" data-min="0"{if $product.stock_info.quantity_max != false} data-max="{floor($product.stock_info.quantity_max/($product.order_quantity_data.pack_unit*$product.order_quantity_data.packaging))}"{/if}/>
                                </div>
                                {/if}
                            {else}
                                <input type="text" name="cart_quantity[]" value="{$product.quantity_virtual}" class="qty-inp-s"{if $product.stock_info.quantity_max != false} data-max="{$product.stock_info.quantity_max}"{/if}
                                    data-value-real="{$product.quantity}"
                                    {if $moq = \common\helpers\Extensions::isAllowed('MinimumOrderQty')}{$moq::setLimit($product.order_quantity_data)}{/if}
                                    {if $oqs = \common\helpers\Extensions::isAllowed('OrderQuantityStep')}{$oqs::setLimit($product.order_quantity_data)}{/if}
                                />
                            {/if}
                        {/if}
                        {$product.hidden_fields}
                    {else}
                        <span class="qty-readonly">{$product.quantity}</span>
                    {/if}
                </div>

                <div class="price">
                    {if $smarty.const.GROUPS_IS_SHOW_PRICE !== false}
                    {$product.final_price}{if $product.standard_price !== false}<br/><small><i>(<strike>{$product.standard_price}</strike>)</i></small>{/if}
                    {if isset($product.promo_message) && !empty($product.promo_message)}
                    <br><small class="promo-message">{$product.promo_message}</small>
                    {/if}
                    {/if}
                </div>

                {$BonusActions=\common\helpers\Extensions::isAllowedAnd('BonusActions', 'isProductPointsEnabled')}
                {if $BonusActions && $product.bonus_points_cost}
                <div class="points">
                {if false}
                    <div class="points-redeem">
                        <b>{number_format($product.bonus_points_price * $product.quantity, 0)}</b>
                        {$smarty.const.TEXT_POINTS_REDEEM_NOT_USED}
                    </div>
                {/if}
                    <div class="points-earn">
                        {if $PremiumAccountClass = \common\helpers\Acl::checkExtensionAllowed('PremiumAccount', 'allowed')}
                            {$PremiumAccountClass::showRewardPointsCost($product.bonus_points_cost * $product.quantity)}
                        {/if}
                        {*$BonusActions::formatPointAndCurrency($product.bonus_points_cost*$product.quantity, $product.bonus_points_cost_currency*$product.quantity)*}
                        {$product.bonus_points_cost_formatted} {$smarty.const.EXT_BONUS_ACTIONS_TEXT_PRODUCT_REWARD_POINTS}
                    </div>
                </div>
                {/if}

                {if $product.parent == '' && $popupMode == false}
                    {if $product.gift_wrap_allowed}
                        <div class="gift-wrap">
                            <label>
                                <span class="title">{$smarty.const.BUYING_GIFT}</span>
                                <span class="value">{$product.gift_wrap_price_formated}</span>
                                <input type="checkbox" name="gift_wrap[{$product.id}]" class="check-on-off" {if $product.gift_wrapped} checked="checked"{/if}/>
                            </label>
                        </div>
                    {/if}
                {/if}

                {if !$popupMode && $multiCart['enabled']}
                    {$product['multicart-actions']}
                {/if}


            </div>
        {/foreach}
    </div>
    {if $bound_quantity_ordered}
        <div class="checkout-attention-message">{sprintf($boundMessage, '<span class="attention_mark">'|cat:$smarty.const.TEXT_INSTOCK_BOUND_MARKER|cat:'</span>', '<span class="attention_mark">'|cat:$smarty.const.TEXT_INSTOCK_BOUND_MARKER|cat:'</span>')}</div>
    {/if}
    {if $oos_product_incart}
        <div class="checkout-attention-message">{$smarty.const.TEXT_INFO_OUT_OF_STOCK_IN_CART}</div>
    {/if}
    {if !$popupMode && $multiCart['enabled']}
        {$multiCart['script']}
    {/if}

    <script type="text/javascript">
        tl(function(){
            {if $allow_checkout == false}
                $('.cart-express-buttons').hide();
            {else}
                $('.cart-express-buttons').show();
            {/if}
            $('.btn-to-checkout, .cart-checkout-buttons').each(function(){
                {if $allow_checkout == false}
                $(this).css({
                    'opacity': '0.5',
                    'cursor': 'default'
                });
                if ($(this).attr('href')) {
                    $(this).attr('data-href', $(this).attr('href')).removeAttr('href')
                }
                {else}
                $(this).css({
                    'opacity': '',
                    'cursor': ''
                });
                if ($(this).attr('data-href')){
                    $(this).attr('href', $(this).attr('data-href'))
                }
                {/if}
            })


            $('body').on('click', '.js-move-item', function(e){
                e.preventDefault();
                $.post($(this).attr('href'),
                    {
                        _csrf : $('input[name=_csrf]').val(),
                        qty   : $(this).closest('.item').find('.qty-inp-s').val()
                    },
                    function(data){
                        if(data.success){
                            alertMessage(data.dialog);
                        }

                    }, 'json');
                return false;
            });


        })




    </script>
    {if isset($settings[0].editable_products) && $settings[0].editable_products}
        <script type="text/javascript">
            tl([
                '{Info::themeFile('/js/main.js')}',
                '{Info::themeFile('/js/bootstrap-switch.js')}'
            ], function(){

                $('.multi-product-copy').hide();
                $('.multi-product-move').hide();

                var form = $('.cart-listing');

                {\frontend\design\Info::addBoxToCss('quantity')}
                $('input.qty-inp-s').quantity({
                    event: function(){
                        form.trigger('cart-change');
                    }
                }).on('blur', function(){
                    form.trigger('cart-change');
                });

                {\frontend\design\Info::addBoxToCss('switch')}
                $(".check-on-off").bootstrapSwitch({
                    offText: '{$smarty.const.TEXT_NO}',
                    onText: '{$smarty.const.TEXT_YES}',
                    onSwitchChange: function () {
                        form.trigger('cart-change');
                    }
                });

                {\frontend\design\Info::addBoxToCss('preloader')}
                var send = 0;
                form.off('cart-change').on('cart-change', function(){
                    addPreloader()
                    var data = $('input', form).serializeArray();
                    data.push({ name:'_csrf', value:$('input[name="_csrf"]').val()})

                    send++;
                    $.post('{Yii::$app->urlManager->createUrl(['shopping-cart', 'action' => 'update_product'])}', data, function(d){
                        $.get('{Yii::$app->urlManager->createUrl(['checkout'])}', function(d){
                            send--;
                            if (send == 0) {
                                $('.main-content').html(d)
                            }
                        });
                        $(window).trigger('cart_change');
                    });
                });

                $('.multi-product-delete a').on('click', function(e){
                    addPreloader();
                    e.preventDefault();
                    $.get($(this).attr('href'), { no_redirect: true}, function(d){
                        $.get('{Yii::$app->urlManager->createUrl(['checkout'])}', function(d){
                            $('.main-content').html(d)
                        });
                        $(window).trigger('cart_change');
                    });
                });

                function addPreloader(){
                    $('.w-cart-products').css({
                        'position': 'relative'
                    }).prepend('<div class="preloader"></div>')
                }
            })
        </script>
    {/if}
</div>
{foreach \common\helpers\Hooks::getList('box/cart/products', 'bottom') as $filename}
    {include file=$filename}
{/foreach}
