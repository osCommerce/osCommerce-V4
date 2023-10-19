{use class="frontend\design\Info"}
<div class="price" {*itemprop="offers" itemscope itemtype="http://schema.org/Offer"*}>
    {if Yii::$app->user->isGuest && \common\helpers\PlatformConfig::getFieldValue('platform_please_login')}
        <div class="pnp_value">{sprintf($smarty.const.TEXT_PLEASE_LOGIN, tep_href_link(FILENAME_LOGIN,'','SSL'))}</div>
    {else}

        {if $qty > 1}

            <span class="price-multiply">x</span>

            {if $special_one}<span id="product-price-special_one" class="special">{$special_one}</span>{/if}
            {if $special_ex_one}<span id="product-price-special-ex_one" class="special special-ex">{$special_ex_one}</span>{/if}
            {if $current_one}<span id="product-price-current_one" class="current">{$current_one}</span>{/if}
            {if $current_ex_one}<span id="product-price-current-ex_one" class="current current-ex">{$current_ex_one}</span>{/if}

            {if $special_promote_type>0 && $special_promo_one_str}
              <span class="special-promote">{$special_promo_one_str}</span>
            {/if}

            {if $special_one}
              {if $special_total_qty>0 && !$special_max_per_order }
                <span class="special-max">
                  <span class="limited-mark-max">{$smarty.const.TEXT_LIMITED_MARK}
                    <span class="limited-text">{sprintf($smarty.const.TEXT_LIMITED_SALE, $special_total_qty)}</span>
                  </span>
                </span>
              {/if}
              {if $special_max_per_order>0 }
                <span class="special-max-per-order">
                  <span class="limited-mark">{$smarty.const.TEXT_LIMITED_MARK}
                    <span class="limited-text">{sprintf($smarty.const.TEXT_LIMITED_SALE_ORDER, $special_max_per_order)}</span>
                  </span>
                </span>
              {/if}
            {/if}

            <span class="price-is">=</span>

            {if $special}<span id="product-price-special" class="special">{$special}</span>{/if}
            {if $special_ex}<span id="product-price-special-ex" class="special special-ex">{$special_ex}</span>{/if}
            {if $current}<span id="product-price-current" class="current">{$current}</span>{/if}
            {if $current_ex}<span id="product-price-current-ex" class="current current-ex">{$current_ex}</span>{/if}
            {if $special_promote_type>0 && $special_promo_str}
              <span class="special-promote">{$special_promo_str}</span>
            {/if}
            {if $special}
              {if $special_total_qty>0 && !$special_max_per_order }
                <span class="special-max">
                  <span class="limited-mark-max">{$smarty.const.TEXT_LIMITED_MARK}
                    <span class="limited-text">{sprintf($smarty.const.TEXT_LIMITED_SALE, $special_total_qty)}</span>
                  </span>
                </span>
              {/if}
              {if $special_max_per_order>0 }
                <span class="special-max-per-order">
                  <span class="limited-mark">{$smarty.const.TEXT_LIMITED_MARK}
                    <span class="limited-text">{sprintf($smarty.const.TEXT_LIMITED_SALE_ORDER, $special_max_per_order)}</span>
                  </span>
                </span>
              {/if}
            {/if}


        {else}
            {if $old != '' && ($tax_rate>0 || $smarty.const.DISPLAY_BOTH_PRICES !='True')}<span class="old"><span id="product-price-old">{$old}</span>{if $smarty.const.DISPLAY_BOTH_PRICES =='True'} <small class="inc-vat-title">{$smarty.const.TEXT_INC_VAT}</small>{/if}</span>{/if}
            {if $old_ex != ''}<span id="product-price-old-ex" class="old old-ex">{$old_ex} <small class="ex-vat-title">{$smarty.const.TEXT_EXC_VAT}</small></span>{/if}
            {if $special != '' && ($tax_rate>0 || $smarty.const.DISPLAY_BOTH_PRICES !='True')}<span  class="special"><span id="product-price-special">{$special}</span>{if $smarty.const.DISPLAY_BOTH_PRICES =='True'} <small class="inc-vat-title">{$smarty.const.TEXT_INC_VAT}</small>{/if}</span>{/if}
            {if $special_ex != ''}<span id="product-price-special-ex" class="special special-ex">{$special_ex} <small class="ex-vat-title">{$smarty.const.TEXT_EXC_VAT}</small></span>{/if}

            {if $current != '' && ($tax_rate>0 || $smarty.const.DISPLAY_BOTH_PRICES !='True')}<span id="product-price-current" class="current">{$current}{if $smarty.const.DISPLAY_BOTH_PRICES =='True'} <small class="inc-vat-title">{$smarty.const.TEXT_INC_VAT}</small>{/if}</span>{/if}
            {if $current_ex != ''}<span id="product-price-current-ex" class="current current-ex">{$current_ex} <small class="ex-vat-title">{$smarty.const.TEXT_EXC_VAT}</small></span>{/if}
            {if $special_promote_type>0 && $special_promo_str != ''}
              <span class="special-promote">{$special_promo_str}</span>
            {/if}
            {if $special}
              {if $special_total_qty>0 && !$special_max_per_order }
                <span class="special-max">
                  <span class="limited-mark-max">{$smarty.const.TEXT_LIMITED_MARK}
                    <span class="limited-text">{sprintf($smarty.const.TEXT_LIMITED_SALE, $special_total_qty)}</span>
                  </span>
                </span>
              {/if}
              {if $special_max_per_order>0 }
                <span class="special-max-per-order">
                  <span class="limited-mark">{$smarty.const.TEXT_LIMITED_MARK}
                    <span class="limited-text">{sprintf($smarty.const.TEXT_LIMITED_SALE_ORDER, $special_max_per_order)}</span>
                  </span>
                </span>
              {/if}
            {/if}
{if $ext = \common\helpers\Acl::checkExtensionAllowed('QuickOrder', 'allowed')}
    {foreach $ext::getPricesForGroups(null, $products_id, \common\helpers\Product::get_products_info($products_id, 'products_tax_class_id')) as $gr => $pr}
        {if strlen($gr) > 1 && strlen($pr) > 1}
            <br><span class="price-hierarchy"><small>{$gr}: {$pr}</small></span>
        {/if}
    {/foreach}
{/if}
        {/if}
    {/if}
</div>

{if isset($settings[0].change_price) && $settings[0].change_price && !Info::isAdmin()}
<script>
    tl(function(){
        $(window).one('changedQty', function (event, qty) {
            var boxId = '{$id}';
            $.get("{tep_href_link('get-widget/one')}", {
                id: boxId,
                action: 'main',
                products_id: '{$products_id}',
                qty: qty,
            }, function (d) {
                $('#box-' + boxId).html(d)
            })
        })
    })
</script>
{/if}