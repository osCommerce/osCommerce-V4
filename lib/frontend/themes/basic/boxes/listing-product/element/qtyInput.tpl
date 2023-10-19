{use class="Yii"}
{$PackUnits = \common\helpers\Acl::checkExtensionAllowed('PackUnits', 'allowed')}
{if Yii::$app->user->isGuest && \common\helpers\PlatformConfig::getFieldValue('platform_please_login')}
    
{elseif $PackUnits && ($product.pack_unit > 0 || $product.packaging > 0)}
    {$quantity_max = $product.stock_indicator.quantity_max}
    {if $product.pack_unit > 0 || $product.packaging > 0}
        {$product.order_quantity_data.order_quantity_minimal = 0}
    {/if}
    {$pack_units_data = $PackUnits::quantityBoxFrontend([], ['products_id' => $product.products_id])}
    <div class="qty-input">
        <div class="qty_t">{$smarty.const.UNIT_QTY}:</div>
        <div class="input">
            <span class="price_1" id="product-price-current"><span class="priceIn">{$pack_units_data.single_price['unit']}</span></span>
            <input type="text" name="qty_p_[0]" value="0" class="qty-inp check-spec-max"  data-type="unit" {if $quantity_max>0} data-max="{$quantity_max}"{/if} data-min="{$product.order_quantity_data.order_quantity_minimal}"  {if $product.order_quantity_data.order_quantity_step>1} data-step="{$product.order_quantity_data.order_quantity_step}"{/if}>
        </div>
    </div>
    {if $product.pack_unit > 0}
    <div class="qty-input">
        <div class="qty_t">{$smarty.const.PACK_QTY}:<span>({$product.pack_unit} items)</span></div>
        <div class="input inps">
            <span class="price_1"><span class="priceIn">{$pack_units_data.single_price['pack']}</span></span>
            <input type="text" name="qty_p_[1]" value="0" class="qty-inp check-spec-max" data-type="pack_unit" data-min="0"  {if $quantity_max>0} data-max="{floor($quantity_max/$product.pack_unit)}"{/if} >
        </div>
    </div>
    {/if}
    {if $product.packaging > 0}
    <div class="qty-input">
        <div class="qty_t">{$smarty.const.CARTON_QTY}:<span>({$product.packaging * $product.pack_unit} items)</span></div>
        <div class="input inps">
            <span class="price_1"><span class="priceIn">{$pack_units_data.single_price['package']}</span></span>
            <input type="text" name="qty_p_[2]" value="0" class="qty-inp"  data-type="packaging" data-min="0" {if $quantity_max>0} data-max="{floor($quantity_max/($product.packaging*$product.pack_unit))}"{/if} >
        </div>
    </div>
    {/if}
{elseif isset($settings['b2b']) && $settings['b2b']}
    <input
            type="text"
            name="qty_p[]"
            value="{if isset($product.add_qty)}{if $product.stock_indicator.quantity_max < $product.add_qty}{$product.stock_indicator.quantity_max}{else}{$product.add_qty}{/if}{else}0{/if}"
            data-zero-init="1"
            class="qty-inp"
            {if $product.stock_indicator.quantity_max>0}
                data-max="{$product.stock_indicator.quantity_max}"
            {/if}
            {if $product.order_quantity_data && $product.order_quantity_data.order_quantity_minimal>0}
                data-min="{$product.order_quantity_data.order_quantity_minimal}"
            {else}
                data-min="0"
            {/if}
            {if $product.order_quantity_data && $product.order_quantity_data.order_quantity_step>1}
                data-step="{$product.order_quantity_data.order_quantity_step}"
            {/if}
    />
{else}
    <input
            type="text"
            name="qty_p"
            value="1"
            class="qty-inp"
            {if $product.stock_indicator.quantity_max > 0 }
                data-max="{$product.stock_indicator.quantity_max}"
            {/if}
            {if $moq = \common\helpers\Extensions::isAllowed('MinimumOrderQty')}{$moq::setLimit($product.order_quantity_data)}{/if}
            {if $oqs = \common\helpers\Extensions::isAllowed('OrderQuantityStep')}{$oqs::setLimit($product.order_quantity_data)}{/if}
    />
{/if}
