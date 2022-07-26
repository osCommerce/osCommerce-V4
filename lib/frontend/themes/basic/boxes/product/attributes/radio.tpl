<div class="radio-attributes">
    {if !empty($item.image)}<img src="{$app->request->baseUrl}/images/{$item.image}" alt="{$item.title|escape:'html'}" width="48px;">{/if}
    {if !empty($item.color)}<span style="color: {$item.color};">{/if}
    {$item.title}
    {if !empty($item.color)}</span>{/if}
    {*<input type="radio" name="{$item.name}" value="0"{if $item.selected == ''} checked{/if}{if !Yii::$app->request->get('list_b2b')} onchange="update_attributes(this.form);"{/if}><label>{$smarty.const.PLEASE_SELECT}</label>*}
    {foreach $item.options as $option}
        {$option_text=$option.text}
{*$option_text=$option.text_final*}
        {if !empty($settings['price_option']) && $settings['price_option']==1 && !empty($option.text_final)}
            {$option_text=$option.text_final}
        {elseif !empty($settings['price_option']) && $settings['price_option']==2 && !empty($option.text_clear)}
            {$option_text=$option.text_clear}
        {/if}
        <label>
            <input type="radio" name="{$item.name}" value="{$option.id}"{if $option.id==$item.selected} checked{/if}{if !empty($option.params) } {$option.params}{/if}{if !Yii::$app->request->get('list_b2b')} onchange="update_attributes(this.form);"{/if}><span class="option-item">
            {if !empty($option.image)}<img src="{$app->request->baseUrl}/images/{$option.image}" alt="{strip_tags($option_text)|escape:'html'}"  width="48px;">{/if}
            <span{if !empty($option.color)} style="color: {$option.color};"{/if}>{$option_text}</span>
                </span>
        </label>
    {/foreach}
</div>
