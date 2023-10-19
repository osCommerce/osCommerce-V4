<div class="select-box">
    <select class="select" name="words_{$item.name}" data-required="{$smarty.const.PLEASE_SELECT|escape:'html'} {$item.title|escape:'html'}"{if !Yii::$app->request->get('list_b2b')} onchange="update_attributes(this.form);"{/if}>
        {if $smarty.const.PRODUCTS_ATTRIBUTES_SHOW_SELECT=='True'}<option>{$smarty.const.SELECT} {$item.title}</option>{/if}
        {foreach $item.options_2level as $word => $values}
          {if is_array($values) && count($values) > 1}
            <option value="{$word}"{if $word==$item.selected_word} selected{/if}>{if defined('TEXT_2LEVEL_ATTRIBUTE_SELECTION_XX_OPTIONS')}{sprintf($smarty.const.TEXT_2LEVEL_ATTRIBUTE_SELECTION_XX_OPTIONS, strip_tags($word), count($values))|escape}{else}{strip_tags($word)|escape} ... ({count($values)} options){/if}</option>
          {elseif isset($values[0]['text'])}
            <option value="{$word}"{if $word==$item.selected_word} selected{/if}>{strip_tags($values[0]['text'])|escape}</option>
          {/if}
        {/foreach}
    </select>
    <select class="select" name="{$item.name}" data-required="{$smarty.const.PLEASE_SELECT|escape:'html'} {$item.title|escape:'html'}"{if !Yii::$app->request->get('list_b2b')} onchange="update_attributes(this.form);"{/if}{if !is_array($item.options_2level[$item.selected_word]) || count($item.options_2level[$item.selected_word]) <= 1} style="display:none"{/if}>
        {foreach $item.options_2level[$item.selected_word] as $option}
            {$option_text=$option.text}
            {if $option.id==$item.selected}{$selected_type = $option.type}{/if}
            {if !empty($settings['price_option']) && $settings['price_option']==1 && !empty($option.text_final)}
                {$option_text=$option.text_final}
            {elseif !empty($settings['price_option']) && $settings['price_option']==2 && !empty($option.text_clear)}
                {$option_text=$option.text_clear}
            {/if}
            <option value="{$option.id}"{if $option.id==$item.selected} selected{/if}{if !empty($option.params) } {$option.params}{/if}>{strip_tags($option_text)|escape}</option>
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