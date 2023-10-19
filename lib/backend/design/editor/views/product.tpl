{use class="common\helpers\Html"}
<div class="product-details" data-box="{$product['products_id']}" >
     <div class="widget box box-no-shadow">
        {if $edit}
            <input type="hidden" name="product_info[][products_id]" value="{$product['products_id']}"/>
            <input type="hidden" name="uprid" value="{\common\helpers\Inventory::normalize_id($product['id'])}"/>
        {else}
            <div class="widget-header">
                <h4>
                  <div class="product-header thumb">{$product['image_thumb']}</div>
                  <div class="product-header name">
                      <div class="prod_name" title="{$product['products_name']}">{strip_tags($product['products_name'])|truncate:60:"...":true}</div>
                      {Html::hiddenInput('product_info[][name]', stripslashes($product['products_name']), ['class' => 'product_name form-control', 'data-reg'=> 'name'])}
                  </div>
                </h4>
                <div class="toolbar no-padding">
                    <div class="btn-group">
                        <span class="btn btn-xs widget-collapse list_collapse"><i class="icon-angle-down"></i></span>
                    </div>
                </div>
            </div>
        {/if}
        <div class="widget-content after">
            <div class="tl-or-pr-edt-left">
                {$product['image']}
            </div>
            <div class="tl-or-pr-edt-right">
                {if $edit}
                <div class="w-line-row">
                    <div class="edp-line ed-pname">
                        <label>{$smarty.const.TEXT_PRODUCT_NAME}:</label>
                        <span class="name">{stripslashes($product['name'])}</span>
                        {Html::hiddenInput('product_info[][name]', stripslashes($product['name']), ['class' => 'product_name form-control', 'data-reg'=> 'name'])}
                        <span class="edit_name"><i class="icon-pencil" data-element="product_name" onclick="manualEdit(this)"><b>{$smarty.const.APPLY}</b></i></span>
                    </div>
                 </div>
                {/if}
                <div class="w-line-row">
                    <div class="or-ed-ad-row-sum {if isset($product['product_details']) && ($product['product_details']['product']['pack_unit'] > 0 || $product['product_details']['product']['packaging'] > 0) } or-ed-ad-row-sum-big{/if}">

                        {if isset($product['product_details']) && ($product['product_details']['product']['pack_unit'] > 0 || $product['product_details']['product']['packaging'] > 0)}
                            <span class="with_tax label_ed-or-pr label_ed-or-pr-auto">
                            <table width="100%" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td align="right">{$smarty.const.TABLE_HEADING_PRICE_EXCLUDING_TAX}: </td>
                                    <td><span class="final_price_unit"></span></td>
                                    <td rowspan="2" class="union_td"><span>}</span></td>
                                </tr>
                                <tr>
                                    <td align="right">{$smarty.const.TABLE_HEADING_PRICE_INCLUDING_TAX}: </td>
                                    <td><span class="final_price_tax_unit"></span></td>
                                </tr>
                                {if $product['product_details']['product']['pack_unit']}
                                <tr>
                                    <td align="right">{$smarty.const.TABLE_HEADING_PRICE_EXCLUDING_TAX}: </td>
                                    <td><span class="final_price_pack_unit"></span></td>
                                    <td rowspan="2" class="union_td"><span>}</span></td>
                                </tr>
                                <tr>
                                    <td align="right">{$smarty.const.TABLE_HEADING_PRICE_INCLUDING_TAX}: </td>
                                    <td><span class="final_price_tax_pack_unit"></span></td>
                                </tr>
                                {/if}
                                {if $product['product_details']['product']['packaging'] > 0}
                                <tr>
                                    <td align="right">{$smarty.const.TABLE_HEADING_PRICE_EXCLUDING_TAX}: </td>
                                    <td><span class="final_price_packaging"></span></td>
                                    <td rowspan="2" class="union_td"><span>}</span></td>
                                </tr>
                                <tr>
                                    <td align="right">{$smarty.const.TABLE_HEADING_PRICE_INCLUDING_TAX}: </td>
                                    <td><span class="final_price_tax_packaging"></span></td>
                                </tr>                                            
                                {/if}
                            </table>
                        </span>
                        {else}
                            <span class="with_tax label_ed-or-pr">
                                <table width="100%" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td align="right" style="width: 150px">{$smarty.const.TABLE_HEADING_PRICE_EXCLUDING_TAX}: </td>
                                        <td style="padding-top: 3px">
                                            {*<span class="old-price"></span>
                                            <span class="final_price "></span>*}
                                            {*Html::hiddenInput('product_info[][final_price]', 0, ['class' => 'edit-price form-control', 'data-reg' => 'final_price'])*}

    {Html::textInputNullable('product_info[][final_price]', 0,['class'=>'form-control js-bind-ctrl edit-price keep-val', 'placeholder' => $product['price']])}
                                        </td>
                                        <td rowspan="2" class="union_td">{*<span>}</span>*}</td>
                                    </tr>
                                    <tr class="vat-aria">
                                        <td align="right">{$smarty.const.TABLE_HEADING_PRICE_INCLUDING_TAX}: </td>
                                        <td>
                                            {*<span class="old-price-tax"></span>
                                            <span class="final_price_tax "></span>*}
                                            {*Html::hiddenInput('product_info[][final_price_tax]', 0, ['class' => 'edit-price form-control', 'data-reg' => 'final_price_tax'])*}
                                            {Html::textInputNullable('product_info[][final_price_tax]', 0,['class'=>'form-control js-bind-ctrl edit-price keep-val', 'placeholder' => 0])}
                                        </td>
                                    </tr>
                                </table>
                            </span>

                            {*<span class="edit_price"><i class="icon-pencil" data-element="edit-price" onclick="manualEdit(this)"><b>{$smarty.const.APPLY}</b></i></span>*}
                        {/if}


                        {if $edit}
                            {$manager->render('ExtraCharge', ['product' => $product, 'manager' => $manager])}
                        {/if}



                        <span class="overwritten-multiply"></span>

                        {if $product['pack_unit'] || $product['packaging'] }
                            
                            <div class="qty-box plus_td">
                                    <span class="label_ed-or-pr" style="display: block;">{$smarty.const.UNIT_QTY}:</span>
                                    <span class="pr_minus"></span>
                                        {Html::textInput('product_info[][qty_][0]', $product['units'], ['class' => 'qty form-control new-product', 'data-type'=>'unit', 'data-max'=>'', 'data-min'=>'', 'data-step'=>'' ])}                                                
                                    <span class='pr_plus'></span>
                                        {Html::hiddenInput('product_info[][qty]', 1, ['class' => 'total_qty'])}
                                {if $product['pack_unit']}
                                    <span class="label_ed-or-pr">{$smarty.const.PACK_QTY}: ({$product['product_details']['product']['pack_unit']} items)</span>
                                    <div class="qty-box plus_td">
                                        <span class="pr_minus"></span>
                                            {Html::textInput('product_info[][qty_][1]', $product['packs'], ['class' => 'qty qty_pack form-control new-product', 'data-type'=>'pack_unit', 'data-max'=>'', 'data-min'=>'', 'data-step'=>''])}
                                        <span class='pr_plus'></span>                                        
                                    </div>
                                {/if}
                                {if $product['packaging'] > 0}
                                    <span class="label_ed-or-pr">{$smarty.const.CARTON_QTY}: (
                                    {if ($product['pack_unit']>0)}
                                        {$product['packaging'] * $product['pack_unit']}
                                    {else}
                                        {$product['packaging']}
                                    {/if} items)
                                    </span>
                                    <div class="qty-box plus_td">
                                        <span class="pr_minus"></span>
                                            {Html::textInput('product_info[][qty_][2]', $product['packagings'], ['class' => 'qty qty_packaging form-control new-product', 'data-type'=>'packaging', 'data-max'=>'', 'data-min'=>'', 'data-step'=>''])}                                            
                                        <span class='pr_plus'></span>
                                    </div>
                                {/if}
                                    <div class="ed-or-pr-stock"><span>{$smarty.const.TEXT_STOCK_QTY}</span><span class="valid1"></span><br><span class="valid"></span></div>
                            </div>
                        {else}
                                <div class="qty-box plus_td">
                                    <div class="qty-title">{$smarty.const.ENTRY_INVOICE_QTY}:</div>
                                    <span class="pr_minus"></span>
                                        {Html::textInput('product_info[][qty]', $product['quantity_virtual'], ['class' => 'qty form-control new-product', 'data-max'=>'', 'data-min'=>'', 'data-step'=>max((int)$product['order_quantity_step'], 1), 'data-value-real'=> $product['quantity']])}
                                    <span class='pr_plus'></span>
                                    <div class="ed-or-pr-stock"><span>{$smarty.const.TEXT_STOCK_QTY}</span><span class="valid1"></span><br><span class="valid"></span></div>
                                </div>
                        {/if}
                        
                        {if $product['pack_unit'] > 0 || $product['packaging'] > 0}
                            <span class="tl-ed-or-two-pr">
                                <table width="100%" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td><span class="total_summ_unit"></span></td>
                                    </tr>
                                    <tr>
                                        <td><span class="total_summ_tax_unit"></span></td>
                                    </tr>
                                    {if $product['pack_unit']}
                                    <tr>
                                        <td><span class="total_summ_pack"></span></td>
                                    </tr>
                                    <tr>
                                        <td><span class="total_summ_tax_pack"></span></td>
                                    </tr>
                                    {/if}
                                    {if $product['packaging'] > 0}
                                    <tr>
                                        <td><span class="total_summ_packaging"></span></td>
                                    </tr>
                                    <tr>
                                        <td><span class="total_summ_tax_packaging"></span></td>
                                    </tr>
                                    {/if}
                                </table>
                            </span>
                                <span class="tl-ed-or-two-pr">=</span>
                            <span class="tl-ed-or-two-pr">
                                <table width="100%" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td>{$smarty.const.TABLE_HEADING_QUANTITY}(<span class="total_qty"></span>)</td>
                                    </tr>
                                    <tr>
                                        <td><span class="total_summ"></span></td>
                                    </tr>
                                    <tr class="vat-aria">
                                        <td><span class="total_summ_tax"></span></td>
                                    </tr>
                                </table>
                            </span>
                        {else}
                            <span class="overwritten-is">=</span>
                            <span class="tl-ed-or-two-pr">
                                <table width="100%" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td><span class="total_summ"></span></td>
                                    </tr>
                                    <tr class="vat-aria">
                                        <td><span class="total_summ_tax"></span></td>
                                    </tr>
                                </table>
                            </span>
                        {/if}

                    </div>
                </div>
                {if $product['ga']}
                <div class="w-line-row">
                        <div class="edp-line or-ed-give">
                            <label>{$smarty.const.TEXT_GIVE_AWAY_ORDER}</label>
                            {if is_array($product['ga'])}
                                {foreach $product['ga'] as $ga}
                                    <div>{$ga['price_b']}</div>
                                {/foreach}
                            {else}
                            <div>{$product['ga']}</div>
                            {/if}
                        </div>
                </div>            
                {/if}
                <div class="w-line-row discount_table_view" style="display:none;">
                </div>
                <div class="attributes-parent w-line-row">
                    <div >
                      <div class="wl-td product-attributes">
                      </div>
                    </div>
                </div>
                <div class="w-line-row-2 w-line-row-22">
                    
                    {if $product['gift_wrap_allowed']}
                        <div>
                            <div class="edp-line">
                                <label>{$smarty.const.GIFT_WRAP_OPTION}:</label>
                                <div class="label_value gift_wrap">
                                    <div>
                                        <span class="gift_wrap_price">
                                        {$manager->render('GiftWrap', ['price' => $product['gift_wrap_price'], 'manager' => $manager])}
                                        </span>
                                        {Html::checkbox('product_info[][gift_wrap]', $product['gift_wrapped'], ['class' => 'check_on_off' ])}
                                    </div>
                                </div>	
                            </div>
                        </div>
                    {/if}
                        
                    <div style="float:right;">
                        <div class="edp-line edp-line-pd-left">
                            <label>{$smarty.const.TEXT_UNKNOWN_TAX_RATE}:</label>{$manager->render('Tax', ['manager' => $manager, 'product' => $product, 'tax_address' => $tax_address, 'tax_class_array' => $tax_class_array, 'onchange' => "changeTax(this)"])}
                        </div>   
                    </div>
                </div>
                <div class="configurator-row w-line-row" style="display:none;">
                    <div >
                        <div class="product-configurator">
                        </div>
                    </div>
                </div>
                <div class="bundles-row w-line-row" style="display:none;">
                    <div>
                      <div class="product-bundles">
                      </div>
                    </div>  
                </div>
           </div>
        </div>
        <div class="marea" style="display:none;"></div>
</div>    

<script>
    
    $(document).ready(function(){

        const $product = $(`.product-details[data-box="{$product['products_id']}"]`);

        $('.overwritten-choose').each(overwrittenChoose)
        $('.overwritten-choose').on('change', overwrittenChoose)

        function overwrittenChoose(){
            $(this).closest('.or-ed-ad-row-sum').find('.extra-disc-box').hide();
            $(this).closest('.or-ed-ad-row-sum').find('.overwritten-' + $(this).val()).show()
        }

        $('.prod_name', $product).click(function(e){
            if ((e.target.offsetWidth - e.offsetX) < e.target.offsetHeight){
                var box = $(this).closest('.product-details');
                loaded_products.forEach(function(e, i){
                    if ($product.data('box') == e.id){
                        loaded_products.splice(i, 1);
                    }
                })
                box.remove();
                $(window).trigger('changedProduct', [$product.data('box')])
            }
            if (!$('.product-details').length){
                _new = true;
                $('.product_holder').html('<div class="widget box"><div class="widget-content after">{$smarty.const.TEXT_PRODUCT_NOT_SELECTED|escape:"javascript"}</div></div>');
                $('.add-product .btn-save').hide();
                $('.add-product .btn-reset').hide();
            }
        });

        $product.on('change', 'select', () => $(window).trigger('changedProduct', [$product.data('box')]))

        /*$(".or-ed-ad-row-sum input.edit-price.form-control").on('change', function(){
            $(this).parents('.or-ed-ad-row-sum').find('i.icon-pencil').each(function(){
                $(this).trigger('click');
            });
        });*/
        
        $(".gift_wrap .check_on_off").bootstrapSwitch(
        {
            onText: "{$smarty.const.SW_ON}",
            offText: "{$smarty.const.SW_OFF}",
            handleWidth: '20px',
            labelWidth: '24px'
            }
        );


        
    })
    
    
    
    /*
        
    var started = true;
    if (product.multi_qty){
        product.multi_qty_data.unit = 1;
        {if $params['product_details']['product']['pack_unit'] > 0}
            product.multi_qty_data.pack_unit = parseInt({$params['product_details']['product']['pack_unit']});
        {/if}
        {if $params['product_details']['product']['packaging'] > 0}
            product.multi_qty_data.packaging = parseInt({$params['product_details']['product']['packaging']});
            if (product.multi_qty_data.hasOwnProperty('pack_unit')){
                product.multi_qty_data.packaging *= product.multi_qty_data.pack_unit;
            }
        {/if}
    }
    
    var sendParams;
    var sendParamsObj = function(){
       return {
            'products_id' : '{$params["products_id"]}',
            'currentCart': $('input[name=currentCart]').val(),
            {if $edit}
            'id': (started? $('.edit_product_popup input[name=uprid]').val():$('#product-attributes select, #product-bundles select').serialize() ),
            {else}
            'id': $('#product-attributes select, #product-bundles select').serialize(),
            {/if}
            'qty': product.getQty(),
            'action':'get_details'
        };
    }
    sendParams = sendParamsObj();
	
    
	$(document).ready(function(){
		update_attributes(document.cart_quantity);
        
        $('input[name=qty], input[data-type=unit], input[data-type=pack_unit], input[data-type=packaging]').focusout(function(){
            update_attributes(document.cart_quantity);
            //$('#total_summ').html(product.calucalteTotal());
        });
        
        $('.product-details input[name=qty], input[data-type=unit], input[data-type=pack_unit], input[data-type=packaging]').change(function(event){
            localStorage.lastType = '';
            if ($(event.target).is('input') && product.multi_qty){
                localStorage.lastType = $(event.target).attr('data-type');
            }
            update_attributes(document.cart_quantity);
           //$('#total_summ').html(product.calucalteTotal());
        });
        
        $('select[name=tax]').change(function(){
            if (product.multi_qty){
                product.multiTotal();
            } else {
                $('#total_summ').html(product.calucalteTotal(false));
                $('#total_summ_tax').html(product.calucalteTotal(true));
                $('input[name=final_price]').val(product.getPrice(false));
                $('input[name=final_price_tax]').val(product.getPrice(true));
                $('#final_price').html(product.getFormatted(product.getPrice(false)));
                $('#final_price_tax').html(product.getFormatted(product.getPrice(true)));
                product.renderDiscountTable();
            }
            $('.gift_wrap span.gift_wrap_price').html(product.getGiftWrapPrice());
        });
        
        $(".gift_wrap .check_on_off").bootstrapSwitch(
        {
            onText: "{$smarty.const.SW_ON}",
            offText: "{$smarty.const.SW_OFF}",
                    handleWidth: '20px',
                    labelWidth: '24px'
            }
        );
        
        $('#edit_price .icon-pencil, #edit_name .icon-pencil').click(function(){ //price edit
            var is_price = false;
            var is_name = false;
            if ($(this).parent().attr('id') == 'edit_price') is_price = true;
            if ($(this).parent().attr('id') == 'edit_name') is_name = true;
            var edit_element = $(this).data('element');
            var name = $('.'+edit_element).attr('name');
            if ($(this).parent().hasClass('btn')){
                $(this).parent().removeClass('btn');
				$('input[name='+name+']').attr('type','hidden');
                if (is_price){
                    if (product.newDetails.price != $('input[name='+name+']').val()){
                        product.price_manualy_modified = true;
                    }
                    product.setPrice($('input[name='+name+']').val());
                    
                    $('#'+name).html(product.getFormatted(product.getPrice(false)));
                    $('#'+name+'_tax').html(product.getFormatted(product.getPrice(true)));
                    $('#total_summ').html(product.calucalteTotal(false));
                    $('#total_summ_tax').html(product.calucalteTotal(true));
                    //$('.with_tax').css('display', 'inline');
                    $('.without_tax').css('display', 'none');
                    $('#'+name+'_tax').css('display', 'block');
                    $('input[name='+name+'_tax]').attr('type','hidden');
                }
                if (is_name){
                    product.newDetails.name = $('input[name='+name+']').val();
                    $('#'+name).html(product.newDetails.name);
                }
                $('#'+name).css('display', 'inline-block');
            } else {
                $(this).parent().addClass('btn');
                $('input[name='+name+']').attr('type','input');
                if (is_price){
                    $('input[name='+name+'_tax]').attr('type','input');
                    //$('.with_tax').css('display', 'inline');
                    $('.without_tax').css('display', 'inline');
                    $('#'+name+'_tax').css('display', 'none');
                }
                $('#'+name).css('display', 'none');
                
            }            
        });
        
        $('input[name=final_price]').keyup(function(){
            var p = parseFloat($(this).val())+product.getTax(parseFloat($(this).val()));
            $('input[name=final_price_tax]').val(p.toFixed(6));
            product.price_manualy_modified = true;
        });
        
        $('input[name=final_price_tax]').keyup(function(){
            var p = product.getunTaxed(parseFloat($(this).val()));
            $('input[name=final_price]').val(p.toFixed(6));
        });
        
        $('.product-details input[name=qty], input[data-type=unit], input[data-type=pack_unit], input[data-type=packaging]').keyup(function(){
            product.checkQuantity();
        })
        
        
        
        
        
        $('.btn-reset').click(function(){
            product.resetDetails();
            $('#total_summ').html(product.calucalteTotal(false));
            $('#total_summ_tax').html(product.calucalteTotal(true));
            $('input[name=final_price]').val(product.getPrice(false));
            $('input[name=final_price_tax]').val(product.getPrice(true));
            $('#final_price').html(product.getFormatted(product.getPrice(false)));
            $('#final_price_tax').html(product.getFormatted(product.getPrice(true)));
            $('#name').html(product.newDetails.name);
            $('.product_name').val(product.newDetails.name);
        })
        
        
        $('form[name=cart_quantity]').submit(function(){
            if (product.multi_qty){
                product.collectQty();
            }
            var defprice = document.createElement('input');
            defprice.setAttribute('type','checkbox');
            defprice.setAttribute('name','use_default_price');
            if (!product.price_manualy_modified){
                defprice.setAttribute('checked','checked');
                $('.product-details .marea').append(defprice);
            }
        })
        
        
	})
    */
</script>
</div>