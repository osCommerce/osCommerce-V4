function getProduct(url, products_id, is_pack, is_modified, holder){
        var product = {
            total:0,
            uprid: products_id,
            products_id : parseInt(products_id),
            getTax: function(price, nearestTax){
                var tax = 0;
                if (this.rates.hasOwnProperty(this.getSelectedRate(nearestTax))){
                    tax = (price * this.rates[this.getSelectedRate(nearestTax)] / 100);
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
            calucalteTotal: function(with_tax, unFormatted){
                var subtotal = this.getSubtotal();
                var total = 0;
                if (with_tax){
                    total = parseFloat(subtotal) + this.getTax(subtotal);
                } else {
                    total = parseFloat(subtotal);
                }
                if (unFormatted) return total;
                return this.getFormatted(total);
            },
            changeTax: function(){
               if (!this.multi_qty){
                    $('input[name="product_info[][final_price_tax]"]', holder).val(this.getPrice(true));
                    $('.final_price_tax', holder).html(this.getFormatted(this.getPrice(true)));
                    $('.total_summ', holder).html(this.calucalteTotal(false));
                    $('.total_summ_tax', holder).html(this.calucalteTotal(true));
                    $('.gift_wrap span.gift_wrap_price', holder).html(this.getGiftWrapPrice());
                } else {
                    this.multiTotal();
                }
            },
            changeConfTax: function(obj){
                let $parent =  $(obj, holder).parents('.pc_row');
                let price = $($parent).find('.element_total_summ', holder).data('price');
                $($parent).find('.element_total_summ_tax', holder).html(this.showPrice(price * $($parent).find('.qty').val(), true, obj));
            },
            showConfTotal:function(){
                let $total = 0;
                product = this;
                $('.element_total_summ').each(function(i, e){
                    let price = $(e).data('price'), qty = $(e).parents('.pc_row').find('.qty').val(), tax = product.getTax(price * qty, $(e).parents('.pc_row').find('select.tax'));
                    $total = (parseFloat(price) * qty + tax ) + $total;
                })
                $total = product.calucalteTotal(true, true) + $total;
                $('.product-price-configurator', holder).html(product.getFormatted($total));
            },
            getSubtotal: function(){
                return price = this.getPrice(false) * this.getQty(true);
            },
            showPrice:function(price, with_tax, nearestTax){
                if (with_tax){
                    price = parseFloat(price) + this.getTax(price, nearestTax);
                }
                return this.getFormatted(price);
            },
            getPrice:function(with_tax){
                var price = this.newDetails.price;
                if (with_tax)
                    price = parseFloat(price) + this.getTax(price);
                return parseFloat(price).toFixed(6);
            },
            getSelectedRate:function(nearestTax){
                if (nearestTax){
                    return $(nearestTax).val();
                } else {
                    return $('select[name="product_info[][tax]['+this.uprid+']"]', holder).val();
                }
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
                            $('.edit_product_popup select[name="product_info[][id]['+key+']"]').val(e[key]);
                        })
                    })
                }
                if (this.edit){
                    $('.name', holder).html(product.newDetails.name);
                    $('.product_name', holder).val(product.newDetails.name);
                }
                this.price_manualy_modified = false;
                this.edit = false;
                $('.edit_product_popup select[name="product_info[][tax]['+this.uprid+']"]').val(this.oldDetails.selected_rate);
                $('.total_summ', holder).html(product.calucalteTotal(false));
                $('.total_summ_tax', holder).html(product.calucalteTotal(true));
                $('input[data-reg="final_price"]', holder).val(product.getPrice(false));
                $('input[data-reg="final_price_tax"]', holder).val(product.getPrice(true));
                $('.final_price', holder).html(product.getFormatted(product.getPrice(false)));
                $('.final_price_tax', holder).html(product.getFormatted(product.getPrice(true)));

            },
            rates: getOrderRates(),
            gift_wrap_price: 0,
            getGiftWrapPrice:function(){
                return this.getFormatted(parseFloat(this.gift_wrap_price) + this.getTax(this.gift_wrap_price));
            },
            initDetails: function(product){
                if (product.hasOwnProperty('gift_wrap_price')){
                    if (product.gift_wrap_price ){
                        this.gift_wrap_price = product.gift_wrap_price;
                        $('.gift_wrap span.gift_wrap_price', holder).html(this.getGiftWrapPrice());
                    }
                }
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
                        $('.quantity-discounts-content .item[data-id=0] .count', holder).html('1-' + till);
                        $('.quantity-discounts-content .item[data-id=0]', holder).attr("data-min",1).attr("data-max",till);
                    } else {
                        $('.quantity-discounts-content .item[data-id=0] .count', holder).html('1');
                        $('.quantity-discounts-content .item[data-id=0]', holder).attr("data-min",1).attr("data-max",1);
                    }
                    $('.quantity-discounts-content .item[data-id=0] .price', holder).html(product.getFormatted(product.getPrice(true)));
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
                        $('.quantity-discounts-content .item[data-id='+item+'] .count', holder).html(start_count + limit);
                        $('.quantity-discounts-content .item[data-id='+item+']', holder).attr("data-min",start_count).attr("data-max",(till>0?till:99999));
                        start_count = parseInt(till)+1;
                        $('.quantity-discounts-content .item[data-id='+item+'] .price', holder).html(product.getFormatted(product.getTax(e.price) + parseFloat(e.price)));
                        item++;
                    });

                    $.each($('.quantity-discounts-content .item', holder), function(i, e){
                        if(_qty >= parseInt($(e).attr('data-min')) && _qty <= parseInt($(e).attr('data-max'))){
                            $('.quantity-discounts-content .item', holder).removeClass('selected');
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
            is_conf: false,
            multi_qty: is_pack,
            multi_qty_data:{},
            multiTotal: function(){
                if (!this.multi_qty) return;
                var total = 0;
                var total_qty = 0;
                var value = this.newDetails.multiprice.unit * parseInt($('input[data-type=unit]', holder).val());
                total_qty += this.multi_qty_data.unit * parseInt($('input[data-type=unit]', holder).val());
                $('.total_summ_unit', holder).html(this.showPrice(value, false));
                $('.total_summ_tax_unit', holder).html(this.showPrice(value, true));
                total += value;
                if (this.newDetails.multiprice.hasOwnProperty('pack_unit')){
                    value = this.newDetails.multiprice.pack_unit * parseInt($('input[data-type=pack_unit]', holder).val());
                    total_qty += this.multi_qty_data.pack_unit * parseInt($('input[data-type=pack_unit]', holder).val());
                    $('.total_summ_pack', holder).html(this.showPrice(value, false));
                    $('.total_summ_tax_pack', holder).html(this.showPrice(value, true));
                    total += value;
                }
                if (this.newDetails.multiprice.hasOwnProperty('packaging')){
                    value = this.newDetails.multiprice.packaging * parseInt($('input[data-type=packaging]', holder).val());
                    total_qty += this.multi_qty_data.packaging * parseInt($('input[data-type=packaging]', holder).val());
                    $('.total_summ_packaging', holder).html(this.showPrice(value, false));
                    $('.total_summ_tax_packaging', holder).html(this.showPrice(value, true));
                    total += value;
                }
                $('.total_summ', holder).html(this.showPrice(total, false));
                $('.total_summ_tax', holder).html(this.showPrice(total, true));
                $('span.total_qty', holder).html(total_qty);
                $('input.total_qty', holder).val(total_qty);
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
            getQty: function(isVirtual){
                if (this.multi_qty){
                    var summ = 0;
                    $.each(this.multi_qty_data, function(i, e){
                        if($('input[data-type='+i+']').size()){
                            if ($('input[data-type='+i+']').val() == '') $('input[data-type='+i+']').val(1);
                            summ += parseInt($('input[data-type='+i+']').val()) * e;
                        }
                    });
                    return summ;
                } else {
                    if (isVirtual === true) {
                        return $('input[name="product_info[][qty]"]', holder).val();
                    }
                    return ($('input[name="product_info[][qty]"]', holder).data('value-real') || $('input[name="product_info[][qty]"]', holder).val());
                }
            },
            checkAttributes:function(){
                let attributes = $('select[name*="product_info[][id]"]', holder);
                let success = true;
                if ($(attributes).size()> 0 ){
                    $.each($(attributes), function(i, e){
                        if ($(e).val() == 0) success = false;
                    })
                }
                return success;
            },
            checkQty:function(){
                let qty = $('.qty', holder);
                let success = true;
                if (!this.multi_qty){
                    if ($(qty).val() < 1 || $(qty).val().length == 0){
                        if ($(qty).data('max') > 0){
                            $(qty).val('1');
                        }
                    }
                } else {
                    if ($('input.total_qty', holder).val() == '0'){
                        $(qty).val('1');
                        $('input.total_qty', holder).val('1');
                    }
                }
                return success;
            },
            checkQuantity: function(){
                if (this.multi_qty){
                    var correct = true;
                    var summ = 0;
                    $.each(this.multi_qty_data, function(i, e){
                        if($('input[data-type='+i+']').size()){
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
                }
            },
            setQty: function(qty){
                $('input[name="product_info[][qty]"]', holder).val(qty);
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
            price_manualy_modified:is_modified,
            bundle:[],
            renderBundles:function(){
                //$('.bundles-row').show();
            },
            getProducts: function(obj, reProd){
                var product = this;
                var products = $('input, select, textarea', holder).serializeArray();
                var productQty = $('input[name="product_info[][qty]"]', holder);
                var index = $(holder).index();
                $.each(products, function(i, e){
                    e.name = e.name.replace("product_info[", "product_info["+index);
                    products[i].name = e.name;
                    if (e.name.match(/\[qty\]$/)) {
                        if ($(productQty[index]).data('value-real')) {
                            e.value = $(productQty[index]).data('value-real');
                        }
                    }
                });
                if (product.multi_qty){
                    if (obj){ //recalc
                        products.push({ 'name': 'product_info['+index+'][qty]', 'value': $(obj).val()});
                        products.push({ 'name': 'product_info['+index+'][type]', 'value': $(obj).data('type')});
                    } else {
                        products.push({ 'name': 'product_info['+index+'][qty]', 'value': $('input.total_qty', holder).val()});
                    }

                }
                if (product.price_manualy_modified){
                    products.push({ 'name': 'product_info['+index+'][product_modified]', 'value': 1});
                }
                if (reProd){
                    products.push({ 'name': 'product_info['+index+'][products_id]', 'value': product.products_id});
                } else {
                    products.push({ 'name': 'product_info['+index+'][products_id]', 'value': product.uprid});
                }

                if (product.edit){
                    products.push({ 'name': 'edit', 'value': true});
                }
                if (product.newDetails.price != product.oldDetails.price){
                    products.push({ 'name': 'product_info['+index+'][price_changed]', 'value': 1});
                }

                if (product.newDetails.name != product.oldDetails.name){
                    products.push({ 'name': 'product_info['+index+'][name_changed]', 'value': 1});
                }

                return products;
            },
            getDetails:function(obj, reProd){
                var product = this;
                var params = product.getProducts(obj, reProd);

                params.push({ 'name': 'action', 'value': 'get_details'});

                $.post(url, params, function(data){
                    var multi_qty = product.multi_qty;
                    var info = data.product_info;

                    if (info.hasOwnProperty('html_attributes')){
                        if (info.html_attributes.length > 0){
                            $('.product-attributes', holder).replaceWith(info.html_attributes);
                        } else {
                            $('.attributes-parent', holder).hide();
                        }
                    }

                    $('.product-attributes select', holder).addClass('form-control');
                    if (info.hasOwnProperty('virtual_item_qty') && info.virtual_item_qty > 1) {
                        $('.valid1', holder).html(" " + (info.product_qty/info.virtual_item_qty).toFixed(2) + " (" +  info.product_qty + ")");
                    } else {
                        $('.valid1', holder).html(info.product_qty);
                    }

                    product.stockInfo.max = info.product_qty;
                    $('.qty', holder).attr('data-max', info.product_qty);

                    if (multi_qty){
                        $('.qty_pack', holder).attr('data-max',Math.floor(data.product_qty/parseInt(info.cartoon_details.product.pack_unit))).attr('data-min',0);
                        $('.qty_packaging', holder).attr('data-max',Math.floor(data.product_qty/parseInt(info.cartoon_details.product.packaging))).attr('data-min',0);
                        product.multi_qty_data.unit = 1;
                        if (info.cartoon_details.product.pack_unit > 0)
                            product.multi_qty_data.pack_unit = parseInt(info.cartoon_details.product.pack_unit);
                        if (info.cartoon_details.product.packaging > 0){
                            product.multi_qty_data.packaging = parseInt(info.cartoon_details.product.packaging);
                            if (product.multi_qty_data.hasOwnProperty('pack_unit')){
                                product.multi_qty_data.packaging *= product.multi_qty_data.pack_unit;
                            }
                        }
                    }

                    if (info.hasOwnProperty('stock_indicator')){
                        $('.valid', holder).html('('+info.stock_indicator.stock_indicator_text+')');
                         product.stockInfo.max = info.stock_indicator.quantity_max;
                         $('.qty', holder).attr('data-max',info.stock_indicator.quantity_max);
                         if (multi_qty){
                            $('.qty_pack', holder).attr('data-max',Math.floor(info.stock_indicator.quantity_max/parseInt(info.cartoon_details.product.pack_unit)));
                            $('.qty_packaging', holder).attr('data-max',Math.floor(info.stock_indicator.quantity_max/parseInt(info.cartoon_details.product.packaging*info.cartoon_details.product.pack_unit)));
                         }
                    }

                    //if (info.hasOwnProperty('order_quantity')){
                    if (info.hasOwnProperty('order_quantity_step') && info.order_quantity_step > 0){
                        product.stockInfo.step = info.order_quantity_step;
                        $('input[name="product_info[][qty]"]', holder).attr('data-step', info.order_quantity_step);
                        if (multi_qty){
                            $('.qty', holder).attr('data-step', data.order_quantity.order_quantity_step);
                            $('.qty_pack', holder).attr('data-step', 1);
                            $('.qty_packaging', holder).attr('data-step', 1);
                        }
                    } else {
                        $('input[name="product_info[][qty]"]', holder).attr('data-step', 1);
                        if (multi_qty){
                            $('.qty_pack', holder).attr('data-step', 1);
                            $('.qty_packaging', holder).attr('data-step', 1);
                        }
                    }
                    let checkQty = false;
                    let checkVirtual = false;
                    if (info.hasOwnProperty('virtual_item_qty') && info.virtual_item_qty > 1) {
                        if (info.hasOwnProperty('virtual_item_step') && $.isArray(info.virtual_item_step) && (info.virtual_item_step.length > 0)) {
                            product.stockInfo.virtual_item_qty = parseInt(info.virtual_item_qty);
                            product.stockInfo.virtual_item_step = info.virtual_item_step;
                            checkQty = true;
                            checkVirtual = true;
                        }
                    }
                    if (info.hasOwnProperty('order_quantity_minimal') && info.order_quantity_minimal > 0){
                        product.stockInfo.min = info.order_quantity_minimal;
                        $('input[name="product_info[][qty]"]', holder).attr('data-min', info.order_quantity_minimal);
                        if ($('.qty', holder).val().length == 0 ){
                            $('.qty', holder).val(info.order_quantity_minimal);
                        }
                        var rQty = $('.qty', holder).val();
                        if (info.hasOwnProperty('virtual_item_qty') && info.virtual_item_qty > 1) {
                            rQty *= parseInt(info.virtual_item_qty);
                        }
                        if (parseInt(rQty) < parseInt(info.order_quantity_minimal) && !multi_qty)
                            $('.qty', holder).val(info.order_quantity_minimal);

                        if (multi_qty){
                            $('.qty', holder).attr('data-min',0);
                        }
                        checkQty = true;
                    } else {
                        if (multi_qty){
                            $('.qty', holder).attr('data-min',0);
                            $('.qty_pack', holder).attr('data-min',0);
                            $('.qty_packaging', holder).attr('data-min',0);
                        }else{
                            $('input[name="product_info[][qty]"]', holder).attr('data-min', 1);
                            $('.qty', holder).val(1);
                        }
                    }
                    if (checkQty == true) {
                        if (checkVirtual == true) {
                            product.stockInfo.min = (product.stockInfo.min ? parseInt(product.stockInfo.min, 10) : 1);
                            product.stockInfo.max = (product.stockInfo.max ? parseInt(product.stockInfo.max, 10) : 0);
                            product.stockInfo.step = (product.stockInfo.step ? parseInt(product.stockInfo.step, 10) : 1);
                            product.stockInfo.virtual_item_qty = (product.stockInfo.virtual_item_qty ? parseInt(product.stockInfo.virtual_item_qty, 10) : 1);
                            product.stockInfo.virtual_item_step = (product.stockInfo.virtual_item_step ? product.stockInfo.virtual_item_step : [1]);
                            if ((product.stockInfo.virtual_item_qty > 1) && $.isArray(product.stockInfo.virtual_item_step)) {
                                $.each(product.stockInfo.virtual_item_step, function(index) {
                                    product.stockInfo.virtual_item_step[index] = parseInt(this, 10);
                                });
                                $('.qty', holder).attr('data-value-real', parseFloat($('.qty', holder).val()) * parseFloat(product.stockInfo.virtual_item_qty));
                                $('.qty', holder).attr('data-virtual-item-qty', product.stockInfo.virtual_item_qty);
                                $('.qty', holder).attr('data-virtual-item-step', JSON.stringify(product.stockInfo.virtual_item_step));
                            }
                        }
                        $('.qty', holder).each(function(){
                            $(this).trigger('check_quantity', [$(this).val()])
                        })
                        //$('.qty', holder).trigger('check_quantity');
                        //product.checkQuantity();
                    }
                    //}
                    started = true;
                    if (product.edit){
                        if (info.hasOwnProperty('final_price')){
                            if (parseFloat(info.final_price).toFixed(2) != parseFloat(info.product_unit_price).toFixed(2)){
                                product.price_manualy_modified = true;
                                product.oldDetails.price = parseFloat(info.product_unit_price);
                                product.newDetails.price = parseFloat(info.final_price);
                            }
                        }
                    }

                    if (info.special_unit_price > 0 ){
                        if (!product.price_manualy_modified){
                            product.oldDetails.price = info.special_unit_price;
                            product.newDetails.price = info.special_unit_price;
                        }
                        //$('.old_price', holder).html(info.product_price);
                        $('input[name="product_info[][final_price]"]', holder).val(product.getPrice(false));
                        $('input[name="product_info[][final_price_tax]"]', holder).val(product.getPrice(true))
                        $('.final_price', holder).html(product.getFormatted(product.getPrice(false)));
                        $('.final_price_tax', holder).html(product.getFormatted(product.getPrice(true)));
                    } else {
                        if (!product.price_manualy_modified){
                            product.oldDetails.price = info.product_unit_price;
                            product.newDetails.price = info.product_unit_price;
                        }
                        $('input[name="product_info[][final_price]"]', holder).val(product.getPrice(false));
                        $('input[name="product_info[][final_price_tax]"]', holder).val(product.getPrice(true))
                        $('.final_price', holder).html(product.getFormatted(product.getPrice(false)));
                        $('.final_price_tax', holder).html(product.getFormatted(product.getPrice(true)));
                    }

                    if (product.multi_qty && info.hasOwnProperty('cartoon_details')){
                        if (info.cartoon_details.hasOwnProperty('single_price')){
                            product.newDetails.multiprice.unit = info.cartoon_details.single_price.unit_base;
                            $('.final_price_unit', holder).html(product.showPrice(info.cartoon_details.single_price.unit_base, false));
                            $('.final_price_tax_unit', holder).html(product.showPrice(info.cartoon_details.single_price.unit_base, true));
                            product.newDetails.multiprice.pack_unit = info.cartoon_details.single_price.pack_base;
                            $('.final_price_pack_unit', holder).html(product.showPrice(info.cartoon_details.single_price.pack_base, false));
                            $('.final_price_tax_pack_unit', holder).html(product.showPrice(info.cartoon_details.single_price.pack_base, true));
                            product.newDetails.multiprice.packaging = info.cartoon_details.single_price.package_base;
                            $('.final_price_packaging', holder).html(product.showPrice(info.cartoon_details.single_price.package_base, false));
                            $('.final_price_tax_packaging', holder).html(product.showPrice(info.cartoon_details.single_price.package_base, true));
                        }
                        if (info.cartoon_details.hasOwnProperty('single_price_data')){
                            $('.final_price_'+info.cartoon_details.single_price_data.current_type, holder).html(product.showPrice(info.cartoon_details.single_price_data.single_price_base, false));
                            $('.final_price_tax_'+info.cartoon_details.single_price_data.current_type, holder).html(product.showPrice(info.cartoon_details.single_price_data.single_price_base, true));
                            product.newDetails.multiprice[info.cartoon_details.single_price_data.current_type] = info.cartoon_details.single_price_data.single_price_base;
                        }
                        product.multiTotal();
                    }


                    if (info.hasOwnProperty('html_bundles')){
                        $('.bundles-row', holder).html(info.html_bundles)
                        $('.bundles-row', holder).show();
                    }

                    if (info.hasOwnProperty('html_configurator')){
                        $('.product-configurator', holder).html(info.html_configurator);
                        $('.configurator-row', holder).show();
                        if (info.hasOwnProperty('configurator_price')){
                            $('.product-price-configurator', holder).html(info.configurator_price);
                        }
                        if (data.hasOwnProperty('configurator_box')){
                            if (data.configurator_box.hasOwnProperty('data')){
                                product.is_conf = true;
                                if (data.configurator_box.data.hasOwnProperty('configurator_elements')){
                                    $.each(data.configurator_box.data.configurator_elements, function(i, e){
                                        $('.item-content[data-id='+e.selected_id+'] .element_total_summ', holder).html(product.showPrice(e.selected_actual_price * e.elements_qty));
                                        $('.item-content[data-id='+e.selected_id+'] .element_total_summ', holder).attr('data-price', e.selected_actual_price);
                                        $('.item-content[data-id='+e.selected_id+'] .element_total_summ_tax', holder).html(product.showPrice(e.selected_actual_price * e.elements_qty, true, $('.item-content[data-id='+e.selected_id+']') .parents('.pc_row').find('select.tax')));
                                    })
                                }
                            }
                            product.showConfTotal();
                        }

                        if (product.edit){
                            if (product.started){
                                product.getDetails();
                                product.started = false;
                            }
                            //
                        }
                    }

                    if(info.hasOwnProperty('html_discount') ){
                        $('.discount_table_view', holder).html(info.html_discount).show();
                    }
                    if (info.hasOwnProperty('discount_table_data') && info.discount_table_data.length>0){
                        product.overloadDiscountTable(info.discount_table_data);
                        product.renderDiscountTable();
                    }

                    product.changeTax();
                }, 'json');
            },
            started: true,
            manualPriceEdit:function(mode){
                var product = this;
                if (mode){
                    $('input[data-reg="final_price"]', holder).keyup(function(){
                        var p = parseFloat($(this).val())+product.getTax(parseFloat($(this).val()));
                        $('input[data-reg="final_price_tax"]', holder).val(p.toFixed(6));
                    })
                    $('input[data-reg="final_price_tax"]', holder).keyup(function(){
                        var p = product.getunTaxed(parseFloat($(this).val()));
                        $('input[data-reg="final_price"]', holder).val(p.toFixed(6));
                    })
                } else {
                    if (product.newDetails.price != $('[data-reg="final_price"]', holder).val()){
                        product.price_manualy_modified = true;
                    }
                    product.setPrice($('[data-reg="final_price"]', holder).val());
                    $('.final_price', holder).html(product.getFormatted(product.getPrice(false)));
                    $('.final_price_tax', holder).html(product.getFormatted(product.getPrice(true)));
                    $('.total_summ', holder).html(product.calucalteTotal(false));
                    $('.total_summ_tax', holder).html(product.calucalteTotal(true));
                    if (product.is_conf) product.showConfTotal();

                }
            },
            manualNameEdit: function(mode){
                if (mode){
                    //
                } else {
                    product.newDetails.name = $('[data-reg="name"]', holder).val();
                    $('.name', holder).html(product.newDetails.name);
                }
            },
            manualEdit: function(item){
                var product = this;
                var is_price = $(item).parent().hasClass('edit_price')? true: false;
                var is_name = $(item).parent().hasClass('edit_name')? true: false;
                //var name = $('.'+$(item).data('element'), holder).attr('name');
                var f_class = $(item).data('element');

                if ($(item, holder).parent().hasClass('btn')){
                    $(item, holder).parent().removeClass('btn');
                    $.each($('.'+f_class), function(i, e){
                        $(e).attr('type','hidden');
                        $('.'+$(e).data('reg'), holder).css('display', 'inline-block');
                    })
                    if (is_price){
                        product.manualPriceEdit(false);
                    }
                    if (is_name){
                        product.manualNameEdit(false);
                    }
                } else {
                    $(item).parent().addClass('btn');
                    $.each($('.'+f_class), function(i, e){
                        $(e).attr('type','input');
                        $('.'+$(e).data('reg'), holder).css('display', 'none');
                        if (is_price){
                            product.manualPriceEdit(true);
                        }
                        if (is_name){
                            product.manualNameEdit(true);
                        }
                    })
                }
            }
        };
        return product;
    }