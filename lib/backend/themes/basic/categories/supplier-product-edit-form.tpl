{use class="common\helpers\Html"}
{assign var=options value = ['class'=>'form-control']}
{if !$sInfo->status}
    {$options['readonly'] = 'readonly' }
{/if}
{Html::hiddenInput('suppliers_id_'|cat:$uprid|cat:'['|cat:$sInfo->suppliers_id|cat:']', $sInfo->suppliers_id, $options)}

<div class="row supplier-cols">
    <div class="col-md-4 js-edit-supplier-product-popup-container">
        <div class="row">
            <div class="col-md-12">{$smarty.const.TEXT_SUPPLIERS_PRODUCT_DETAILS}</div>
        </div>
        <div class="js-edit-supplier-product-popup">
            <div class="row">
                <div class="col-md-12 js-bind-text bind-text{if !$sInfo->suppliers_product_name} hide-suppliers-info{/if}">
                    <label>{$smarty.const.TEXT_SUPPLIERS_PRODUCT_NAME}</label>
                    <div class="input-value"><span class="js-bind-value">{$sInfo->suppliers_product_name}</span>&nbsp;</div>
                    {Html::textInput('suppliers_data['|cat:$uprid|cat:']['|cat:$sInfo->suppliers_id|cat:'][suppliers_product_name]', $sInfo->suppliers_product_name, array_merge($options, ['class' => 'form-control js-bind-ctrl']))}
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 js-bind-text bind-text{if !$sInfo->suppliers_model} hide-suppliers-info{/if}">
                    <label>{$smarty.const.TEXT_SUPPLIERS_MODEL}</label>
                    <div class="input-value"><span class="js-bind-value">{$sInfo->suppliers_model}</span>&nbsp;</div>
                    {Html::textInput('suppliers_data['|cat:$uprid|cat:']['|cat:$sInfo->suppliers_id|cat:'][suppliers_model]', $sInfo->suppliers_model, array_merge($options, ['class' => 'form-control js-bind-ctrl']))}
                </div>
                <div class="col-md-6 js-bind-text bind-text{if !$sInfo->suppliers_ean} hide-suppliers-info{/if}">
                    <label>{$smarty.const.TEXT_SUPPLIERS_EAN}</label>
                    <div class="input-value"><span class="js-bind-value">{$sInfo->suppliers_ean}</span>&nbsp;</div>
                    {Html::textInput('suppliers_data['|cat:$uprid|cat:']['|cat:$sInfo->suppliers_id|cat:'][suppliers_ean]', $sInfo->suppliers_ean, array_merge($options, ['class' => 'form-control js-bind-ctrl', 'maxlength' => 14]) )}
                </div>
                <div class="col-md-6 js-bind-text bind-text{if !$sInfo->suppliers_upc} hide-suppliers-info{/if}">
                    <label>{$smarty.const.TEXT_SUPPLIERS_UPC}</label>
                    <div class="input-value"><span class="js-bind-value">{$sInfo->suppliers_upc}</span>&nbsp;</div>
                    {Html::textInput('suppliers_data['|cat:$uprid|cat:']['|cat:$sInfo->suppliers_id|cat:'][suppliers_upc]', $sInfo->suppliers_upc, array_merge($options, ['class' => 'form-control js-bind-ctrl']))}
                </div>
                <div class="col-md-6 js-bind-text bind-text{if !$sInfo->suppliers_asin} hide-suppliers-info{/if}">
                    <label>{$smarty.const.TEXT_SUPPLIERS_ASIN}</label>
                    <div class="input-value"><span class="js-bind-value">{$sInfo->suppliers_asin}</span>&nbsp;</div>
                    {Html::textInput('suppliers_data['|cat:$uprid|cat:']['|cat:$sInfo->suppliers_id|cat:'][suppliers_asin]', $sInfo->suppliers_asin, array_merge($options, ['class' => 'form-control js-bind-ctrl', 'maxlength' => 10]) )}
                </div>
                <div class="col-md-6 js-bind-text bind-text{if !$sInfo->suppliers_isbn} hide-suppliers-info{/if}">
                    <label>{$smarty.const.TEXT_SUPPLIERS_ISBN}</label>
                    <div class="input-value"><span class="js-bind-value">{$sInfo->suppliers_isbn}</span>&nbsp;</div>
                    {Html::textInput('suppliers_data['|cat:$uprid|cat:']['|cat:$sInfo->suppliers_id|cat:'][suppliers_isbn]', $sInfo->suppliers_isbn, array_merge($options, ['class' => 'form-control js-bind-ctrl', 'maxlength' => 13]) )}
                </div>
                {if ($pInfo->manual_stock_unlimited|default:null == 0)}
                <div class="col-md-6 js-bind-text bind-text{if !$sInfo->emergency_stock} hide-suppliers-info{/if}">
                    <label>{$smarty.const.TEXT_USE_EMERGENCY_STOCK}</label><input type="checkbox" name="suppliers_data[{$uprid}][{$sInfo->suppliers_id}][emergency_stock]" value="1" {if $sInfo->emergency_stock}checked {/if} />
                </div>
                {/if}


                <div class="col-md-6 js-bind-text bind-text stock-reorder-supplier{if !$sInfo->stock_reorder_level} hide-suppliers-info{/if}">
                    {if ($pInfo->manual_stock_unlimited|default:null == 0)}
                        <label>{$smarty.const.TEXT_STOCK_REORDER_LEVEL}&nbsp;<input type="checkbox" name="suppliers_data[{$uprid}][{$sInfo->suppliers_id}][stock_reorder_level_on]" value="1" {if $sInfo->stock_reorder_level_on}checked {/if}/></label>
                        {Html::input('text', 'suppliers_data['|cat:$uprid|cat:']['|cat:$sInfo->suppliers_id|cat:'][stock_reorder_level]', $sInfo->stock_reorder_level, ['class'=>'form-control form-control-small-qty'])}
                    {/if}
                </div>

                <div class="col-md-6 js-bind-text bind-text stock-reorder-supplier{if !$sInfo->stock_reorder_quantity} hide-suppliers-info{/if}">
                    {if ($pInfo->manual_stock_unlimited|default:null == 0)}
                        <label>{$smarty.const.TEXT_STOCK_REORDER_QUANTITY}&nbsp;<input type="checkbox" name="suppliers_data[{$uprid}][{$sInfo->suppliers_id}][stock_reorder_quantity_on]" value="1" {if $sInfo->stock_reorder_quantity_on}checked {/if}/></label>
                        {Html::input('text', 'suppliers_data['|cat:$uprid|cat:']['|cat:$sInfo->suppliers_id|cat:'][stock_reorder_quantity]', $sInfo->stock_reorder_quantity, ['class'=>'form-control form-control-small-qty'])}
                    {/if}
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 js-bind-text bind-text{if !$sInfo->notes} hide-suppliers-info{/if}">
                    <label>{$smarty.const.TEXT_SUPPLIERS_NOTES}</label>
                    <div class="input-value"><span class="js-bind-value">{$sInfo->notes}</span>&nbsp;</div>
                    <textarea name="{'suppliers_data['|cat:$uprid|cat:']['|cat:$sInfo->suppliers_id|cat:'][notes]'}" rows="2" cols="32" class="form-control js-bind-ctrl">{$sInfo->notes}</textarea>
                </div>
            </div>
                
        </div>
        <div class="margined">
            <a href="javascript:void(0)" class="js-supplier-detail-edit">{$smarty.const.TEXT_EDIT_DETAILS}</a>
        </div>
    </div>

    <div class="col-md-8">
        <div class="row quantity-discount">
            <div class="col-md-5 price-rule">
                <div>
                    <label>{$smarty.const.TEXT_SUPPLIERS_PRICE}</label>
                    <div class="input-group-with-select">
                        {Html::textInput('suppliers_data['|cat:$uprid|cat:']['|cat:$sInfo->suppliers_id|cat:'][suppliers_price]', $sInfo->suppliers_price, array_merge($options,['class'=>'form-control js-supplier-cost js-supplier-recalc']))}
                        <div class="input-group-select">
                            {assign var = cMap value = \yii\helpers\ArrayHelper::map($currencies->currencies, 'id', 'code')}
                            {if $sInfo->supplier}
                                {assign var=iSec value = \yii\helpers\ArrayHelper::index($sInfo->supplier->getAllowedCurrencies()->all(), 'currencies_id')}
                            {else}
                                {assign var=iSec value = array()}
                            {/if}
                            {assign var=doptions value = ['class'=>'form-control js-supplier-currency js-supplier-recalc']}
                            {if !$sInfo->status}
                                {$doptions['readonly'] = 'readonly' }
                            {/if}
                            {Html::dropDownList('suppliers_data['|cat:$uprid|cat:']['|cat:$sInfo->suppliers_id|cat:'][currencies_id]', $sInfo->currencies_id, array_intersect_key($cMap, $iSec), $doptions)}
                        </div>
                    </div>
                </div>
                <div class="js-edit-supplier-product-popup-container">
                    <div class="js-edit-supplier-product-popup">
                        <div class="js-bind-text bind-text">
                            <div class="hide-in-popup margined">
                                <i>{$smarty.const.TEXT_SUPPLIERS_TAX_RATE} <span class="js-bind-value">{if is_null($sInfo->tax_rate)}{$sInfo->supplier->tax_rate}{else}{$sInfo->tax_rate}{/if}</span> %</i> <i class="icon-pencil link-cursor color-hilite js-supplier-detail-edit"></i>
                            </div>
                            <div class="show-in-popup">
                                <label>{$smarty.const.TEXT_SUPPLIERS_TAX_RATE}</label>
                                {Html::textInputNullable('suppliers_data['|cat:$uprid|cat:']['|cat:$sInfo->suppliers_id|cat:'][tax_rate]', $sInfo->tax_rate, array_merge($options,['class'=>'form-control js-bind-ctrl js-supplier-tax-rate js-supplier-recalc', 'placeholder'=>$sInfo->supplier->tax_rate]))}
                            </div>
                        </div>
                        <div class="show-in-popup">
                            <label>
                                {$smarty.const.TEXT_SUPPLIER_PRICE_WITH_TAX}
                                {if is_null($sInfo->price_with_tax)}{assign var="price_with_tax" value=$sInfo->supplier->supplier_prices_with_tax}{else}{assign var="price_with_tax" value=$sInfo->price_with_tax}{/if}
                                {Html::checkbox('suppliers_data['|cat:$uprid|cat:']['|cat:$sInfo->suppliers_id|cat:'][price_with_tax]', $price_with_tax, array_merge($options,['class'=>'no-uniform js-supplier-tax-rate-flag js-supplier-recalc','style'=>'vertical-align:bottom','value'=>1]))}
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-7" style="padding-top: 5px">
                {\backend\design\QuantityDiscount::widget(['name' => 'suppliers_discount['|cat:$uprid|cat:']['|cat:$sInfo->suppliers_id|cat:']', 'value' => \common\helpers\Suppliers::getDiscountValuesArray($sInfo->suppliers_price_discount)])}
            </div>
        </div>
        <div class="tab-sup-rules">
            <div class="js-applied-rule"></div>
            <div class="js-applied-formula"></div>
        </div>
        <hr>
        <div class="row align-items-end">
            <div class="col-md-4">
                <label>{$smarty.const.TEXT_SUPPLIER_DISCOUNT}</label>
                {Html::textInputNullable('suppliers_data['|cat:$uprid|cat:']['|cat:$sInfo->suppliers_id|cat:'][supplier_discount]', $sInfo->supplier_discount, array_merge($options,['class'=>'form-control js-supplier-discount js-supplier-recalc', 'placeholder'=>'0.00']))}
            </div>
            <div class="col-md-4">
                <label>{$smarty.const.TEXT_SURCHARGE}</label>
                {Html::textInputNullable('suppliers_data['|cat:$uprid|cat:']['|cat:$sInfo->suppliers_id|cat:'][suppliers_surcharge_amount]', $sInfo->suppliers_surcharge_amount, array_merge($options,['class'=>'form-control js-supplier-surcharge js-supplier-recalc', 'placeholder'=>$sInfo->supplier->suppliers_surcharge_amount]))}
            </div>
            <div class="col-md-4">
                <label>{$smarty.const.TEXT_MARGIN}</label>
                {Html::textInputNullable('suppliers_data['|cat:$uprid|cat:']['|cat:$sInfo->suppliers_id|cat:'][suppliers_margin_percentage]', $sInfo->suppliers_margin_percentage, array_merge($options,['class'=>'form-control js-supplier-margin js-supplier-recalc', 'placeholder'=>$sInfo->supplier->suppliers_margin_percentage]))}
            </div>
        </div>
        <hr>
        <div class="row price-qty-row">
            <div class="col-sm-4 price-qty-col">
                <div class="bs-supplier-price-cell">
                    <label>{$smarty.const.TEXT_SUPPLIERS_OUR_PRICE}</label>
                    <div class="row">
                        <div class="col-sm-6"><div id="supplier_cost_price_net_{$idPart}" class="js-supplier-landed-price-net-displayed"></div>{$smarty.const.TEXT_NET}</div>
                        <div class="col-sm-6"><div id="supplier_cost_price_gross_{$idPart}"><span class="js-supplier-landed-price-gross-displayed"></span><i class="icon-pencil link-cursor color-hilite js-supplier-landed-price-edit" data-toggle="modal" data-target="#editLandedPrice" data-supplier_id="{$idPart}"></i></div>{$smarty.const.TEXT_GROSS}<div class="js-overridden-mark" style>manually overridden</div></div>

                        {Html::hiddenInput('suppliers_data['|cat:$uprid|cat:']['|cat:$sInfo->suppliers_id|cat:'][landed_price]', $sInfo->landed_price, array_merge($options,['class'=>'form-control js-supplier-landed-price-field']))}
                    </div>
                </div>
                <div class="supplier-cols">
                        <label>{$smarty.const.TEXT_SUPPLIERS_QUANTITY}</label>
                        {Html::textInputNullable('suppliers_data['|cat:$uprid|cat:']['|cat:$sInfo->suppliers_id|cat:'][suppliers_quantity]', $sInfo->suppliers_quantity, array_merge($options,['class'=>'supplier-qty', 'button' => ['options'=> ['class' => 'supplier-qty' ]] ]))}
                        {*Html::textInput('suppliers_data['|cat:$uprid|cat:']['|cat:$sInfo->suppliers_id|cat:'][suppliers_quantity]', $sInfo->suppliers_quantity, array_merge($options,['class'=>'form-control js-supplier-recalc']))*}
                </div>
            </div>
            <div class="col-sm-8">
                <div class="row">
                    <div class="col-sm-6 bs-supplier-price-cell">
                        <label>{$smarty.const.TEXT_CALCULATED}</label>
                        <div class="row">
                            <div class="col-sm-6"><div id="calc_net_price_{$idPart}" class="js-supplier-calc-net-price"></div>{$smarty.const.TEXT_NET}</div>
                            <div class="col-sm-6"><div id="calc_gross_price_{$idPart}" class="js-supplier-calc-gross-price"></div>{$smarty.const.TEXT_GROSS}</div>
                        </div>
                    </div>
                    <div class="col-sm-6 bs-supplier-price-cell">
                        <label>{$smarty.const.TEXT_OUR_PROFIT}</label>
                        <div id="calc_profit_{$idPart}" class="supplier-big-price js-supplier-calc-profit"></div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-6 bs-supplier-price-cell bs-background-gray">
                        <label>{$smarty.const.TEXT_OUR_CURRENT}</label>
                        <div class="row">
                            <div class="col-sm-6"><div id="our_net_price_{$idPart}" class="js-supplier-cur-net-price"></div>{$smarty.const.TEXT_NET}</div>
                            <div class="col-sm-6"><div id="our_gross_price_{$idPart}" class="js-supplier-cur-gross-price"></div>{$smarty.const.TEXT_GROSS}</div>
                        </div>
                    </div>
                    <div class="col-sm-6 bs-supplier-price-cell bs-background-gray">
                        <label>{$smarty.const.TEXT_OUR_CURRENT_PROFIT}</label>
                        <div id="our_profit_{$idPart}" class="supplier-big-price js-supplier-cur-profit"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

