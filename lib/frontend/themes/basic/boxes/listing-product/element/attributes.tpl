{use class="Yii"}
{use class="frontend\design\Info"}

{if $product.product_has_attributes}
    {foreach $product.product_attributes_details.attributes_array as $iter => $item}
        {if $product.show_attributes_quantity && $item@last}

            {if count($item.options) > 0}
                <div class="mix-attributes multiattributes" data-id="{$item.id}">
                    {if $item.image}
                        <img src="{$app->request->baseUrl}/images/{$item.image}" alt="{$item.title|escape:'html'}" width="48px;">
                    {/if}
                    {if $item.color}
                    <span class="attribute-color" style="background-color: {$item.color};">&nbsp;</span>
                    {/if}
                    <div class="item-title">{$item.title}</div>
                    <div class="attribute-qty-blocks">
                    {foreach $item.options as $option}
                        {if $option['mix']}
                            <label class="attribute-qty-block" data-id="{$option.id}">

                                {if !empty($option.image)}
                                    <img src="{$app->request->baseUrl}/images/{$option.image}" alt="{$option.text|escape:'html'}"  width="48px;">
                                {/if}
                                <span{if !empty($option.color)} style="color: {$option.color};"{/if}>{$option.text}</span>

                                <div class="mult-qty-input">
                                    <div class="input">
                                        <input type="text"
                                               name="mix_qty[{$product.products_id|escape:'html'}][]"
                                               value="0"
                                               class="qty-inp"
                                               data-min = "0"
                                               data-max="{$quantity_max}"
                                               {if $oqs = \common\helpers\Extensions::isAllowed('OrderQuantityStep')}
                                                   {$oqs::setLimit($order_quantity_data)}
                                               {/if} />
                                    </div>
                                </div>

                                {*\frontend\design\boxes\product\MultiQuantity::widget(['option' => $option])*}
                            </label>
                        {/if}
                    {/foreach}
                    </div>
                </div>
            {/if}

        {elseif $item['type'] == 'radio'}
            <div class="radio-attributes">
                {if $smarty.const.PRODUCTS_ATTRIBUTES_SHOW_SELECT=='True'}{$smarty.const.SELECT} {/if}
                <div class="item-title">{$item.title}</div>
                {foreach $item.options as $option}
                    {$option_text=$option.text}
                {if isset($element.settings[0])}
                    {$settings=$element.settings[0]}
                {/if}
                {if !empty($settings['price_option']) && $settings['price_option']==1 && !empty($option.text_final)}
                    {$option_text=$option.text_final}
                {elseif !empty($settings['price_option']) && $settings['price_option']==2 && !empty($option.text_clear)}
                    {$option_text=$option.text_clear}
                {/if}
                    <label>
                        <input type="radio"
                               name="{$options_prefix}{$item.name}"
                               value="{$option.id}"
                               {if $option.id==$item.selected} checked{/if}
                               {if !empty($option.params) } {$option.params}{/if}>
                        <span class="option">
                            {if !empty($option.image)}
                                <span class="option-image"><img src="{$app->request->baseUrl}/images/{$option.image}" alt="{strip_tags($option_text)|escape:'html'}"  width="48px;"></span>
                            {/if}
                            {if !empty($option.color)}
                                <span class="option-color" style="background-color: {$option.color}" title="{strip_tags($option_text)|escape}"></span>
                            {/if}
                            <span class="option-text">{$option_text}</span>
                        </span>
                    </label>
                {/foreach}
            </div>
        {else}
            <div class="select-attributes">
                <div class="item-title">{$item.title}</div>
                <select class="select"
                        name="{$options_prefix}{$item.name}"
                        data-required="{$smarty.const.PLEASE_SELECT} {$item.title}">
                    {if $smarty.const.PRODUCTS_ATTRIBUTES_SHOW_SELECT=='True'}
                        <option value="0">{$smarty.const.SELECT} {$item.title}</option>
                    {/if}
                    {foreach $item.options as $option}
                        {$option_text=$option.text}
    {*$option_text=$option.text_final*}

                        {if !empty($settings['price_option']) && $settings['price_option']==1 && !empty($option.text_final)}
                            {$option_text=$option.text_final}
                        {elseif !empty($settings['price_option']) && $settings['price_option']==2 && !empty($option.text_clear)}
                            {$option_text=$option.text_clear}
                        {/if}
                        <option value="{$option.id}"
                                {if $option.id==$item.selected} selected{/if}
                                {if !empty($option.params) } {$option.params}{/if}>
                            {strip_tags($option_text)|escape}
                        </option>
                    {/foreach}
                </select>
            </div>
        {/if}
    {/foreach}

{/if}