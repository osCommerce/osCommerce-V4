<div class="select-box">
    <select class="select" name="{$item.name}" data-required="{$smarty.const.PLEASE_SELECT|escape:'html'} {$item.title|escape:'html'}"{if !Yii::$app->request->get('list_b2b')} onchange="update_attributes(this.form);"{/if}>
        {if $smarty.const.PRODUCTS_ATTRIBUTES_SHOW_SELECT=='True'}<option value="0">{$smarty.const.SELECT} {$item.title}</option>{/if}
        {foreach $item.options as $option}
            {$option_text=$option.text}
            {if !empty($settings['price_option']) && $settings['price_option']==1 && !empty($option.text_final)}
                {$option_text=$option.text_final}
            {elseif !empty($settings['price_option']) && $settings['price_option']==2 && !empty($option.text_clear)}
                {$option_text=$option.text_clear}
            {/if}
            <option value="{$option.id}"{if $option.id==$item.selected} selected{/if}{if !empty($option.params) } {$option.params}{/if}>{strip_tags($option_text)|escape}</option>
        {/foreach}
    </select>
</div>
