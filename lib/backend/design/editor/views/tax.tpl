{use class="yii\helpers\Html"}
{if $wrap}
<label>{$smarty.const.TABLE_HEADING_TAX}:</label>
<div class="label_value">
{/if}
    {assign var="tax_selected" value="{$product['overwritten']['tax_selected']}"}
    {if isset($product['products_tax_class_id'])}
        {assign var="class_id" value=$product['products_tax_class_id']}
    {else}
        {assign var="class_id" value=$product['tax_class_id']}
    {/if}
    
    {if $tax_selected neq ''}
        {$zone_id = $tax_selected}
    {else}
        {assign var="zone" value="{\common\helpers\Tax::get_zone_id($class_id, $tax_address['entry_country_id'], $tax_address['entry_zone_id'])}"}
        {assign var="zone_id" value="{$class_id}_{$zone}"}
    {/if}
    {Html::dropDownList('product_info[][tax]['|cat:$uprid|cat:']', $zone_id, $tax_class_array, ['class'=>'form-control tax', 'onchange'=>$onchange, 'prompt' => TEXT_NONE])}    
{if $wrap}
</div>
{/if}