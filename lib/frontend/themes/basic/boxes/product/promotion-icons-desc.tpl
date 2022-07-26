{if is_array($product.promo_details) && count($product.promo_details) > 0 || $product.price_special}
<div class="promo_info_icon">{$smarty.const.TEXT_PROMO_OFFER}</div>
<div class="promos-info">
    {foreach $product.promo_details as $key => $info}
        {if $info.promo_description && (!$info.slave || !$info.hide_on_slave)}
        <div class="promo-item{if $info.slave} depended{/if} {$info.class}{if is_array($info.restrict_class)} {implode(' ', $info.restrict_class)}{/if} {$info.css_class} promo-{$key}">
            {if isset($info.promo_icon)}
                <div class="promo-icon"><img src="{$info.promo_icon}" alt="{$info.promo_name}"></div>
            {/if}
            <div class="promo-name"><span class='promo-details'>{$info.promo_description}</span></div>
        </div>
        {/if}
    {/foreach}
</div>
{/if}