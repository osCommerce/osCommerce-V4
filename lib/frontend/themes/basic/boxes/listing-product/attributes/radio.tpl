<div class="radio-attributes">
    
   {* radio for please select???? <input type="radio" name="{$item.name}" value="0"{if $item.selected == ''} checked{/if}{if !Yii::$app->request->get('list_b2b')} onchange="update_attributes_list(this.form);"{/if}>*}{*<label>{$smarty.const.PLEASE_SELECT}</label> *}
    {if $smarty.const.PRODUCTS_ATTRIBUTES_SHOW_SELECT=='True'}{$smarty.const.SELECT} {/if}
    {$item.title}
    {foreach $item.options as $option}
        {$option_text=$option.text}
{*$option_text=$option.text_final*}
        {if !empty($settings['price_option']) && $settings['price_option']==1 && !empty($option.text_final)}
            {$option_text=$option.text_final}
        {elseif !empty($settings['price_option']) && $settings['price_option']==2 && !empty($option.text_clear)}
            {$option_text=$option.text_clear}
        {/if}
        <label><input type="radio" name="{$options_prefix}{$item.name}" value="{$option.id}"{if $option.id==$item.selected} checked{/if}{if !empty($option.params) } {$option.params}{/if}{if !Yii::$app->request->get('list_b2b')} onchange="update_attributes_list(this);"{/if}><span>{$option_text}</span></label>
    {/foreach}
</div>
