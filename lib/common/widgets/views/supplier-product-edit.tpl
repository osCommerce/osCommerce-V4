    {use class="common\helpers\Html"}
    {use class="Yii"}

    {assign var=options value = ['class'=>'form-control']}
    {if !$sProduct->status}
        {$options['readonly'] = 'readonly' }
    {/if}
    {Html::hiddenInput('suppliers_id['|cat:$sProduct->suppliers_id|cat:']', $sProduct->suppliers_id, $options)}    
        <div class="tab-sup tab-sup01 js-extra-popup_parent">
            <div class="tab-sup-line1 after" style="width:94%">
                <div>
                    <label>{$smarty.const.TEXT_SUPPLIERS_NAME}</label>
                    {Html::textInput('suppliers_data['|cat:$sProduct->uprid|cat:']['|cat:$sProduct->suppliers_id|cat:'][suppliers_product_name]', $sProduct->suppliers_product_name, $options)}
                </div>
            </div>
            <div class="tab-sup-line1 after" style="width:94%">
                <div>
                    <label>{$smarty.const.TEXT_SUPPLIERS_MODEL}</label>
                    {Html::textInput('suppliers_data['|cat:$sProduct->uprid|cat:']['|cat:$sProduct->suppliers_id|cat:'][suppliers_model]', $sProduct->suppliers_model, $options)}
                </div>
            </div>
            <div class="tab-sup-line after">
                <div>
                    <label>{$smarty.const.TEXT_SUPPLIERS_PRICE}</label>
                    {Html::textInput('suppliers_data['|cat:$sProduct->uprid|cat:']['|cat:$sProduct->suppliers_id|cat:'][suppliers_price]', $sProduct->suppliers_price, array_merge($options,['class'=>'form-control js-supplier-cost js-supplier-recalc']))}
                </div> 
                <div>
                    <label>{$smarty.const.TEXT_CURRENCY}</label>
                    {assign var = cMap value = \yii\helpers\ArrayHelper::map($currencies->currencies, 'id', 'code')}
                    {assign var=iSec value = \yii\helpers\ArrayHelper::index($sProduct->supplier->getAllowedCurrencies()->all(), 'currencies_id')}
                    {assign var=doptions value = ['class'=>'form-control js-supplier-currency js-supplier-recalc' ]}
                    {if !$sProduct->status}
                        {$doptions['readonly'] = 'readonly' }
                    {/if}
                    {Html::dropDownList('suppliers_data['|cat:$sProduct->uprid|cat:']['|cat:$sProduct->suppliers_id|cat:'][currencies_id]', $sProduct->currencies_id, array_intersect_key($cMap, $iSec), $doptions)}
                </div>
            </div>
            <div class="row" style="margin-right: 8px">
                <div class="col-md-4">
                    <label>{$smarty.const.TEXT_SUPPLIER_DISCOUNT}</label>
                    {Html::textInputNullable('suppliers_data['|cat:$sProduct->uprid|cat:']['|cat:$sProduct->suppliers_id|cat:'][supplier_discount]', $sProduct->supplier_discount, array_merge($options,['class'=>'form-control js-supplier-discount js-supplier-recalc', 'placeholder'=>'0.00']))}
                </div>
                {if $service->allow_change_surcharge}
                    <div class="col-md-4">
                        <label>{$smarty.const.TEXT_SURCHARGE}</label>
                        {Html::textInputNullable('suppliers_data['|cat:$sProduct->uprid|cat:']['|cat:$sProduct->suppliers_id|cat:'][suppliers_surcharge_amount]', $sProduct->suppliers_surcharge_amount, array_merge($options,['class'=>'form-control js-supplier-surcharge js-supplier-recalc', 'placeholder'=>$sProduct->supplier->suppliers_surcharge_amount]))}
                    </div>
                {/if}
                {if $service->allow_change_margin}
                    <div class="col-md-4">
                        <label>{$smarty.const.TEXT_MARGIN}</label>
                        {Html::textInputNullable('suppliers_data['|cat:$sProduct->uprid|cat:']['|cat:$sProduct->suppliers_id|cat:'][suppliers_margin_percentage]', $sProduct->suppliers_margin_percentage, array_merge($options,['class'=>'form-control js-supplier-margin js-supplier-recalc', 'placeholder'=>$sProduct->supplier->suppliers_margin_percentage]))}
                    </div>
                {/if}
            </div>
            <div class="tab-sup-line after">
                <div>
                    <label>{$smarty.const.TEXT_SUPPLIERS_QUANTITY}</label>
                    {Html::textInput('suppliers_data['|cat:$sProduct->uprid|cat:']['|cat:$sProduct->suppliers_id|cat:'][suppliers_quantity]', $sProduct->suppliers_quantity, array_merge($options,['class'=>'form-control js-supplier-recalc']))}
                </div>
                <div style="text-align: right">
                    <button type="button" class="btn btn-secondary" onclick="return supplierExtraPopup(this);">{$smarty.const.TEXT_SUPPLIER_EXTRA_DETAILS}</button>
                </div>
            </div>
            <div class="hidden js-SupplierExtraDataPopup popup-box-wrap-page">
                <div class="around-pop-up-page"></div>
                <div class='popup-box-page'>
                    <div class='pop-up-close-page'></div><div class='popup-heading cat-head'>{$smarty.const.TEXT_SUPPLIER_EXTRA_POPUP_TITLE}</div>
                    <div class='pop-up-content-page'>
                        <div class="popup-content">
                            <div class="row">
                                <div class="col-md-6">
                                    <label>{$smarty.const.TEXT_SUPPLIERS_EAN}</label>
                                    {Html::textInput('suppliers_data['|cat:$sProduct->uprid|cat:']['|cat:$sProduct->suppliers_id|cat:'][suppliers_ean]', $sProduct->suppliers_ean, array_merge($options, ['maxlength' => 14]) )}
                                </div>
                                <div class="col-md-6">
                                    <label>{$smarty.const.TEXT_SUPPLIERS_ASIN}</label>
                                    {Html::textInput('suppliers_data['|cat:$sProduct->uprid|cat:']['|cat:$sProduct->suppliers_id|cat:'][suppliers_asin]', $sProduct->suppliers_asin, array_merge($options, ['maxlength' => 10]) )}
                                </div>
                                <div class="col-md-6">
                                    <label>{$smarty.const.TEXT_SUPPLIERS_ISBN}</label>
                                    {Html::textInput('suppliers_data['|cat:$sProduct->uprid|cat:']['|cat:$sProduct->suppliers_id|cat:'][suppliers_isbn]', $sProduct->suppliers_isbn, array_merge($options, ['maxlength' => 13]) )}
                                </div>
                                <div class="col-md-6">
                                    <label>{$smarty.const.TEXT_SUPPLIERS_UPC}</label>
                                    {Html::textInput('suppliers_data['|cat:$sProduct->uprid|cat:']['|cat:$sProduct->suppliers_id|cat:'][suppliers_upc]', $sProduct->suppliers_upc, $options)}
                                </div>
                                <div class="col-md-6">
                                    <label>{$smarty.const.TEXT_SUPPLIERS_TAX_RATE}</label>
                                    {Html::textInputNullable('suppliers_data['|cat:$sProduct->uprid|cat:']['|cat:$sProduct->suppliers_id|cat:'][tax_rate]', $sProduct->tax_rate, array_merge($options,['class'=>'form-control js-supplier-tax-rate js-supplier-recalc', 'placeholder'=>$sProduct->supplier->tax_rate]))}
                                </div>
                                <div class="col-md-6">
                                    <label>&nbsp;</label><br>
                                    <label>
                                        {$smarty.const.TEXT_SUPPLIER_PRICE_WITH_TAX}
                                        {if is_null($sProduct->price_with_tax)}{assign var="price_with_tax" value=$sProduct->supplier->supplier_prices_with_tax}{else}{assign var="price_with_tax" value=$sProduct->price_with_tax}{/if}
                                        {Html::checkbox('suppliers_data['|cat:$sProduct->uprid|cat:']['|cat:$sProduct->suppliers_id|cat:'][price_with_tax]', $price_with_tax, array_merge($options,['class'=>'js-supplier-tax-rate-flag js-supplier-recalc','style'=>'vertical-align:bottom','value'=>1]))}
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="popup-buttons">
                            <div class="btn-toolbar">
                                <div class="text-right">
                                    <button type="button" class="btn btn-primary js-extra-close-button">{$smarty.const.TEXT_CLOSE}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="tab-sup tab-sup02">
            <div>
                <p>
                    <span class="slab">{$smarty.const.TEXT_SUPPLIER_PRICE}</span><br>
                    <span id="supplier_price_{$sProduct->suppliers_id}"></span>
                </p>
            </div>
            <div>
                <p>
                    <span class="slab">{$smarty.const.TEXT_OUR_PRICE}</span><br>
                    <span id="supplier_cost_price_{$sProduct->suppliers_id}"></span>
                </p>
            </div>
        </div>
<script type="text/javascript">
    window.supplierExtraPopup = function(button) {
        var $popupContent = $(button).parents('.js-extra-popup_parent').find('.js-SupplierExtraDataPopup');
        $popupContent.removeClass('hidden');
        var $contentCont = $('#content, .content-container');
        var cZKeep = $contentCont.css('z-index'),
            cPKeep = $contentCont.css('position');
        $contentCont.css({ 'position': 'relative', 'z-index': '100'});
        $('.w-or-prev-next > .tabbable').css({ 'z-index': '5'});
        $popupContent.find('.pop-up-close-page, .js-extra-close-button').off('click').on('click',function(){
            $(this).parents('.js-SupplierExtraDataPopup').addClass('hidden');
            $contentCont.css({ 'position': cZKeep, 'z-index': cPKeep});
        });

        return false;
    }
</script>