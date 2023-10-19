{use class="common\helpers\Html"}

{if $edit}
    <div class="">
        <div class="overwritten-holder">
            <div class="extra-disc-box overwritten-percent">
                {Html::dropDownList('product_info[][dis_action_percent]['|cat:$product['id']|cat:']', $predefined['percent_action'], ['-' => '-', '+' => '+'], ['class' => ' action-percent'])}
                <div>
                    {Html::textInput('product_info[][dis_action_percent_value]['|cat:$product['id']|cat:']', $predefined['percent_value'], ['class' => 'spinner-percent form-control'])}
                </div>
            </div>
            <div class="extra-disc-box overwritten-fixed">
                {Html::dropDownList('product_info[][dis_action_fixed]['|cat:$product['id']|cat:']', $predefined['fixed_action'], ['-' => '-', '+' => '+'], ['class' => ' action-fixed'])}
                <div>
                    <div style="margin: 4px 0 4px">{Html::textInput('product_info[][dis_action_fixed_value]['|cat:$product['id']|cat:']', $predefined['fixed_value'], ['class' => 'spinner-fixed form-control'])}</div>
                    <div style="margin-bottom: 3px" class="vat-aria">{Html::textInput('dis_action_fixed_value', $predefined['fixed_value'], ['class' => 'spinner-fixed-tax form-control'])}</div>
                </div>

            </div>
            <div class="">
                <select name="" class="form-control overwritten-choose">
                    <option value="percent"{if $overwrittenType == 'percent'} selected{/if}>%</option>
                    <option value="fixed"{if $overwrittenType == 'fixed'} selected{/if}>
                        {if $currency['symbol_left']}{$currency['symbol_left']}{else}{$currency['symbol_right']}{/if}
                    </option>
                </select>
            </div>
        </div>

    </div>
    <div class="overwritten-is"> = </div>

    <div class="product-result-price">

        {Html::textInputNullable('product_info[][result_price]['|cat:$product['id']|cat:']', $product['final_price'],['class'=>'form-control js-bind-ctrl edit-price result-price-item keep-val', 'placeholder' => ''])}

        <div class="vat-aria">
        {Html::textInputNullable('result-price-tax', $product['final_price'],['class'=>'form-control js-bind-ctrl edit-price result-price-item-tax keep-val', 'placeholder' => ''])}
        </div>

        {*$manager->render('Price', ['field' => 'product_info[][result_price]['|cat:$product['id']|cat:']', 'price' => $product['final_price'], 'tax' => 0, 'qty' => \common\helpers\Product::getVirtualItemQuantityValue($product['id']), 'currency' => $cart->currency, 'isEditInGrid' => true, 'classname' => 'result-price' ])*}
    </div>

{else}

    {if $isInitPriceOverwritten}

        <div class="overwritten-final-price">
            {$manager->render('Price', ['field' => 'old-price', 'price' => $product['price'], 'tax' => 0, 'qty' => 1, 'currency' => $cart->currency, 'isEditInGrid' => false, 'classname' => 'start-price' ])}

            {if $predefined['percent_value'] || $predefined['fixed_value']}
                {$manager->render('Price', ['field' => 'final-price', 'price' => $newInitPrice, 'tax' => 0, 'qty' => 1, 'currency' => $cart->currency, 'isEditInGrid' => false, 'classname' => 'start-price' ])}
            {/if}
        </div>

    {elseif $predefined['percent_value'] || $predefined['fixed_value']}

        {$manager->render('Price', ['field' => 'result_price', 'price' => $product['price'], 'tax' => 0, 'qty' => 1, 'currency' => $cart->currency, 'isEditInGrid' => false, 'classname' => 'result-price' ])}

    {/if}


    {if $predefined['percent_value'] || $predefined['fixed_value']}
        <div class="overwritten-discount">
            {if $predefined['percent_value']}
                {$predefined['percent_action']}{$predefined['percent_value']}%
            {/if}
            {if $predefined['fixed_value']}
                {$predefined['fixed_action']}{$predefined['fixed_value']}{$currency['symbol_left']}
            {/if}
        </div>
    {/if}

{/if}