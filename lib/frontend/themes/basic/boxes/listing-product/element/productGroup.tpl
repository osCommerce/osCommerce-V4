{if $product.product_group_params}

    {foreach $product.product_group_params.properties as $property}
        <div class="radioBox2 radioBox">
            <div class="radioBoxHead"><span class="title">{$property['properties_name']}:</span> {*<span class="value">{$property['values'][$property['current_value']]['text']}</span>*}</div>
            {foreach $property['values'] as $value}
                <label{if $value['color']} class="prColor"{/if}>
                    {*<input type="radio" name="prop_{$property['properties_id']}"{if $value['product']['selected']} checked{/if}>*}
                    <div class="pr-groups{if $value['product']['selected']} active{/if}">
                        <a href="{$value['product']['lazy']['link']}" class="js-list-prod" {if !($settings[0]['hide_out_of_stock_groups'] && !$value['product']['lazy']['stock_indicator']['flags']['add_to_cart'])}{*not available*}{/if} data-products-id="{$value['product']['id']|escape:'html'}">
                            <div class="containerBlock" title="{$value['product']['name']|escape:'html'}">
                                {if strlen($value['lazy']['image']) > 0}
                                    <img src="{$app->request->baseUrl}/images/{$value['lazy']['image']}" title="{$value['text']|escape:'html'}" alt="{$value['text']|escape:'html'}">
                                    {* <div class="val2">{$value['product']['lazy']['price']}</div> *}
                                {elseif $value['color']}
                                    <div class="val1" style="background-color: {$value['color']}" title="{$value['text']|escape:'html'}">&nbsp;{*{$value['text']}*}</div>
                                {else}
                                    <div class="val1">{$value['text']}</div>
                                    {*<div class="val2">{$value['product']['lazy']['price']}</div>*}
                                {/if}
                            </div>
                        </a>
                    </div>
                </label>
            {/foreach}
        </div>
        {foreachelse}
        {*<div class="radioBox2 radioBox">
            <div class="radioBoxHead"><span class="title">{$smarty.const.TEXT_PRODUCTS_GROUPS_VARIANTS}:</span></div>
            {foreach $product.product_group_params.products as $product}
                <label>
                   // <input type="radio" name="products"{if $product['selected']} checked{/if}>
                    <div class="pr-groups">
                        <a href="{$product['lazy']['link']}">
                            <div class="containerBlock" title="{$product['name']}">
                                <img src="{$product['lazy']['image']}" title="{$product['name']}" alt="{$product['name']}">
                                // <div class="val2">{$product['lazy']['price']}</div>
                            </div>
                            </a>
                       // {if !$product['selected']}<a href="{$product['lazy']['link']}">{/if}<div class="containerBlock group-product-name" title="{$product['name']}">{$product['name']}</div>{if !$product['selected']}</a>{/if}
                    </div>
                </label>
            {/foreach}
        </div>*}
    {/foreach}

{/if}