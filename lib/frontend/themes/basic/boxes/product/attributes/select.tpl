<div class="select-box">
    {if count($item.options) == 1 && $item.options[0].type > 0}
         <span class="attr-title">{$item.options[0].text}</span>
        {$selected_type = $item.options[0].type}
        {$style='style="display:none"'}
    {/if}
    <select class="select" name="{$item.name}" data-required="{$smarty.const.PLEASE_SELECT|escape:'html'} {$item.title|escape:'html'}"{if !Yii::$app->request->get('list_b2b')} onchange="update_attributes(this.form);"{/if} {$style|default:null}>
        {if $smarty.const.PRODUCTS_ATTRIBUTES_SHOW_SELECT=='True'}<option value="0">{$smarty.const.SELECT} {$item.title}</option>{/if}
        {foreach $item.options as $option}
            {$option_text=$option.text}
            {if $option.id==$item.selected}{$selected_type = $option.type}{/if}
            {if !empty($settings['price_option']) && $settings['price_option']==1 && !empty($option.text_final)}
                {$option_text=$option.text_final}
            {elseif !empty($settings['price_option']) && $settings['price_option']==2 && !empty($option.text_clear)}
                {$option_text=$option.text_clear}
            {/if}
            <option value="{$option.id}"{if $option.id==$item.selected} selected{/if}{if !empty($option.params) } {$option.params}{/if} data-type="{$option.type}">{strip_tags($option_text)|escape}</option>
        {/foreach}
    </select>
    {if $selected_type == 1}
    <div class="textinput js-attr-text-input">
        <input type="text" name="attr_text[{$item.id}]" value="{$attrText[$item.id]|default:null}">
    </div>
    {/if}
    {if $selected_type == 2}
        <div class="textarea js-attr-area-input">
            <textarea cols=50 rows=7 name="attr_text[{$item.id}]">{$attrText[$item.id]|default:null}</textarea>
        </div>
    {/if}
</div>
