<script type="text/javascript">
    //===== Price and Cost START =====//
    var tax_rates = new Array();
    {if {$app->controller->view->tax_classes|default:array()|@count} > 0}
    {foreach $app->controller->view->tax_classes as $tax_class_id => $tax_class}
    tax_rates[{$tax_class_id}] = {\common\helpers\Tax::get_tax_rate_value($tax_class_id)};
    {/foreach}
    {/if}

    function doRound(x, places) {
        return Math.round(x * Math.pow(10, places)) / Math.pow(10, places);
    }

    function getTaxRate(uprid) {
        if ( uprid ) {
            if ( uprid.name ) {
                var matchUprid = new RegExp('(\\d[^_\\[]+)');
                var parseInputUprid = matchUprid.exec(uprid.name);
                if (parseInputUprid.length > 0) {
                    uprid = parseInputUprid[1];
                }
            }
            var $taxRateSelector = $('.js-inventory-tax-class').filter('[name$="'+uprid+'"]');
            if ( $taxRateSelector.length==1 ) {
                $taxRateSelector = $($taxRateSelector[0]);
                if ( typeof tax_rates[$taxRateSelector.val()] !== 'undefined' ){
                    return tax_rates[$taxRateSelector.val()];
                }
                return 0;
            }
        }
        var selected_value = document.forms['product_edit'].products_tax_class_id.selectedIndex;
        var parameterVal = document.forms['product_edit'].products_tax_class_id[selected_value].value;

        if ( (parameterVal > 0) && (tax_rates[parameterVal] > 0) ) {
            return tax_rates[parameterVal];
        } else {
            return 0;
        }
    }

    function percentFormat(num) {
        num = (''+num).replace(/\.?0+$/g,'');
        if ( num==='' ) num = '0';
        return num;
    }

    function currencyFormat(num, id=0) {

        if (!(parseInt(id)>0)) {
            id={$default_currency['id']|json_encode};
        }

        var sep_th_a = { {foreach $app->controller->view->currenciesFormats as $c}{$c['id']|json_encode}:{$c['thousands_point']|json_encode},{/foreach} };
        var sep_dec_a = { {foreach $app->controller->view->currenciesFormats as $c}{$c['id']|json_encode}:{$c['decimal_point']|json_encode},{/foreach} };
        var symbol_right_a = { {foreach $app->controller->view->currenciesFormats as $c}{$c['id']|json_encode}:{$c['symbol_right']|json_encode},{/foreach} };
        var symbol_left_a = { {foreach $app->controller->view->currenciesFormats as $c}{$c['id']|json_encode}:{$c['symbol_left']|json_encode},{/foreach} };
        var decimal_places_a = { {foreach $app->controller->view->currenciesFormats as $c}{$c['id']|json_encode}:{$c['decimal_places']|json_encode},{/foreach} };

        var sep_th = sep_th_a[id];
        var sep_dec = sep_dec_a[id];
        var symbol_right = symbol_right_a[id];
        var symbol_left = symbol_left_a[id];
        var decimal_places = decimal_places_a[id];
        var sign = '';
        if (num < 0) {
            num = Math.abs(num);
            sign = '-';
        }
        num = Math.round(num * Math.pow(10, decimal_places*1)) / Math.pow(10, decimal_places*1); // round
        var s = new String(num);
        p=s.indexOf('.');
        n=s.indexOf(',');
        var j = Math.floor(num);
        var s1 = new String(j);
        if (p>0 || n>0) {
            if (p>0) {
                s = s.replace('.', sep_dec);
            } else {
                s = s.replace(',', sep_dec);
            }
        }
        var j2 = Math.floor(num * 10);
        if (j == num) {
            s = s + sep_dec + '0000';
        } else if (j2 == num * 10) {
            s = s + '000';
        }
        var l = s1.length;
        var n = Math.floor((l-1)/3);
        while (n >= 1) {
            s = s.substring(0, s.indexOf(sep_dec)-(3*n)) + sep_th + s.substring(s.indexOf(sep_dec)-(3*n), s.length);
            n--;
        }
        s = s.substring(0, s.indexOf(sep_dec) + decimal_places * 1 + 1);
        s = sign + symbol_left + s + symbol_right;
        return s;
    }

    {if $app->controller->view->useMarketPrices == true}
    function updateGross() {
        return;
        var taxRate = getTaxRate();
        {foreach $app->controller->view->currenciesTabs as $currId => $currTitle}
        var grossValue = document.forms['product_edit'].products_price_{$currId}.value;
        if (taxRate > 0) {
            grossValue = grossValue * ((taxRate / 100) + 1);
        }
        document.forms['product_edit'].products_price_gross_{$currId}.value = doRound(grossValue, 6);
        {/foreach}
    }
    function updateNet() {
        var taxRate = getTaxRate();
        {foreach $app->controller->view->currenciesTabs as $currId => $currTitle}
        var netValue = document.forms['product_edit'].products_price_gross_{$currId}.value;
        if (taxRate > 0) {
            netValue = netValue / ((taxRate / 100) + 1);
        }
        document.forms['product_edit'].products_price_{$currId}.value = doRound(netValue, 6);
        {/foreach}
    }
    {else}
    function updateGross() {
        return;
        var taxRate = getTaxRate();
        var grossValue = document.forms['product_edit'].products_price.value;

        if (taxRate > 0) {
            grossValue = grossValue * ((taxRate / 100) + 1);
        }

        document.forms['product_edit'].products_price_gross.value = doRound(grossValue, 6);
    }
    function updateAllPrices() {
        var taxRate = getTaxRate();
        var grossValue = document.forms['product_edit'].products_price.value;

        if (taxRate > 0) {
            grossValue = grossValue * ((taxRate / 100) + 1);
        }
        var arrValue = [];
        $('[name="discount_price[]"]').each(function(i, e) {
            arrValue[i] = e.value;
            if (taxRate > 0) {
                arrValue[i] = arrValue[i] * ((taxRate / 100) + 1);
            }
        });
        $('[name="discount_price_gross[]"]').each(function(i, e) {
            e.value = doRound(arrValue[i], 6);
        });

        var arrValue = [];
        $('[name^="inventoryprice_"]').each(function(i, e) {
            arrValue[i] = e.value;
            if (taxRate > 0) {
                arrValue[i] = arrValue[i] * ((getTaxRate(e) / 100) + 1);
            }
        });
        $('[name^="inventorygrossprice_"]').each(function(i, e) {
            e.value = doRound(arrValue[i], 6);
        });

        var arrValue = [];
        $('[name^="inventoryfullprice_"]').each(function(i, e) {
            arrValue[i] = e.value;
            if (taxRate > 0) {
                arrValue[i] = arrValue[i] * ((getTaxRate(e) / 100) + 1);
            }
        });
        $('[name^="inventorygrossfullprice_"]').each(function(i, e) {
            e.value = doRound(arrValue[i], 6);
        });

        var arrValue = [];
        $('[name^="pack_unit_full_prices"]').each(function(i, e) {
            arrValue[i] = e.value;
            if (taxRate > 0 && e.value != '') {
                arrValue[i] = arrValue[i] * ((taxRate / 100) + 1);
            }
        });
        $('[name^="pack_unit_full_gross_prices"]').each(function(i, e) {
            if (arrValue[i] == '') {
                e.value = arrValue[i];
            } else {
                e.value = doRound(arrValue[i], 6);
            }
        });

        var arrValue = [];
        $('[name^="packaging_full_prices"]').each(function(i, e) {
            arrValue[i] = e.value;
            if (taxRate > 0 && e.value != '') {
                arrValue[i] = arrValue[i] * ((getTaxRate(e) / 100) + 1);
            }
        });
        $('[name^="packaging_full_gross_prices"]').each(function(i, e) {
            if (arrValue[i] == '') {
                e.value = arrValue[i];
            } else {
                e.value = doRound(arrValue[i], 6);
            }
        });

        var arrValue = [];
        $('[name^="inventory_discount_price_"]').each(function(i, e) {
            arrValue[i] = e.value;
            if (taxRate > 0) {
                arrValue[i] = arrValue[i] * ((getTaxRate(e) / 100) + 1);
            }
        });
        $('[name^="inventory_discount_gross_price_"]').each(function(i, e) {
            e.value = doRound(arrValue[i], 6);
        });

        var arrValue = [];
        $('[name^="inventory_discount_full_price_"]').each(function(i, e) {
            arrValue[i] = e.value;
            if (taxRate > 0) {
                arrValue[i] = arrValue[i] * ((getTaxRate(e) / 100) + 1);
            }
        });
        $('[name^="inventory_discount_full_gross_price_"]').each(function(i, e) {
            e.value = doRound(arrValue[i], 6);
        });

        {if is_array($app->controller->view->groups|default:null) && $app->controller->view->groups|default:array()|@count > 0}
        {foreach $app->controller->view->groups as $groups_id => $group}

        var fieldValue = document.forms['product_edit'].elements['products_groups_prices_{$groups_id}'].value
        if (fieldValue == -1) {
            document.forms['product_edit'].elements['products_groups_prices_gross_{$groups_id}'].value = doRound(fieldValue, 6);
        } else {
            {if \common\helpers\Acl::checkExtension('BusinessToBusiness', 'productBlock')}
            {\common\extensions\BusinessToBusiness\BusinessToBusiness::productBlock($group)}
            {else}
            if (taxRate > 0) {
                fieldValue = fieldValue * ((taxRate / 100) + 1);
            }
            {/if}
            document.forms['product_edit'].elements['products_groups_prices_gross_{$groups_id}'].value = doRound(fieldValue, 6);
        }

        var arrValue = [];
        $('[name="discount_price_{$groups_id}[]"]').each(function(i, e) {
            arrValue[i] = e.value;
            if (taxRate > 0) {
                arrValue[i] = arrValue[i] * ((taxRate / 100) + 1);
            }
        });
        $('[name="discount_price_gross_{$groups_id}[]"]').each(function(i, e) {
            e.value = doRound(arrValue[i], 6);
        });

        {/foreach}
        {/if}

    }

    function updateNet() {
        var taxRate = getTaxRate();
        var netValue = document.forms['product_edit'].products_price_gross.value;

        if (taxRate > 0) {
            netValue = netValue / ((taxRate / 100) + 1);
        }

        document.forms['product_edit'].products_price.value = doRound(netValue, 6);

        var arrValue = [];
        $('[name="discount_price_gross[]"]').each(function(i, e) {
            arrValue[i] = e.value;
            if (taxRate > 0) {
                arrValue[i] = arrValue[i] / ((taxRate / 100) + 1);
            }
        });
        $('[name="discount_price[]"]').each(function(i, e) {
            e.value = doRound(arrValue[i], 6);
        });

        var arrValue = [];
        $('[name^="inventorygrossprice_"]').each(function(i, e) {
            arrValue[i] = e.value;
            if (taxRate > 0) {
                arrValue[i] = arrValue[i] / ((getTaxRate(e) / 100) + 1);
            }
        });
        $('[name^="inventoryprice_"]').each(function(i, e) {
            e.value = doRound(arrValue[i], 6);
        });

        var arrValue = [];
        $('[name^="inventorygrossfullprice_"]').each(function(i, e) {
            arrValue[i] = e.value;
            if (taxRate > 0) {
                arrValue[i] = arrValue[i] / ((getTaxRate(e) / 100) + 1);
            }
        });
        $('[name^="inventoryfullprice_"]').each(function(i, e) {
            e.value = doRound(arrValue[i], 6);
        });

        var arrValue = [];
        $('[name^="pack_unit_full_gross_prices"]').each(function(i, e) {
            arrValue[i] = e.value;
            if (taxRate > 0 && e.value != '') {
                arrValue[i] = arrValue[i] / ((taxRate / 100) + 1);
            }
        });
        $('[name^="pack_unit_full_prices"]').each(function(i, e) {
            if (arrValue[i] == '') {
                e.value = arrValue[i];
            } else {
                e.value = doRound(arrValue[i], 6);
            }
        });

        var arrValue = [];
        $('[name^="packaging_full_gross_prices"]').each(function(i, e) {
            arrValue[i] = e.value;
            if (taxRate > 0 && e.value != '') {
                arrValue[i] = arrValue[i] / ((taxRate / 100) + 1);
            }
        });
        $('[name^="packaging_full_prices"]').each(function(i, e) {
            if (arrValue[i] == '') {
                e.value = arrValue[i];
            } else {
                e.value = doRound(arrValue[i], 6);
            }
        });

        var arrValue = [];
        $('[name^="inventory_discount_gross_price_"]').each(function(i, e) {
            arrValue[i] = e.value;
            if (taxRate > 0) {
                arrValue[i] = arrValue[i] / ((getTaxRate(e) / 100) + 1);
            }
        });
        $('[name^="inventory_discount_price_"]').each(function(i, e) {
            e.value = doRound(arrValue[i], 6);
        });

        var arrValue = [];
        $('[name^="inventory_discount_full_gross_price_"]').each(function(i, e) {
            arrValue[i] = e.value;
            if (taxRate > 0) {
                arrValue[i] = arrValue[i] / ((getTaxRate(e) / 100) + 1);
            }
        });
        $('[name^="inventory_discount_full_price_"]').each(function(i, e) {
            e.value = doRound(arrValue[i], 6);
        });

        {if is_array($app->controller->view->groups|default:null) && $app->controller->view->groups|default:array()|@count > 0}
        {foreach $app->controller->view->groups as $groups_id => $group}

        var fieldValue = document.forms['product_edit'].elements['products_groups_prices_gross_{$groups_id}'].value
        if (fieldValue == -1) {
            document.forms['product_edit'].elements['products_groups_prices_{$groups_id}'].value = doRound(fieldValue, 6);
        } else {
            {if {$group['groups_is_tax_applicable']} > 0}
            if (taxRate > 0) {
                fieldValue = fieldValue / ((taxRate / 100) + 1);
            }
            {/if}
            document.forms['product_edit'].elements['products_groups_prices_{$groups_id}'].value = doRound(fieldValue, 6);
        }

        var arrValue = [];
        $('[name="discount_price_gross_{$groups_id}[]"]').each(function(i, e) {
            arrValue[i] = e.value;
            if (taxRate > 0) {
                arrValue[i] = arrValue[i] / ((taxRate / 100) + 1);
            }
        });
        $('[name="discount_price_{$groups_id}[]"]').each(function(i, e) {
            e.value = doRound(arrValue[i], 6);
        });

        {/foreach}
        {/if}

    }
    {/if}







    function updateGrossPrice(el) {
        var taxRate = getTaxRate(el);
        var roundTo = 6;
        //$(el).focus();
        if($(el).attr('data-roundTo')) {
            roundTo = parseInt($(el).attr('data-roundTo'));
        }
        var targetId = el.id.replace('_price', '_price_gross');

        /* process % in special price first */
        if (el.value.slice(-1)=='%'){
            var id_suffix = $(el).attr('data-idsuffix');
            if (typeof id_suffix != 'undefined') {
                base_suffix = id_suffix.replace(/\d+$/, 0);
                base_price = parseFloat( unformatMaskField('#products_group_price' + base_suffix) ) || $('#group_price_container' + id_suffix).attr('data-base_price');
                el.value = doRound(base_price * (1-parseFloat(el.value.slice(0, -1))/100), roundTo);
            }
        }
        ////////

        var grossValue = parseFloat(el.value.replace(/[^(\d+)\.(\d+)]/g, '')) || 0; // net value by default
        if (grossValue==-2) { // generally - kostyl'
            grossValue = 0;
        }
        if (taxRate > 0) {
            grossValue = grossValue * ((taxRate / 100) + 1);
        }
        $('#' + targetId).val(doRound(grossValue, roundTo)).blur();
    }

    function updateNetPrice(el) {
        var taxRate = getTaxRate(el);
        var targetId = el.id.replace('_price_gross', '_price');
        var roundTo = 6;
        /* process % in special price first */
        if (el.value.slice(-1)=='%'){
            var id_suffix = $(el).attr('data-idsuffix');
            if (typeof id_suffix != 'undefined') {
                base_suffix = id_suffix.replace(/\d+$/, 0);
                base_price = parseFloat($('#products_group_price_gross' + base_suffix).val()) || $('#group_price_container' + id_suffix).attr('data-base_price_gross');
                el.value = doRound(base_price * (1-parseFloat(el.value.slice(0, -1))/100), roundTo);
            }
        }
        ////////
        var netValue = el.value; // gross value by default
        if (taxRate > 0) {
            netValue = netValue / ((taxRate / 100) + 1);
        }
        $('#' + targetId).val(doRound(netValue, roundTo)).blur();
    }

    function updateGrossVisible(uprid) {
        if (typeof uprid === 'object') uprid = null;
        /// update all visible gross price (on change tax class)
        /// inputs (visible) + lists (all)
        if ( !uprid ) {
            updateVisibleGrossInputs();

            $('#suppliers-placeholder{(int)$pInfo->products_id} .js-supplier-product').trigger('change');
        }

        ///lists: 1) attributes, inventory
        var fullPrice = $('#full_add_price').val(),
            mainTaxRate = getTaxRate(),
            taxRate = getTaxRate(uprid);

        $('a.inventory-popup-link').each(function (){
            var walkUprid = $(this).attr('href').replace(/^[^-]+/, '');
            updateInvListPrices(fullPrice, walkUprid, taxRate);

            if ( uprid && ('-'+uprid.replace(/\D/g,'-'))!=walkUprid ) return;
            $('#id'+walkUprid).find('input[name^="products_group_price_"]').each(function(){
                updateGrossPrice(this, taxRate);
            });
        });

    }

    function updateVisibleGrossInputs(el) {
        /// el - currency-group tab
        if (typeof el !== 'undefined') {
            $('input.price-options:checked:visible', $(el)).each(function() {
                $(this).click();
            });
            $(el).find('input[id*=_price]:visible').not('[id*=_price_gross]').keyup();
        } else {
            $('input.price-options:checked:visible').each(function() {
                $(this).click();
            });
            $('input[id*=_price]:visible').not('[id*=_price_gross]').keyup();
        }
    }
    function priceOptionsClick() {
        /// 1) recalculate related net price
        /// hide/show price related block (specials, wrap, surchase, point
        /// init bootstrapSwitch
        // no name - switch by JS
        var id = $(this).attr('id');
        $('input.price-options[id^="' + id.replace(/\d$/, '') + '"]').not('[id="' + id + '"]').prop("checked", false); // switch off other options
        var mainPriceSwitched = id.match(/_m\d$/); //not special
        var isInventory = id.match(/^iop/);
        var val = $(this).val(),
            id_suffix = $(this).attr('data-idsuffix'), // '_2' '_12_2'
            base_suffix = id_suffix.replace(/\d+$/, 0);

        if ( parseFloat(val)==-1) {

            if (mainPriceSwitched) {
                $('#div_wrap_hide' + id_suffix).hide();
                $('#products_group_price' + id_suffix).val(-1);
            } else {
                $('#div_sale_prod' + id_suffix).hide();
                $('#special_price' + id_suffix).val(-1);
            }

        } else if ( parseFloat(val)==-2 ) {
            if (mainPriceSwitched) {
                /// save correct order in arrays!!!!
                toshow = ['span_products_group_price', 'span_products_group_price_gross', 'div_wrap_hide'];
                tohide = ['products_group_price', 'products_group_price_gross'];
            } else {
                toshow = ['span_special_price', 'span_special_price_gross', 'div_sale_prod'];
                tohide = ['special_price', 'special_price_gross'];
            }

            /// 1) recalculate related net price
            if (mainPriceSwitched) {
                // either from input or from
                base_price = parseFloat( unformatMaskField('#products_group_price' + base_suffix) ) || $('#group_price_container' + id_suffix).attr('data-base_price');
            } else {
                //masked base_price = parseFloat($('#special_price' + base_suffix).val());
                base_price = parseFloat(unformatMaskField('#special_price' + base_suffix));
                if ( isNaN(base_price) || base_price<=0) {
                    base_price = $('#group_price_container' + id_suffix).attr('data-base_special_price');
                }
                if ( isNaN(base_price) || base_price<=0) {
                    base_price = $('#group_price_container' + id_suffix).attr('data-base_price');
                }
            }
            discount = 1 - parseFloat($('#group_price_container' + id_suffix).attr('data-group_discount'))/100;
            curr_id = $('#group_price_container' + id_suffix).attr('data-currencies-id');

            $('#' + tohide[0] + id_suffix).val(base_price*discount);
            $('#' + tohide[0] + id_suffix).keyup();// I'm lazy - calculate gross price

            if ($(this).parents('.option-percent-price').length==0) {
                $('#' + toshow[0] + id_suffix).text(currencyFormat(doRound(base_price * discount, 6), curr_id));
                $('#' + toshow[1] + id_suffix).text(currencyFormat(unformatMaskField('#' + tohide[1] + id_suffix), curr_id));
            }else{
                $('#' + toshow[0] + id_suffix).text(percentFormat(doRound(base_price, 6)));
                $('#' + toshow[1] + id_suffix).text(percentFormat(doRound(base_price, 6)));
            }

            $('#' + tohide[0] + id_suffix).val('-2');

            for (i=0; i<toshow.length; i++) $('#' + toshow[i] + id_suffix).show();
            for (i=0; i<tohide.length; i++) $('#' + tohide[i] + id_suffix).hide();

            tab = $('#div_wrap_hide' + id_suffix).not(".inited");
            if (mainPriceSwitched && tab.length) {
                tab.addClass('inited');

                $('.check_sale_prod:visible, .check_points_prod:visible, .check_supplier_price_mode:visible, .check_qty_discount_prod:visible, .check_gift_wrap:visible, .check_shipping_surcharge:visible, .check_delivery_option:visible', tab).bootstrapSwitch(bsPriceParams);
            }

        } else {
            if (mainPriceSwitched) {
                /// save correct order in arrays!!!!
                tohide = ['span_products_group_price', 'span_products_group_price_gross'];
                toshow = ['products_group_price', 'products_group_price_gross', 'div_wrap_hide'];
            } else {
                tohide = ['span_special_price', 'span_special_price_gross'];
                toshow = ['special_price', 'special_price_gross', 'div_sale_prod'];
            }
            for (i=0; i<toshow.length; i++) $('#' + toshow[i] + id_suffix).show();
            for (i=0; i<tohide.length; i++) $('#' + tohide[i] + id_suffix).hide();

            if (parseFloat(unformatMaskField('#' + toshow[0] + id_suffix))<0) {
            //if (parseFloat($('#' + toshow[0] + id_suffix).val())<0) {
                $('#' + toshow[0] + id_suffix).val(0);
                $('#' + toshow[1] + id_suffix).val(0);
            }

            tab = $('#div_wrap_hide' + id_suffix).not(".inited");
            if (mainPriceSwitched && tab.length) {
                tab.addClass('inited');

                $('.check_sale_prod:visible, .check_points_prod:visible, .check_supplier_price_mode:visible, .check_qty_discount_prod:visible, .check_gift_wrap:visible, .check_shipping_surcharge:visible', tab).bootstrapSwitch({
                    onSwitchChange: function (element, argument) {
                        var t = $(this).attr('data-toswitch');
                        if (typeof(t) != 'undefined') { //all divs, css class of which is starting with t
                            sel = '[class*="' + t +'"]';
                        } else {
                            sel = '#div_' + $(this).attr('id');
                        }
                        if (argument) {
                            $(sel).show();
                        } else {
                            $(sel).hide();
                        }
                        return true;
                    },
                    onText: "{$smarty.const.SW_ON}",
                    offText: "{$smarty.const.SW_OFF}",
                    handleWidth: '20px',
                    labelWidth: '24px'
                });
            }
        }

    }

    function invPriceTabsShown(clicked='') {
        var el = $(this).attr('href');
        if (typeof(el) === 'undefined' && clicked !== '') {
            el = clicked;
        }
        updateVisibleGrossInputs($(el));

        $('.check_qty_discount_prod, .check_supplier_price_mode, .attr_file_switch').tlSwitch({
            onSwitchChange: function (element, argument) {
                var t = $(this).attr('data-toswitch');
                var tcss = $(this).attr('data-togglecss');
                if (typeof(tcss) != 'undefined') { // toggle option
                    $('.' + tcss).toggle();
                } else {
                    if (typeof(t) != 'undefined') { //all divs, css class of which is starting with t
                        sel = '[class*="' + t +'"]';
                    } else {
                        sel = '#div_' + $(this).attr('id');
                    }
                    if (argument) {
                        $(sel).show();
                    } else {
                        $(sel).hide();
                    }
                }
                return true;
            }
        });
    }
    /* updates net and gross prices in assigned attributes and inventory blocks (span, currency formatted)
    * if tax rate is specified then only gross price is calculated and updated
    */
    function updateInvListPrices(fullPrice='', upridSuffix='', taxRate='') {
        if (fullPrice!=0 && fullPrice!=1) {
            fullPrice = $('#full_add_price').val();
        }
        if (upridSuffix!='') {
            if (fullPrice=='1') {
                pricePrefix = '';
            } else {
                pricePrefix = $('select.default_currency[id^="invPricePrefix' + upridSuffix + '"]').val() || '';
            }
            if ( pricePrefix.indexOf('%')!==-1 ){
                priceNet = percentFormat($('input.default_currency[id^="products_group_price' + upridSuffix + '"]:first').val());
                priceGross = priceNet;
            }else {
                priceNet = currencyFormat($('input.default_currency[id^="products_group_price' + upridSuffix + '"]:first').val());
                if (taxRate == '') {
                    priceGross = currencyFormat($('input.default_currency[id^="products_group_price_gross' + upridSuffix + '"]:first').val());
                } else {
                    priceGross = currencyFormat($('input.default_currency[id^="products_group_price' + upridSuffix + '"]:first').val() * ((taxRate / 100) + 1));
                }
            }
            if (taxRate=='') {
                $('#inv_list_price' + upridSuffix).text(pricePrefix + priceNet);
                $('#attr_list_price' + upridSuffix).text(pricePrefix + priceNet);
            }
            $('#inv_list_price_gross' + upridSuffix).text(pricePrefix +  priceGross);
            $('#attr_list_price_gross' + upridSuffix).text(pricePrefix +  priceGross);
        }
    }


    function attrInventoryDetailsClick() {
        var popup = $($(this).attr('href'));
        //save all vals for cancel button functionality
        var _vals = {};
        popup.find("input").each(function() {
            if (this.type == 'text' && !this.disabled && typeof(this.name) !== 'undefined' && this.name != '') {
                if ( this.name.substr(-2,2) == '[]') {
                    if (typeof _vals[this.name] !== 'object') {
                        _vals[this.name] = new Array();
                    }
                    _vals[this.name].push(this.value);
                } else {
                    _vals[this.name] = this.value;
                }
            }
            if (this.type == 'checkbox' && !this.disabled && typeof(this.name) !== 'undefined' && this.name != '') {
                if ( this.name.substr(-2,2) == '[]') {
                    if (typeof _vals[this.name] !== 'object') {
                        _vals[this.name] = new Array();
                    }
                    _vals[this.name].push(this.checked);
                } else {
                    _vals[this.name] = this.checked;
                }
            }
        });
        //saved

        popup.find('.js-supplier-product').trigger('change');

        popup.show();
        //init visible elements.
        invPriceTabsShown(popup);
        if ( typeof getCountSuppliersPricesInv === 'function') getCountSuppliersPricesInv(popup);

        $('#content, .content-container').css({ 'position': 'relative', 'z-index': '100'});
        $('.w-or-prev-next > .tabbable').css({ 'z-index': '5'});

        var height = function(){
            var h = $(window).height() - $('.popup-heading', popup).height() - $('.popup-buttons', popup).height() - 120;
            $('.popup-content', popup).css('max-height', h);
        };
        height();
        $(window).on('resize', height);
//////// cancel button //////////
        $('.pop-up-close-page, .btn-cancel', popup).off('click').on('click', function(){
            //Cancel button - Reset changes
            popup.find("input").each(function() {
                if (!$(this).is('[readonly]') && typeof(this.name) !== 'undefined' && this.name != '') {
                    if (this.type == 'text') {
                        if(_vals[this.name] !== 'undefined') {
                            if (typeof _vals[this.name]  === 'object') { // array
                                this.value = _vals[this.name].shift();
                            } else {
                                this.value = _vals[this.name];//this.defaultValue;
                            }
                        } else {
                            this.value = this.defaultValue;
                        }
                    }
                    if (this.type == 'checkbox') {
                        if(_vals[this.name] !== undefined) {
                            if (typeof _vals[this.name]  === 'object') { // array
                                _checked = _vals[this.name].shift();
                            } else {
                                _checked = _vals[this.name];
                            }
                            try {
                                if ($(this).parent().is('div.bootstrap-switch-container'))
                                    $(this).bootstrapSwitch('state', _checked);
                            } catch (err) { }
                            this.checked = _checked;
                        }
                    }
                }
            });

            $('.js_inventory_group_price', popup).each(function() {
                $(this).removeClass("inited");
            });

            popup.hide();
            $(window).off('resize', height);
            $('#content, .content-container').css({ 'position': '', 'z-index': ''});
            $('.w-or-prev-next > .tabbable').css({ 'z-index': ''});
        });
//// save ////
        $('.btn-save2', popup).off('click').on('click', function(){
            //update default currency "main" (0) group  prices in lists

            fullPrice = $('#full_add_price').val();
            uprid=$(this).attr('data-upridsuffix');
            updateInvListPrices(fullPrice, uprid);

            popup.hide();
            $(window).off('resize', height);
            $('#content, .content-container').css({ 'position': '', 'z-index': ''});
            $('.w-or-prev-next > .tabbable').css({ 'z-index': ''});
        });

        $('.js_inventory_group_price input.price-options').off('click').on('click', priceOptionsClick);
        return false;
    }
    $(document).on('click','.inventory-popup-link',attrInventoryDetailsClick);

    window.supplierExtraPopup = function(button) {
        var $dataSource = $(button).parents('.js-edit-supplier-product-popup-container').find('.js-edit-supplier-product-popup');
        var $popupData = $dataSource.clone();
        if ($('body > #supplierProductDetailEdit').length===0){
            var _move = $('#supplierProductDetailEdit');
            $('body').append(_move.clone());
            _move.remove();
        }
        var $popupContent = $('#supplierProductDetailEdit');
        $popupContent.find('.popup-content').html($popupData);

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
        $popupContent.find('.js-extra-update-button').off('click').on('click',function(){
            $('input, select, textarea', $popupData).each(function(){
                var $input = $(this);
                var $targetInput = $dataSource.find('[name="'+this.name+'"]');
                if ( this.type.toLowerCase()=='checkbox' ) {
                    if ($targetInput.get(0).checked != $input.get(0).checked){
                        $targetInput.trigger('click');
                    }
                }else{
                    var targetValue = $input.val();
                    if ( targetValue==='' && $input.hasClass('js-supplier-tax-rate') && typeof jQuery.fn.textInputNullableValue === 'function' ) {
                        targetValue = $input.textInputNullableValue();
                    }
                    $targetInput.val(targetValue);
                    $targetInput.trigger('change');
                    if ( $input.hasClass('js-supplier-tax-rate') && typeof jQuery.fn.textInputNullableValue === 'function' ) {
                        $targetInput.trigger('update-state');
                    }
                }
            });
            $(this).parents('.js-SupplierExtraDataPopup').addClass('hidden');
            $contentCont.css({ 'position': cZKeep, 'z-index': cPKeep});
        });

        $('div.stock-reorder-supplier input:checkbox')
            .off()
            .on('change', function() {
                $(this).closest('div').find('input:text.form-control').attr('disabled', 'disabled');
                if ($(this).prop('checked') == true) {
                    $(this).closest('div').find('input:text.form-control').removeAttr('disabled');
                }
            })
            .change();

        return false;
    }
    $(document).on('click','.js-supplier-detail-edit', function(event){ return supplierExtraPopup(event.target) });

    $(document).on('change', '.js-bind-ctrl', function(event){
        var $input = $(event.target);
        var labelValue = $input.val();
        if ( labelValue==='' && $input.hasClass('js-supplier-tax-rate') && typeof jQuery.fn.textInputNullableValue === 'function' ) {
            labelValue = $input.textInputNullableValue();
        }
        const $bindText = $input.closest('.js-bind-text')
        $bindText.find('.js-bind-value').html( labelValue );
        if (labelValue) {
            $bindText.removeClass('hide-suppliers-info')
        } else {
            $bindText.addClass('hide-suppliers-info')
        }
    });

    function updateSupplierUpDownBtns() {
        cnt = getSuppliersCount();
        $('.move-up-down-btns').toggle( cnt > 1);
        if (cnt > 1) {
            $('.move-up-down-btns button.btn-up').prop('disabled', false).first().prop('disabled',true);
            $('.move-up-down-btns button.btn-down').prop('disabled', false).last().prop('disabled', true);
        }
        $('.move-up-down-btns button').off('click').on('click', function (event) {
            var div = $(this).closest('.supplier-product-widget');
            if ($(event.target).is('.btn-down')) {
                div.next('.supplier-product-widget').after(div);
            } else {
                div.prev('.supplier-product-widget').before(div);
            }
            $('#suppliers-default-sort').bootstrapSwitch('state', false/*state*/,true/*skipevent*/);
            $(document).trigger('suppliers:sort-changed');
            //return false;
        })
    }

    function updateSupplierSortSwitch()
    {
        $('#supplier-default-sort-holder').toggle(getSuppliersCount() > 1);
    }
    function isSuppliersSortedDef()
    {
        return $('#suppliers-default-sort').bootstrapSwitch('state');
    }
    function getSuppliersCount() {
        return $('.supplier-product-widget').length;
    }

    function sortSuppliersDef()
    {
        if (!isSuppliersSortedDef()) return;
        var root = $('#suppliers-placeholder{(int)$pInfo->products_id}');
        var suppliersOrderedIds = {\common\helpers\Suppliers::orderedIds()|json_encode};
        var sortString = ','+suppliersOrderedIds.join(',')+',';
        $('.js-supplier-product',root).sort(function(a,b) {
            return sortString.indexOf($(a).data('supplier-id')) > sortString.indexOf($(b).data('supplier-id'));
        }).appendTo(root);
        $(document).trigger('suppliers:sort-changed');
    }

    function updatePricesAndProfit(supplierBlock, supplierId) {
        var landedPriceAssigned = !!getSupplierLandedPriceField(supplierBlock).val();
        var supplierTaxRate = parseFloat( $('.js-supplier-tax-rate',supplierBlock).textInputNullableValue() );
        var supplierTaxInCost = $('.js-supplier-tax-rate-flag',supplierBlock).get(0).checked?1:0;
        var supplierDiscount = $('.js-supplier-discount', supplierBlock).textInputNullableValue();
        // currency and risk
        var supplierCost = getConvertedWithCurrencyRisk(getSupplierCostField(supplierBlock));
        // supplier discount
        var calcLandedCrossPrice = doRound(supplierCost * (1 - supplierDiscount / 100), 6);
        // supplier tax
        var calcLandedNetPrice = 0;
        if (!supplierTaxInCost) {
            calcLandedNetPrice = calcLandedCrossPrice;
            calcLandedCrossPrice = calcLandedCrossPrice * ((100 + supplierTaxRate) / 100);
        } else {
            calcLandedNetPrice = supplierTaxRate > 0 ?  calcLandedCrossPrice*(100-supplierTaxRate)/100 : calcLandedCrossPrice;
        }
        // manual landed price
        $('.js-overridden-mark', supplierBlock).toggle(landedPriceAssigned);
        fieldLandedPrice = getSupplierLandedOrCalcField(supplierBlock);
        if (landedPriceAssigned) {
            finalLandedCrossPrice = getConvertedWithCurrencyRisk(getSupplierLandedPriceField(supplierBlock));
            finalLandedNetPrice = supplierTaxRate > 0 ?  finalLandedCrossPrice*(100-supplierTaxRate)/100 : finalLandedCrossPrice;
            fieldLandedPrice.addClass('overridden-price');
        } else {
            finalLandedCrossPrice = calcLandedCrossPrice;
            finalLandedNetPrice = calcLandedNetPrice
            fieldLandedPrice.removeClass('overridden-price');
        }
        //var supplierCurrency = $('.js-supplier-currency',supplierBlock).val()
        // Cross Landed
        fieldLandedPrice.html(currencyFormat(finalLandedCrossPrice/*, supplierCurrency*/)).data('calc-value', finalLandedCrossPrice);

        // Net Landed
        $('.js-supplier-landed-price-net-displayed', supplierBlock).html(currencyFormat(finalLandedNetPrice/*, supplierCurrency*/));

        // Calculated Price and Profit
        var calcNetPrice = $('.js-supplier-calc-net-price', supplierBlock).data('value');
        var taxRate = getTaxRate();
        var calcGrossPrice = calcNetPrice * ((taxRate / 100) + 1);
        var calcProfit = calcNetPrice - finalLandedCrossPrice;
        $('.js-supplier-calc-gross-price', supplierBlock).html(currencyFormat(calcGrossPrice));
        $('.js-supplier-calc-profit', supplierBlock).html(currencyFormat(calcProfit));

        // Current Price and Profit
        if (typeof unformatMaskField === 'function'){
            var curNetPrice = unformatMaskField('input.js-products_group_price:first');
        }else{
            var curNetPrice = $('input.js-products_group_price:first').val();
        }
        var curGrossPrice =curNetPrice * ((taxRate / 100) + 1);
        var curProfit = curNetPrice - finalLandedCrossPrice;
        $('.js-supplier-cur-net-price', supplierBlock).html(currencyFormat(curNetPrice));
        $('.js-supplier-cur-gross-price', supplierBlock).html(currencyFormat(curGrossPrice));
        $('.js-supplier-cur-profit', supplierBlock).html(currencyFormat(curProfit));
    }

    function updatePricesAndProfitAll() {
        $('.js-supplier-product').each( function (index, elem) {
            updatePricesAndProfit( $(elem), $(elem).data('supplier-id'));
        })
    }

    $(document).ready(function() {
        updateSupplierUpDownBtns();
        updateSupplierSortSwitch();

        $('#suppliers-default-sort').bootstrapSwitch({ onSwitchChange: function (e, status) {
            if (status) {
                sortSuppliersDef();
            }
        }});

        $(document).on('suppliers:added', function (event, data) {
            updateSupplierAt($('#suppliers{$pInfo->products_id}-'+data.suppliers_id));
            updateSupplierUpDownBtns();
            updateSupplierSortSwitch();
        }).on('suppliers:deleted', function (event, data) {
            var index = $suppliersIdWithDefValues.indexOf(parseInt(data.supplierId));
            if (index !== -1) $suppliersIdWithDefValues.splice(index, 1);
            updateSupplierUpDownBtns();
            updateSupplierSortSwitch();
        }).on('suppliers:sort-changed', function () {
            updateSupplierUpDownBtns();
        }).on('suppliers:selected-manually', function () {
            updateSupplierAll();
        }).on('suppliers:calc-price-changed', function (event, data) {
            updatePricesAndProfit(data.updateBlock, data.supplierId);
        }).on('suppliers:landed-price-changed', function (event, data) {
            updatePricesAndProfit(data.updateBlock, data.supplierId);
        }).on('suppliers:group-price-changed', function (event, data) {
            updatePricesAndProfitAll();
        });

        $('#editLandedPrice button.save-changes').on('click', function (event) {
            var updateBlock = $(event.target).closest('.modal');
            var supplierBlockId = updateBlock.data('supplier-block');
            var supplierBlock = $('#' + supplierBlockId);
            var type = $('input[name="modal-selected-type"]:checked', updateBlock).val();
            if (type == 'manually') {
                newPrice = doRound($('#modal-manually-landed-price').val(), 6);
            } else {
                newPrice = null;
            }
            $('.js-supplier-landed-price-field', supplierBlock).val(newPrice);
            $(document).trigger('suppliers:landed-price-changed', { 'updateBlock': supplierBlock, 'supplierId': supplierId });
        });

        $('#editLandedPrice.modal').on('shown.bs.modal', function(event) {
            $(this).before($('.modal-backdrop'));
            $(this).css("z-index", parseInt($('.modal-backdrop').css('z-index')) + 1);

            var supplierBlock = $(event.relatedTarget).closest('.js-supplier-product');
            var calcLandedPrice = $('.js-supplier-landed-price-gross-displayed', supplierBlock).data('calc-value');
            var fieldLandedPrice = $('.js-supplier-landed-price-field', supplierBlock).val();

            var dialogBlock = event.target;
            $(dialogBlock).data('supplier-block', supplierBlock.attr('id'));
            $("input[name=modal-selected-type][value=" + (fieldLandedPrice? 'manually' : 'calculate') + "]").prop('checked', true);
            $('#modal-calculated-landed-price', dialogBlock).html(calcLandedPrice);
            $('#modal-manually-landed-price', dialogBlock).val(fieldLandedPrice);
        });

        $('.js-supplier-margin, .js-supplier-surcharge, .js-supplier-discount').on('nullable-save', function (event) {
            var supplierBlock = $(event.target).closest('.js-supplier-product');
            var suppliers_id = supplierBlock.data('supplier-id');
            var postData = $('input', supplierBlock).serializeArray();
            postData.push({ name: 'save_products_id', value: {(int)$pInfo->products_id} });
            postData.push({ name: 'save_suppliers_id', value: suppliers_id });

            $.post('{Yii::$app->urlManager->createUrl('categories/save-supplier-fields')}', postData, function(data, status) {
            }, "json");

        });

    });
    //===== Price and Cost END =====//
</script>