{use class="backend\components\Currencies"}
{Currencies::widget(['currency'=>$params['currency']])}
<div class="product-details">        
     <div class="widget box box-no-shadow">
                  {if $params['is_editing']}
                  <input type="hidden" name="products_id" value="{$params['product']['products_id']}"/>
				  <input type="hidden" name="uprid" value="{$params['product']['id']}"/>
                  {else}
                  <div id="prod_name" class="popup-heading" title="{$params['product_name']}">{$params['product_name']|truncate:70:"...":true}</div>
                  <input type="hidden" name="products_id" value="{$params['products_id']}"/>
                  {/if}
					<div class="widget-content after">
                        <div class="tl-or-pr-edt-left">
                            {$params['image']}
                        </div>
                        <div class="tl-or-pr-edt-right">
                            {if $params['is_editing']}
                            <div class="w-line-row">
                                <div class="edp-line ed-pname">
                                    <label>{$smarty.const.TEXT_PRODUCT_NAME}:</label>
                                    <span id="name">{stripslashes($params['product']['name'])}</span>
                                    {tep_draw_hidden_field('name', stripslashes($params['product']['name']), 'class="product_name form-control"')}
                                    <span id="edit_name"><i class="icon-pencil" data-element="product_name"></i></span>
                                </div>
                             </div>
                            {/if}
                            <div class="w-line-row">
                                <div class="or-ed-ad-row-sum {if isset($params['product_details']) && ($params['product_details']['product']['pack_unit'] > 0 || $params['product_details']['product']['packaging'] > 0)}or-ed-ad-row-sum-big{/if}">
                                    {if isset($params['product_details']) && ($params['product_details']['product']['pack_unit'] > 0 || $params['product_details']['product']['packaging'] > 0)}
                                    <span class="with_tax label_ed-or-pr label_ed-or-pr-auto">                                        
                                        <table width="100%" cellspacing="0" cellpadding="0">                                      
                                            <tr>
                                                <td align="right">{$smarty.const.TABLE_HEADING_PRICE_EXCLUDING_TAX}: </td>
                                                <td><span id="final_price_unit"></span></td>
                                                <td rowspan="2" class="union_td"><span>}</span></td>
                                            </tr>
                                            <tr>
                                                <td align="right">{$smarty.const.TABLE_HEADING_PRICE_INCLUDING_TAX}: </td>
                                                <td><span id="final_price_tax_unit"></span></td>
                                            </tr>
                                            {if $params['product_details']['product']['pack_unit']}
                                            <tr>
                                                <td align="right">{$smarty.const.TABLE_HEADING_PRICE_EXCLUDING_TAX}: </td>
                                                <td><span id="final_price_pack_unit"></span></td>
                                                <td rowspan="2" class="union_td"><span>}</span></td>
                                            </tr>
                                            <tr>
                                                <td align="right">{$smarty.const.TABLE_HEADING_PRICE_INCLUDING_TAX}: </td>
                                                <td><span id="final_price_tax_pack_unit"></span></td>
                                            </tr>
                                            {/if}
                                            {if $params['product_details']['product']['packaging'] > 0}
                                            <tr>
                                                <td align="right">{$smarty.const.TABLE_HEADING_PRICE_EXCLUDING_TAX}: </td>
                                                <td><span id="final_price_packaging"></span></td>
                                                <td rowspan="2" class="union_td"><span>}</span></td>
                                            </tr>
                                            <tr>
                                                <td align="right">{$smarty.const.TABLE_HEADING_PRICE_INCLUDING_TAX}: </td>
                                                <td><span id="final_price_tax_packaging"></span></td>
                                            </tr>                                            
                                            {/if}
                                        </table>
                                    </span>
                                    {else}
                                    <span class="with_tax label_ed-or-pr">                                        
                                        <table width="100%" cellspacing="0" cellpadding="0">
                                            <tr id="old_price_tr">
                                                <td colspan=3><span id="old_price"></span></td>
                                            </tr>                                        
                                            <tr>
                                                <td align="right">{$smarty.const.TABLE_HEADING_PRICE_EXCLUDING_TAX}: </td>
                                                <td><span id="final_price"></span>{tep_draw_hidden_field('final_price', '0', 'class="edit-price form-control"')}</td>
                                                <td rowspan="2" class="union_td"><span>}</span></td>
                                            </tr>
                                            <tr>
                                                <td align="right">{$smarty.const.TABLE_HEADING_PRICE_INCLUDING_TAX}: </td>
                                                <td><span id="final_price_tax"></span>{tep_draw_hidden_field('final_price_tax', '0', 'class="edit-price form-control"')}</td>
                                            </tr>
                                        </table>
                                    </span>
                                    <span id="edit_price"><i class="icon-pencil" data-element="edit-price"></i></span>
                                    {/if}                                                                        
                                    <span class="or-ed-x">x</span>
                                    {if isset($params['product_details']) && ($params['product_details']['product']['pack_unit'] > 0 || $params['product_details']['product']['packaging'] > 0)}
                                        
                                            <div class="qty plus_td">
                                                <span class="label_ed-or-pr" style="display: block;">{$smarty.const.UNIT_QTY}:</span>
                                                <span class="pr_minus"></span>
                                                    <input type="text" name="qty[0]" value="{$params['product']['units']}" id="qty" data-type="unit"  data-max="" data-min="" data-step="" class="form-control new-product">
                                                <span class='pr_plus'></span>
                                                <input name="qty_[0]" value="1" type="hidden">
                                                {if $params['product_details']['product']['pack_unit']}
                                        <span class="label_ed-or-pr">{$smarty.const.PACK_QTY}: ({$params['product_details']['product']['pack_unit']} items)</span>
                                            <div class="qty plus_td">
                                                <span class="pr_minus"></span>
                                                    <input type="text" name="qty[1]" value="{$params['product']['packs']}" id="qty_pack" data-type="pack_unit"  data-max="" data-min="" data-step="" class="form-control new-product">
                                                <span class='pr_plus'></span>
                                                <input name="qty_[1]" value="{$params['product_details']['product']['pack_unit']}" type="hidden">
                                            </div>   
                                        {/if}
                                                {if $params['product_details']['product']['packaging'] > 0}
                                        <span class="label_ed-or-pr">{$smarty.const.CARTON_QTY}: ({if ($params['product_details']['product']['pack_unit']>0)}{$params['product_details']['product']['packaging'] * $params['product_details']['product']['pack_unit']}{else}{$params['product_details']['product']['packaging']}{/if} items)</span>
                                            <div class="qty plus_td">
                                                <span class="pr_minus"></span>
                                                    <input type="text" name="qty[2]" value="{$params['product']['packagings']}" id="qty_packaging" data-type="packaging"  data-max="" data-min="" data-step="" class="form-control new-product">
                                                <span class='pr_plus'></span> 
                                                <input name="qty_[2]" value="{$params['product_details']['product']['packaging']}" type="hidden">                                                
                                            </div>                                                
                                        {/if}
                                                <div class="ed-or-pr-stock"><span>{$smarty.const.TEXT_STOCK_QTY}</span><span class="valid1"></span><br><span class="valid"></span></div>
                                            </div>
                                        
                                        
                                        
                                    {else}
                                        <span class="label_ed-or-pr">{$smarty.const.ENTRY_INVOICE_QTY}:</span>
                                            <div class="qty plus_td">
                                                <span class="pr_minus"></span>
                                                    {tep_draw_input_field('qty', (int)$params['product']['qty'], ' id="qty" data-max="" data-min="" data-step="" class="form-control new-product"')}
                                                <span class='pr_plus'></span>
                                                <div class="ed-or-pr-stock"><span>{$smarty.const.TEXT_STOCK_QTY}</span><span class="valid1"></span><br><span class="valid"></span></div>
                                            </div>                                    
                                    {/if}
                                    <span>=</span>
                                    {if isset($params['product_details']) && ($params['product_details']['product']['pack_unit'] > 0 || $params['product_details']['product']['packaging'] > 0)}
                                        <span class="tl-ed-or-two-pr">
                                            <table width="100%" cellspacing="0" cellpadding="0">
                                                <tr>
                                                    <td><span id="total_summ_unit"></span></td>
                                                </tr>
                                                <tr>
                                                    <td><span id="total_summ_tax_unit"></span></td>
                                                </tr>
                                                {if $params['product_details']['product']['pack_unit']}
                                                <tr>
                                                    <td><span id="total_summ_pack"></span></td>
                                                </tr>
                                                <tr>
                                                    <td><span id="total_summ_tax_pack"></span></td>
                                                </tr>
                                                {/if}
                                                {if $params['product_details']['product']['packaging'] > 0}
                                                <tr>
                                                    <td><span id="total_summ_packaging"></span></td>
                                                </tr>
                                                <tr>
                                                    <td><span id="total_summ_tax_packaging"></span></td>
                                                </tr>
                                                {/if}
                                            </table>                                            
                                        </span>
                                            <span class="tl-ed-or-two-pr">=</span>
                                        <span class="tl-ed-or-two-pr">
                                            <table width="100%" cellspacing="0" cellpadding="0">
                                                <tr>
                                                    <td>{$smarty.const.TABLE_HEADING_QUANTITY}(<span id="total_qty"></span>)</td>
                                                </tr>
                                                <tr>
                                                    <td><span id="total_summ"></span></td>
                                                </tr>
                                                <tr>
                                                    <td><span id="total_summ_tax"></span></td>
                                                </tr>
                                            </table>
                                        </span>
                                    {else}
                                        <span class="tl-ed-or-two-pr">
                                            <table width="100%" cellspacing="0" cellpadding="0">
                                                <tr>
                                                    <td><span id="total_summ"></span></td>
                                                </tr>
                                                <tr>
                                                    <td><span id="total_summ_tax"></span></td>
                                                </tr>
                                            </table>
                                        </span>
                                    {/if}    
                                </div>
                            </div>
                            {if $params['ga'] neq ''}
                            <div class="w-line-row">
                                    <div class="edp-line or-ed-give">
                                        <label>{$smarty.const.TEXT_GIVE_AWAY_ORDER}</label>
                                        {if is_array($params['ga'])}
                                            {foreach $params['ga'] as $ga}
                                                <div>{$ga['price_b']}</div>
                                            {/foreach}
                                        {else}
                                        <div>{$params['ga']}</div>
                                        {/if}
                                    </div>
                            </div>            
                            {/if}
                            <div class="w-line-row discount_table_view" style="display:none;">
                            </div>
                            <div class="attributes-parent w-line-row">
                                <div >
                                    <div class="wl-td product-attributes"  id="product-attributes">
                                    </div>
                                </div>
                            </div>
                            <div class="bundles-row w-line-row" style="display:none;">
                                <div >
                                  <div id="product-bundles">
                                  </div>							
                                </div>
                            </div>
                            <div class="configurator-row w-line-row" style="display:none;">
                                <div >
                                  <div id="product-configurator">
                                  </div>
                                </div>
                            </div>
                            <div class="w-line-row-2 w-line-row-22">
                                
                                {if $params['gift_wrap_allowed']}
                                    <div>
                                        <div class="edp-line">
                                            <label>{$smarty.const.GIFT_WRAP_OPTION}:</label>
                                            <div class="label_value gift_wrap">
                                                <div><span class="gift_wrap_price">+{$params['gift_wrap_price']}</span>
                                                    <input type="checkbox" name="gift_wrap" class="check_on_off" {if $params['product']['gift_wrapped']} checked{/if}/></div>
                                            </div>	
                                        </div>
                                    </div>
                                {/if}
                                    
                                <div>
                                     <div class="edp-line">
                                        <label>{$smarty.const.TABLE_HEADING_TAX}:</label>
                                        <div class="label_value">
                                            {assign var="zone" value="{\common\helpers\Tax::get_zone_id($params['tax_class_id'])}"}
                                            {assign var="zone_id" value="{$params['tax_class_id']}_{$zone}"}
                                            {if $zone eq '' || $params['is_editing']}
                                                {assign var="tax_selected" value="{$cart->getOwerwrittenKey($params['product']['id'], 'tax_selected')}"}
                                                {if $tax_selected neq ''}
                                                    {$zone_id = $tax_selected}
                                                {/if}
                                            {/if}						
                                            {tep_draw_pull_down_menu("tax", $tax_class_array,  $zone_id , "class='form-control tax'")}
                                        </div>			
                                    </div>   
                                </div>
                            </div>
                       </div>
					</div>
                    <div class="marea" style="display:none;"></div>
</div>    
</div>
<script>
    var product = {
        total:0,
        getTax: function(price){
            var tax = 0;
            if (this.rates.hasOwnProperty(this.getSelectedRate())){
                tax = (price * this.rates[this.getSelectedRate()] / 100);
            }
            return tax;
        },
        getunTaxed: function(price){
            var value = price;
            if (this.rates.hasOwnProperty(this.getSelectedRate())){
                value = (price / (100 + parseFloat(this.rates[this.getSelectedRate()])) * 100);
            }
            return value;
        },
        calucalteTotal: function(with_tax){            
            var subtotal = this.getSubtotal();
            var total = 0;
            if (with_tax){
                total = parseFloat(subtotal) + this.getTax(subtotal);
            } else {
                total = parseFloat(subtotal);
            }
            
            return this.getFormatted(total);
        },
        getSubtotal: function(){
            return price = this.getPrice(false) * this.getQty();
        },
        showPrice:function(price, with_tax){
            if (with_tax)
                price = parseFloat(price) + this.getTax(price);
            return this.getFormatted(price);
        },
        getPrice:function(with_tax){
            var price = this.newDetails.price;
            if (with_tax)
                price = parseFloat(price) + this.getTax(price);
            return parseFloat(price).toFixed(6);
        },
        getSelectedRate:function(){
            return $('select[name=tax]').val();
        },
        getFormatted:function(value){
            if (typeof accounting == 'object'){
                return accounting.formatMoney(value, curr_hex[currency_id].symbol_left,curr_hex[currency_id].decimal_places,curr_hex[currency_id].thousands_point,curr_hex[currency_id].decimal_point);
            } 
            return value;
        },
        setPrice:function(price){
            this.newDetails.price = price;
            return;
            var tax = 0;
            if (this.rates.hasOwnProperty(this.getSelectedRate())){
                tax = parseFloat(this.rates[this.getSelectedRate()]);
            }
            this.newDetails.price = (price * 100 / (100 + tax));
        },
        resetDetails: function(){
            this.newDetails.price = this.oldDetails.price;
            this.newDetails.name = this.oldDetails.name;
            if (this.oldDetails.attributes.length > 0){
                $.each(this.oldDetails.attributes, function(i, e){
                    Object.keys(e).map(function(key){
                        $('.edit_product_popup select[name="id['+key+']"]').val(e[key]);
                    })
                })
            }
            $('.edit_product_popup select[name=tax]').val(this.oldDetails.selected_rate);
            //update_attributes(document.cart_quantity);
        },
        rates: [],
        gift_wrap_price: 0,
        getGiftWrapPrice:function(){
            return this.getFormatted(parseFloat(this.gift_wrap_price) + this.getTax(this.gift_wrap_price));
        },
        discount_table:[],
        overloadDiscountTable:function(data){
            if (Array.isArray(data)){
                this.discount_table = [];                
                $.each(data, function(i,e){
                   product.discount_table.push({ 'count':e.count, 'price':e.price });
                });
            }
        },
        renderDiscountTable:function(){
            if (this.discount_table.length > 0){
                var till = parseInt(this.discount_table[0].count) - 1;
                if (till > 2){
                    $('.quantity-discounts-content .item[data-id=0] .count').html('1-' + till);
                    $('.quantity-discounts-content .item[data-id=0]').attr("data-min",1).attr("data-max",till);
                } else {
                    $('.quantity-discounts-content .item[data-id=0] .count').html('1');
                    $('.quantity-discounts-content .item[data-id=0]').attr("data-min",1).attr("data-max",1);
                }                
                $('.quantity-discounts-content .item[data-id=0] .price').html(product.getFormatted(product.getPrice(true)));
                var start_count = parseInt(this.discount_table[0].count);
                var item = 1;
                var limit = '';
                var _qty = this.getQty();
                $.each(this.discount_table, function(i, e){
                    if (product.discount_table.length > i+1){
                        till = parseInt(product.discount_table[i+1].count) - 1;
                    } else{
                        till = '';
                    }
                    if (till != ''){
                        limit =  '-' + till;
                    } else {
                        limit = '+';
                    }
                    $('.quantity-discounts-content .item[data-id='+item+'] .count').html(start_count + limit);
                    $('.quantity-discounts-content .item[data-id='+item+']').attr("data-min",start_count).attr("data-max",(till>0?till:99999));
                    start_count = parseInt(till)+1;
                    $('.quantity-discounts-content .item[data-id='+item+'] .price').html(product.getFormatted(product.getTax(e.price) + parseFloat(e.price)));
                    item++;
                });
                
                $.each($('.quantity-discounts-content .item'), function(i, e){
                    if(_qty >= parseInt($(e).attr('data-min')) && _qty <= parseInt($(e).attr('data-max'))){
                        $('.quantity-discounts-content .item').removeClass('selected');
                        $(e).addClass('selected');                    
                    }
                });
                
            }
        },
        stockInfo:{
            min:1,
            max:9999,
            step:1,
        },
        multi_qty: {if isset($params['product_details']) && ($params['product_details']['product']['pack_unit'] > 0 || $params['product_details']['product']['packaging'] > 0)}true{else}false{/if}, 
        multi_qty_data:{},
        multiTotal: function(){
            if (!this.multi_qty) return;
            var total = 0;
            var total_qty = 0;
            var value = this.newDetails.multiprice.unit * parseInt($('input[data-type=unit]').val());  
            total_qty += this.multi_qty_data.unit * parseInt($('input[data-type=unit]').val());
            $('#total_summ_unit').html(this.showPrice(value, false));
            $('#total_summ_tax_unit').html(this.showPrice(value, true));
            total += value;
            if (this.newDetails.multiprice.hasOwnProperty('pack_unit')){
                value = this.newDetails.multiprice.pack_unit * parseInt($('input[data-type=pack_unit]').val());
                total_qty += this.multi_qty_data.pack_unit * parseInt($('input[data-type=pack_unit]').val());
                $('#total_summ_pack').html(this.showPrice(value, false));
                $('#total_summ_tax_pack').html(this.showPrice(value, true));    
                total += value;
            }
            if (this.newDetails.multiprice.hasOwnProperty('packaging')){
                value = this.newDetails.multiprice.packaging * parseInt($('input[data-type=packaging]').val());
                total_qty += this.multi_qty_data.packaging * parseInt($('input[data-type=packaging]').val());
                $('#total_summ_packaging').html(this.showPrice(value, false));
                $('#total_summ_tax_packaging').html(this.showPrice(value, true));
                total += value;
            }
            $('#total_summ').html(this.showPrice(total, false));
            $('#total_summ_tax').html(this.showPrice(total, true));
            $('#total_qty').html(total_qty);
            return;
        },
        collectQty: function(){
            if (this.multi_qty){
                var v = 0;
                if (parseInt($('input[data-type=unit]').val()) > 0){
                    v = parseInt($('input[data-type=unit]').val());
                    $('input[data-type=unit]').val(this.multi_qty_data.unit * v);
                }
                v = 0;
                if (parseInt($('input[data-type=pack_unit]').val()) > 0){                    
                    v = parseInt($('input[data-type=pack_unit]').val());
                    $('input[data-type=pack_unit]').val(this.multi_qty_data.pack_unit * v)
                }
                v = 0;
                if (parseInt($('input[data-type=packaging]').val()) > 0){
                    v = parseInt($('input[data-type=packaging]').val());
                    $('input[data-type=packaging]').val(this.multi_qty_data.packaging * v)
                }
                return false;
            }
        },
        getQty: function(){
            if (this.multi_qty){
                var summ = 0;
                $.each(this.multi_qty_data, function(i, e){
                    if($('input[data-type='+i+']').length){
                        if ($('input[data-type='+i+']').val() == '') $('input[data-type='+i+']').val(1);
                        summ += parseInt($('input[data-type='+i+']').val()) * e;
                    }
                });
                return summ;
            } else {
                return $('input[name=qty]').val();
            }
        },
        checkQuantity: function(){
            if (this.multi_qty){
                var correct = true;
                var summ = 0;
                $.each(this.multi_qty_data, function(i, e){
                    if($('input[data-type='+i+']').length){
                        if ($('input[data-type='+i+']').val() == '') $('input[data-type='+i+']').val(0);
                        summ += parseInt($('input[data-type='+i+']').val()) * e;
                    }
                });
                if (summ > this.stockInfo.max){
                    summ = this.stockInfo.max;
                    if (this.multi_qty_data.hasOwnProperty('packaging') && this.multi_qty_data.packaging > 0){
                        $('input[data-type=packaging]').val($('input[data-type=packaging]').attr('data-max'));
                        summ -= $('input[data-type=packaging]').val() * this.multi_qty_data.packaging;
                    }
                    if (this.multi_qty_data.hasOwnProperty('pack_unit') && this.multi_qty_data.pack_unit > 0){
                        if (summ < this.multi_qty_data.pack_unit){
                            $('input[data-type=pack_unit]').val(0);
                        } else {
                            $('input[data-type=pack_unit]').val(Math.floor(summ/this.multi_qty_data.pack_unit));
                        }                        
                        summ -= $('input[data-type=pack_unit]').val() * this.multi_qty_data.pack_unit;
                    }
                    if (this.multi_qty_data.hasOwnProperty('unit') && this.multi_qty_data.unit > 0){
                        if (summ < 0) summ = 0;
                        $('input[data-type=unit]').val(summ);                        
                    }
                }                
            } else {
                var qty = parseInt(this.getQty());
                var break_loop = false;
                var correct = false;
                var value;
                var $i = 0;
                if (!Number.isInteger(qty)) {
                    qty = this.stockInfo.min;
                    break_loop = true;
                }
                do{
                    value = parseInt(this.stockInfo.min)+parseInt(this.stockInfo.step)*$i;
                    if (qty == value){
                        break_loop  = true;
                        correct = true;
                    } else if (qty < value){
                        break_loop  = true;
                        correct = false;
                    }
                    $i++;
                }while(!break_loop);
                if (value>this.stockInfo.max){
                    value = this.stockInfo.max;
                    correct = false;
                }
                if (!correct){
                    this.setQty(value);
                }
            }
        },
        setQty: function(qty){
            $('input[name=qty]').val(qty);
            return;
        },
        oldDetails: {
            price :0,
            name: '',
            attributes:[],
            selected_rate:0,
            multiprice:{}
        },
        newDetails: {
            price :0,
            name: '',
            attributes:[],
            selected_rate:0,
            multiprice:{}
        },
        price_manualy_modified:{if isset($params['product']['price_manualy_modified'])}{$params['product']['price_manualy_modified']}{else}false{/if},
        bundle:[],
        renderBundles:function(){
            //$('.bundles-row').show();
        }
    };
    
    {foreach $params['rates'] as $key => $rate}
        product.rates['{$key}'] = '{$rate}';
    {/foreach}
    
    {if $params['gift_wrap_price']}
    product.gift_wrap_price = {$params['gift_wrap_price']};
    $('.gift_wrap span.gift_wrap_price').html(product.getGiftWrapPrice());
    {/if}
    
    {if $params['is_editing']}
    product.newDetails.name = '{$params["product"]["name"]}';
    product.oldDetails.name = '{$params["product"]["old_name"]}';
    
        {if is_array($params["product"]['attributes']) && count($params["product"]['attributes']) > 0}
            {foreach $params["product"]['attributes'] as $key => $value}
                product.oldDetails.attributes.push({ '{$key}':{$value} });
            {/foreach}
        {/if}
    product.oldDetails.selected_rate = '{$params["product"]["selected_rate"]}';
    {/if}
    
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
                    'details': 1,
                    {if $params['is_editing']}
                    'id': (started? $('.edit_product_popup input[name=uprid]').val():$('#product-attributes select, #product-bundles select').serialize() ),
                    {else}
                    'id': $('#product-attributes select, #product-bundles select').serialize(),
                    {/if}				
                    'qty': product.getQty(),
                    'orders_id': "{$params['oID']}"    
        };
    }
    sendParams = sendParamsObj();
    
	function update_attributes(form){
            sendParams = sendParamsObj();
            if (product.multi_qty){
                if (localStorage.hasOwnProperty('lastType')){
                    sendParams.type = localStorage.lastType;
                    sendParams.qty_ = new Array();
                    sendParams.qty_[0] = $('input[name="qty[0]"]').val();
                    sendParams.qty_[1] = $('input[name="qty[1]"]').val();
                    sendParams.qty_[2] = $('input[name="qty[2]"]').val();
                    sendParams.qty = $('input[data-type='+sendParams.type+']').val()
                }
            }
        
			$.post('orders/addproduct?products_id={$params["products_id"]}&details=1&orders_id={$params["oID"]}',/*{
				'products_id' : '{$params["products_id"]}',
				'details': 1,
                {if $params['is_editing']}
                'id': (started? $('.edit_product_popup input[name=uprid]').val():$('#product-attributes select, #product-bundles select').serialize() ),
                {else}
				'id': $('#product-attributes select, #product-bundles select').serialize(),
                {/if}
				
				'qty': product.getQty(),
				'orders_id': "{$params['oID']}"
			}*/
            sendParams
            , function(data){
            
                var multi_qty = product.multi_qty;
                
                if (data.hasOwnProperty('attributes_array') || data.hasOwnProperty('inventory_array')){
                    if (data.product_attributes.length > 0){
                        $('#product-attributes').replaceWith(data.product_attributes);
                    } else {
                        $('.attributes-parent').hide();
                    }
                }
				
                
                $('#product-attributes select').addClass('form-control');

				$('.valid1').html(data.product_qty);
                
                product.stockInfo.max = data.product_qty;
                $('#qty').attr('data-max', data.product_qty);
                
                {if isset($params['product_details']) && ($params['product_details']['product']['pack_unit'] > 0 || $params['product_details']['product']['packaging'] > 0)}
                
                    if (multi_qty){
                        $('#qty_pack').attr('data-max',Math.floor(data.product_qty/parseInt(data.product_details.product.pack_unit))).attr('data-min',0);
                        $('#qty_packaging').attr('data-max',Math.floor(data.product_qty/parseInt(data.product_details.product.packaging))).attr('data-min',0);
                        product.multi_qty_data.unit = 1;
                        if (data.product_details.product.pack_unit > 0)
                            product.multi_qty_data.pack_unit = parseInt(data.product_details.product.pack_unit);
                        if (data.product_details.product.packaging > 0){
                            product.multi_qty_data.packaging = parseInt(data.product_details.product.packaging);
                            if (product.multi_qty_data.hasOwnProperty('pack_unit')){
                                product.multi_qty_data.packaging *= product.multi_qty_data.pack_unit;
                            }
                        }
                    }
                    
                {/if}
                
                if (data.hasOwnProperty('stock_indicator')){
                    $('.valid').html('('+data.stock_indicator.stock_indicator_text+')');
                     product.stockInfo.max = data.stock_indicator.quantity_max;
                     $('#qty').attr('data-max',data.stock_indicator.quantity_max);
                     if (multi_qty){
                        $('#qty_pack').attr('data-max',Math.floor(data.stock_indicator.quantity_max/parseInt(data.product_details.product.pack_unit)));
                        $('#qty_packaging').attr('data-max',Math.floor(data.stock_indicator.quantity_max/parseInt(data.product_details.product.packaging*data.product_details.product.pack_unit)));
                     }
                }
				
                if (data.hasOwnProperty('order_quantity')){
                    if (data.order_quantity.hasOwnProperty('order_quantity_step') && data.order_quantity.order_quantity_step > 0){
                        product.stockInfo.step = data.order_quantity.order_quantity_step;
                        $('.product-details input[name=qty]').attr('data-step', data.order_quantity.order_quantity_step);
                        if (multi_qty){
                            $('#qty').attr('data-step', data.order_quantity.order_quantity_step);
                            $('#qty_pack').attr('data-step', 1);
                            $('#qty_packaging').attr('data-step', 1);
                        }
                    } else {
                        $('.product-details input[name=qty]').attr('data-step', 1);
                        if (multi_qty){
                            $('#qty_pack').attr('data-step', 1);
                            $('#qty_packaging').attr('data-step', 1);
                        }                        
                    }
                    if (data.order_quantity.hasOwnProperty('order_quantity_minimal') && data.order_quantity.order_quantity_minimal > 0){
                        product.stockInfo.min = data.order_quantity.order_quantity_minimal;
                        $('.product-details input[name=qty]').attr('data-min', data.order_quantity.order_quantity_minimal);
                        if ($('#qty').val().length == 0 ){
                            $('#qty').val(data.order_quantity.order_quantity_minimal);
                        }
                        if (parseInt($('#qty').val()) < parseInt(data.order_quantity.order_quantity_minimal) && !multi_qty)
                            $('#qty').val(data.order_quantity.order_quantity_minimal);
                        
                        if (multi_qty){
                            $('#qty').attr('data-min',0);
                        }
                        product.checkQuantity();
                    } else {
                        if (multi_qty){
                            $('#qty').attr('data-min',0);
                            $('#qty_pack').attr('data-min',0);
                            $('#qty_packaging').attr('data-min',0);
                        }else{
                            $('.product-details input[name=qty]').attr('data-min', 1);
                            $('#qty').val(1);
                        }
                    }                    
                }
                {if $params['is_editing']}
                    //product.newDetails.price = '{$params["product"]["final_price"]}';
                    product.oldDetails.price = '{$params["product"]["final_price"]}';
                    
                    if (data.special_unit_price > 0 ){
                        //data.special_unit_price;
                        if (started || product.price_manualy_modified){
                            product.newDetails.price = '{$params["product"]["final_price"]}';
                        } else {
                            product.newDetails.price = data.special_unit_price;
                        }
                        
                        $('.old_price').html(data.product_price);                    
                        $('input[name=final_price]').val(product.getPrice(false));
                        $('input[name=final_price_tax]').val(product.getPrice(true))
                        $('#final_price').html(product.getFormatted(product.getPrice(false)));
                        $('#final_price_tax').html(product.getFormatted(product.getPrice(true)));
                    } else {
                        //product.oldDetails.price = '{$params["product"]["final_price"]}';//data.product_unit_price;
                        if (started || product.price_manualy_modified){
                            product.newDetails.price = '{$params["product"]["final_price"]}';
                        } else {
                            product.newDetails.price = data.product_unit_price;
                        }
                        
                        
                        $('input[name=final_price]').val(product.getPrice(false));
                        $('input[name=final_price_tax]').val(product.getPrice(true))
                        $('#final_price').html(product.getFormatted(product.getPrice(false)));
                        $('#final_price_tax').html(product.getFormatted(product.getPrice(true)));
                    }
                {else}
                
                    if (product.newDetails.price == product.oldDetails.price){
                        if (data.special_unit_price > 0 ){
                            product.oldDetails.price = data.special_unit_price;
                            product.newDetails.price = data.special_unit_price;
                            $('.old_price').html(data.product_price);                    
                            $('input[name=final_price]').val(product.getPrice(false));
                            $('input[name=final_price_tax]').val(product.getPrice(true))
                            $('#final_price').html(product.getFormatted(product.getPrice(false)));
                            $('#final_price_tax').html(product.getFormatted(product.getPrice(true)));
                        } else {
                            product.oldDetails.price = data.product_unit_price;
                            product.newDetails.price = data.product_unit_price;                    
                            $('input[name=final_price]').val(product.getPrice(false));
                            $('input[name=final_price_tax]').val(product.getPrice(true))
                            $('#final_price').html(product.getFormatted(product.getPrice(false)));
                            $('#final_price_tax').html(product.getFormatted(product.getPrice(true)));
                        }
                    }
                
                {/if}
   
                if (product.multi_qty && data.hasOwnProperty('product_details')){
                    if (data.product_details.hasOwnProperty('single_price')){
                        product.newDetails.multiprice.unit = data.product_details.single_price.unit_base;
                        $('#final_price_unit').html(product.showPrice(data.product_details.single_price.unit_base, false));
                        $('#final_price_tax_unit').html(product.showPrice(data.product_details.single_price.unit_base, true));
                        product.newDetails.multiprice.pack_unit = data.product_details.single_price.pack_base;
                        $('#final_price_pack_unit').html(product.showPrice(data.product_details.single_price.pack_base, false));
                        $('#final_price_tax_pack_unit').html(product.showPrice(data.product_details.single_price.pack_base, true));
                        product.newDetails.multiprice.packaging = data.product_details.single_price.package_base;
                        $('#final_price_packaging').html(product.showPrice(data.product_details.single_price.package_base, false));
                        $('#final_price_tax_packaging').html(product.showPrice(data.product_details.single_price.package_base, true));                        
                    }
                    if (data.product_details.hasOwnProperty('single_price_data')){
                        $('#final_price_'+data.product_details.single_price_data.current_type).html(product.showPrice(data.product_details.single_price_data.single_price_base, false));
                        $('#final_price_tax_'+data.product_details.single_price_data.current_type).html(product.showPrice(data.product_details.single_price_data.single_price_base, true));
                        product.newDetails.multiprice[data.product_details.single_price_data.current_type] = data.product_details.single_price_data.single_price_base;
                    }
                    product.multiTotal();
                }
                
                
                if (data.hasOwnProperty('bundles')){
                    $('#product-bundles').html(data.bundles_block)
                    $('.bundles-row').show();
                }
                
                if (data.hasOwnProperty('pctemplates_id')){
                    $('#product-configurator').html(data.configurator_block)
                    $('.configurator-row').show();
                }
                
                if(data.hasOwnProperty('discount_table_view') && data.discount_table_view.length>0){
                    $('.discount_table_view').html(data.discount_table_view).show();
                }
                if (data.hasOwnProperty('discount_table_data') && data.discount_table_data.length>0){
                    product.overloadDiscountTable(data.discount_table_data);
                    product.renderDiscountTable();
                }

				if ($('.popup-box-wrap').height()>screen.height/2){
					//$('.popup-box-wrap').css('top', (screen.height-$('.popup-box-wrap').height())/2 + $(window).scrollTop());
				}
                if (!product.multi_qty){
                    $('#total_summ').html(product.calucalteTotal(false));
                    $('#total_summ_tax').html(product.calucalteTotal(true));
                }
				localStorage.orderChanged = true;
                $('.add-product .btn-save').show();
                $('.add-product .btn-reset').show();
                started = false;
			}, 'json');		
	}
    
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
        
        $('#prod_name').click(function(e){
            if ((e.target.offsetWidth - e.offsetX) < e.target.offsetHeight){
                $('.product-details').html('{$smarty.const.TEXT_PRODUCT_NOT_SELECTED}');
                $('.add-product .btn-save').hide();
                $('.add-product .btn-reset').hide();
            }
        });
        
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
</script>