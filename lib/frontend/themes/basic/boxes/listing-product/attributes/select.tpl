<div class="select-box">
    <select class="select" name="{$options_prefix}{$item.name}" data-required="{$smarty.const.PLEASE_SELECT} {$item.title}"{if !Yii::$app->request->get('list_b2b')} onchange="update_attributes_list(this);"{/if}>
        {if $smarty.const.PRODUCTS_ATTRIBUTES_SHOW_SELECT=='True'}<option value="0">{$smarty.const.SELECT} {$item.title}</option>{/if}
        {foreach $item.options as $option}
            <option value="{$option.id}"{if $option.id==$item.selected} selected{/if}{if {strlen($option.params)} > 0} {$option.params}{/if}>{$option.text}</option>
        {/foreach}
    </select>
</div>
