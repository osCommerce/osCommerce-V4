{use class="Yii"}
{if !isset($product.price_from)}
    {if !isset($product.price_special)}{$product.price_special = false}{/if}
    {if Yii::$app->user->isGuest && \common\helpers\PlatformConfig::getFieldValue('platform_please_login')}
        <span class="current">{sprintf($smarty.const.TEXT_PLEASE_LOGIN, tep_href_link(FILENAME_LOGIN,'','SSL'))}</span>
    {else}
        {if isset($product.price_old)}<span class="old" {if !$product.price_special} style="display:none;"{/if}>{$product.price_old}</span>{/if}
        {if isset($product.price_special)}<span class="specials" {if !$product.price_special} style="display:none;"{/if}>{$product.price_special}</span>{/if}
        {if isset($product.price)}<span class="current" {if $product.price_special} style="display:none;"{/if}>{$product.price}</span>{/if}
        {if $product.price_special}
          {if $product.special_total_qty>0 && !$product.special_max_per_order }
            <span class="special-max">
              <span class="limited-mark-max">{$smarty.const.TEXT_LIMITED_MARK}
                <span class="limited-text">{sprintf($smarty.const.TEXT_LIMITED_SALE, $product.special_total_qty)}</span>
              </span>
            </span>
          {/if}
          {if $product.special_max_per_order>0 }
            <span class="special-max-per-order">
              <span class="limited-mark">{$smarty.const.TEXT_LIMITED_MARK}
                <span class="limited-text">{sprintf($smarty.const.TEXT_LIMITED_SALE_ORDER, $product.special_max_per_order)}</span>
              </span>
            </span>
          {/if}
        {/if}
      {if $product.special_promote_type>0 && $product.special_promo_str != ''}
        <div class="save-price-box"><div><span class="save-title">{$smarty.const.SALE_TEXT_SAVE}</span><span class="save-price">{$product.special_promo_str}</span></div></div>
      {/if}
{if $ext = \common\helpers\Acl::checkExtensionAllowed('QuickOrder', 'allowed')}
    {foreach $ext::getPricesForGroups(null, $product.products_id, $product.products_tax_class_id) as $gr => $pr}
        {if strlen($gr) > 1 && strlen($pr) > 1}
            <br><span class="price-hierarchy"><small>{$gr}: {$pr}</small></span>
        {/if}
    {/foreach}
{/if}
    {/if}
{/if}