{use class="\common\helpers\Html"}
{use class="\yii\helpers\ArrayHelper"}

<tr class="js-supplier-rule-row">
    <td>
        {if $rulesRO}
            {ArrayHelper::getValue($currenciesVariants, $rule['currencies_id'])}
        {else}
            {Html::dropDownList('suppliers_data['|cat:$supplier_idx|cat:'][price_rule]['|cat:$supplier_rule_idx|cat:'][currencies_id]', $rule['currencies_id'], $currenciesVariants, ['class'=>'form-control'])}
        {/if}
    </td>
    <td class="js-cond js-cond-fromTo">
        {if $rulesRO}{$rule['supplier_price_from']|default:null}{else}
        {Html::textInput('suppliers_data['|cat:$supplier_idx|cat:'][price_rule]['|cat:$supplier_rule_idx|cat:'][supplier_price_from]', $rule['supplier_price_from'], ['class'=>'form-control'])}
        {/if}
    </td>
    <td class="js-cond js-cond-fromTo">
        {if $rulesRO}{$rule['supplier_price_to']|default:null}{else}
        {Html::textInput('suppliers_data['|cat:$supplier_idx|cat:'][price_rule]['|cat:$supplier_rule_idx|cat:'][supplier_price_to]', $rule['supplier_price_to'], ['class'=>'form-control'])}
        {/if}
    </td>
    <td class="js-cond js-cond-notBelow">
        {if $rulesRO}{$rule['supplier_price_not_below']|default:null}{else}
        {Html::textInput('suppliers_data['|cat:$supplier_idx|cat:'][price_rule]['|cat:$supplier_rule_idx|cat:'][supplier_price_not_below]', $rule['supplier_price_not_below'], ['class'=>'form-control'])}
        {/if}
    </td>
    <td>
        {if $rulesRO}{$rule['price_formula_text']|default:null}{else}
        <div class="input-group js-price-formula-group">
            {Html::textInput('suppliers_data['|cat:$supplier_idx|cat:'][price_rule]['|cat:$supplier_rule_idx|cat:'][price_formula_text]', $rule['price_formula_text'], ['maxlength'=>'64', 'size'=>'32', 'class'=>'form-control js-price-formula-text', 'readonly'=>'readonly'])}
            {Html::hiddenInput('suppliers_data['|cat:$supplier_idx|cat:'][price_rule]['|cat:$supplier_rule_idx|cat:'][price_formula]', $rule['price_formula'], ['class'=>'js-price-formula-data'])}
            <div class="input-group-addon js-price-formula" data-formula-allow-params=""><i class="icon-money"></i></div>
        </div>
        {/if}
    </td>
    <td>
        {if $rulesRO}
            {if is_null($rule['supplier_discount'])}
                {if isset($default_rule['supplier_discount'])}{$default_rule['supplier_discount']}{/if}
            {else}
                {$rule['supplier_discount']}
            {/if}
        {else}
        {Html::textInputNullable('suppliers_data['|cat:$supplier_idx|cat:'][price_rule]['|cat:$supplier_rule_idx|cat:'][supplier_discount]', $rule['supplier_discount'], ['class'=>'form-control','placeholder'=>$default_rule['supplier_discount']])}
        {/if}
    </td>
    <td>
        {if $rulesRO}
            {if is_null($rule['surcharge_amount'])}
                {if isset($default_rule['surcharge_amount_formatted'])}{$default_rule['surcharge_amount_formatted']}{/if}
            {else}
                {$rule['surcharge_amount']}
            {/if}
        {else}
        {Html::textInputNullable('suppliers_data['|cat:$supplier_idx|cat:'][price_rule]['|cat:$supplier_rule_idx|cat:'][surcharge_amount]', $rule['surcharge_amount'], ['class'=>'form-control','placeholder'=>$default_rule['surcharge_amount']])}
        {/if}
    </td>
    <td>
        {if $rulesRO}
            {if is_null($rule['margin_percentage'])}
                {if isset($default_rule['margin_percentage'])}{$default_rule['margin_percentage']}{/if}
            {else}
                {$rule['margin_percentage']}
            {/if}
        {else}
        {Html::textInputNullable('suppliers_data['|cat:$supplier_idx|cat:'][price_rule]['|cat:$supplier_rule_idx|cat:'][margin_percentage]', $rule['margin_percentage'], ['class'=>'form-control','placeholder'=>$default_rule['margin_percentage']])}
        {/if}
    </td>
    {if not $rulesRO}
    <td>
        <a href="javascript:void(0)" class="js-remove-supplier-rule"><i class="icon-trash color-alert"></i></a>
    </td>
    {/if}
</tr>