{if is_array($product_promo_details) && count($product_promo_details) > 0 || $product.price_special}
<div class="promos-info">
    {*if $product.promo_class}
        <div class="{$product.promo_class}"></div>
    {/if*}
    {$salePromo=false} {* to avoid double icon*}
    {foreach $product_promo_details as $key => $info}
        {if (!$info.slave || !$info.hide_on_slave)}
        {if $info.working_promo}
          {if is_array($info.restrict_class) && in_array('sales', $info.restrict_class)}
          {$salePromo=true}
          {/if}
        <div class="promo-item{if $info.slave} depended{/if} {$info.class} {if is_array($info.restrict_class)}{implode(' ', $info.restrict_class)}{/if}{if $info.css_class} {$info.css_class}{/if}">
            {if isset($info.promo_icon)}
                <div class="promo-icon">
                  {if empty($info.promo_icon_extra)}
                    <img src="{$info.promo_icon}" alt="{$info.promo_name}">
                  {else}
                    <div class="promo-icon-bg" {if $info.promo_icon}style="background-image: url('{$info.promo_icon|escape}')"{/if}>
                      {$smarty.const.SALE_TEXT_SAVE} <span class='discount-amount'>{$info.promo_icon_extra}</span>
                    </div>
                  {/if}
                </div>
            {/if}
            <div class="promo-name"{if $info.font_color || $info.bg_color} style="{if $info.font_color}color: {$info.font_color};{/if}{if $info.bg_color}background-color: {$info.bg_color}{/if}"{/if}>{if $info.promo_name}<span class='promo-name-span'>{$info.promo_name} </span>{/if}<span style="display:none" class='promo-details'>{$info.promo_description}</span></div>
        </div>
        {/if}
        {/if}
    {/foreach}
    {* someone broke the functionality the promoclass is always set on listing :( && $product.promo_class*}
    {if ($product.price_special || $product.special_price) && $product.promo_show_sale_icon && !$salePromo}
        <div class="promo-item sale-flag">
            <div class="promo-name">{$smarty.const.SALE_TEXT}</div>
        </div>
    {/if}
</div>
{/if}