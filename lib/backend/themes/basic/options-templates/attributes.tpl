{use class="yii\helpers\Html"}
<div class="page-header">
  <div class="page-title">
    <h3>{$app->controller->view->headingTitle}</h3>
  </div>
</div>
<form action="{Yii::$app->urlManager->createUrl('options-templates/attributes-update')}" method="post" id="options_templates_attributes" name="options_templates_attributes">
{Html::hiddenInput('options_templates_id', $tInfo->options_templates_id)}
{Html::hiddenInput('products_tax_class_id', $products_tax_class_id, ['id'=>'selTaxClass'])}
<div class="attr-box-wrap after">
  <div class="attr-box attr-box-1">
    <div class="widget widget-attr-box box box-no-shadow" style="margin-bottom: 0;">
      <div class="widget-header">
        <h4>{$smarty.const.TAB_ATTRIBUTES}</h4>
        <div class="box-head-serch after">
          <input type="search" value="" id="search-by-attributes" placeholder="{$smarty.const.TAB_SEARCH_ATTR}" class="form-control" />
          <button onclick="return false"></button>
        </div>
      </div>
      <div class="widget-content">
        <select class="attr-tree" size="25" name="attributes" ondblclick="addSelectedAttribute()" style="width: 100%; height: 100%; border: none;" multiple="multiple">
          {foreach $app->controller->view->attributes as $optgroup}
            <optgroup label="{$optgroup['label']}" id="{$optgroup['id']}">
              {foreach $optgroup['options'] as $option}
                <option value="{$option['value']}">{$option['name']}</option>
              {/foreach}
            </optgroup>
          {/foreach}
        </select>
      </div>
    </div>
  </div>
  <div class="attr-box attr-box-2">
    <span class="btn btn-primary" onclick="addSelectedAttribute()"></span>
  </div>
  <div class="attr-box attr-box-3">
    <div class="widget-new widget-attr-box box box-no-shadow" style="margin-bottom: 0;">
      <div class="widget-header">
        <h4>{$smarty.const.TEXT_ASSIGNED_ATTR}</h4>
        <div class="box-head-serch after">
          <input type="search" placeholder="{$smarty.const.TEXT_SEARCH_ASSIGNED_ATTR}" class="form-control" />
          <button onclick="return false"></button>
        </div>
      </div>
      <div class="widget-content " id="selected_attributes_box">
{$attributes=$app->controller->view->selectedAttributes}
{$options_templates_id={$tInfo->options_templates_id} }
{include file='./product-new-option.tpl'}
      </div>
    </div>
  </div>
</div>
<div class="btn-bar">
  <div class="btn-left"><a href="{Yii::$app->urlManager->createUrl(['options-templates/index', 'tID' => $tInfo->options_templates_id])}" class="btn btn-cancel-foot">Cancel</a></div>
  <div class="btn-right"><button class="btn btn-primary">Save</button></div>
</div>
</form>
<script type="text/javascript">
    var tax_rates = new Array();
    {if {$app->controller->view->tax_classes|@count} > 0}
    {foreach $app->controller->view->tax_classes as $tax_class_id => $tax_class}
    tax_rates[{$tax_class_id}] = {\common\helpers\Tax::get_tax_rate_value($tax_class_id)};
    {/foreach}
    {/if}
    var bsPriceParams = {
        onSwitchChange: function (element, argument) {
            var t = $(this).attr('data-toswitch');
            if (typeof(t) != 'undefined') { //all divs, css class of which is starting with t
                tmp = t.split(",");
                if (tmp.length>1) {
                    sel = '[class*="' + tmp.join('"], [class*="') + '"]';
                }else {
                    sel = '[class*="' + t +'"]';
                }
            } else {
                sel = '#div_' + $(this).attr('id');
            }
            var setDefaults = $(this).attr('data-defaults-set');
            if (argument) {
                $(sel).show();
                if (typeof(setDefaults) != 'undefined') {
                    var defval=$(this).attr('data-defaults-on');
                }
            } else {
                $(sel).hide();
                if (typeof(setDefaults) != 'undefined') {
                    var defval=$(this).attr('data-defaults-off');
                }
            }
            if (typeof(setDefaults) != 'undefined' && typeof(defval) != 'undefined') {
                $(setDefaults.split(',')).each(function() {
                    $('#'+this).val(defval);
                });
            }
            var autoPrice = $(this).attr('auto-price');
            if (typeof(autoPrice) != 'undefined') {
                if (argument) {
                    $('#' + autoPrice +'_btn').attr('disabled', 'disabled');
                    chooseSupplierAutoPrice(autoPrice);
                } else {
                    $('#' + autoPrice +'_btn').removeAttr('disabled');
                }
            }
            return true;
        },
        onText: "{$smarty.const.SW_ON}",
        offText: "{$smarty.const.SW_OFF}",
        handleWidth: '20px',
        labelWidth: '24px'
    };

    var ajax_attribute_add_in_progress = 0;
    function updateInventoryBox() {
        if (ajax_attribute_add_in_progress == 0) {
            $('ul[id^="invPrice"] [data-bs-toggle="tab"]').off('shown.bs.tab').on('shown.bs.tab', invPriceTabsShown);
            $('ul[id^="attr_popup"] [data-bs-toggle="tab"]').off('shown.bs.tab').on('shown.bs.tab', invPriceTabsShown);
            $('.js_inventory_group_price input.price-options').off('click').on('click', priceOptionsClick);
        }
    }
  function addSelectedAttribute() {
    var new_products_options_ids = '', assigned_products_options_ids = '', products_options_values_ids = ''; // pass comma separated ids

    $( 'select[name="attributes"] option:selected' ).each(function() {
      var $opt_group = $(this).parent();
      var products_options_id = $opt_group.attr('id');
      var products_options_values_id = $(this).val();
      if ( $( '#products_attributes_id_'+products_options_id+'_'+products_options_values_id ).length ) {
        //already exist
      } else {
        products_options_values_ids += products_options_values_id + ',';
        if ($( "#attr-option-"+products_options_id ).length ) {
          assigned_products_options_ids += products_options_id + ',';
        } else {
          new_products_options_ids += products_options_id + ',';
        }
      }
    });

    if (assigned_products_options_ids != '') {
      ajax_attribute_add_in_progress += 1;
      $.post(
        "{Yii::$app->urlManager->createUrl('options-templates/new-attribute')}",
        { 'options_templates_id': {$tInfo->options_templates_id}, 'products_options_id' : assigned_products_options_ids, 'products_options_values_id' : products_options_values_ids },
        function(dataJ, status, xhr, dataType) {
            if (status == "success") {
                for (i=0;i<dataJ.length; i++) {
                  products_options = dataJ[i]['products_options_id'];
                  products_options_values = dataJ[i]['products_options_values_id'];
                  data = dataJ[i]['data'];
                  var $target_tbody = $(".attr-option-"+products_options+" tbody");
                  var insert_order_locate = ',';
                  $target_tbody.find('input[name^="products_attributes_id\['+products_options+'\]"]').each(function(){
                    var val_id_match = this.name.match(/products_attributes_id\[\d+\]\[(\d+)\]/);
                    if ( val_id_match ) {
                      insert_order_locate = insert_order_locate + val_id_match[1] + ',';
                    }
                  });
                  var after_val_id = '', before_val_id = '', id_pass = false;
                  $('select[name="attributes"] optgroup#' + products_options + ' option').each(function(){
                    if ( before_val_id ) return;
                    if ( this.value==products_options_values ) {
                      id_pass = true;
                    }else
                    if ( insert_order_locate.indexOf(','+this.value+',')!=-1 ){
                      if ( id_pass ) {
                        before_val_id = this.value;
                      }else{
                        after_val_id = this.value;
                      }
                    }
                  });
                  if ( after_val_id ) {
                    $target_tbody.find('input[name^="products_attributes_id\['+products_options+'\]\['+after_val_id+'\]"]').parents('tr[role="row"]').after(data);
                  }else if( before_val_id ) {
                    $target_tbody.find('input[name^="products_attributes_id\['+products_options+'\]\['+before_val_id+'\]"]').parents('tr[role="row"]').before(data);
                  }else {
                    $target_tbody.append(data);
                  }
                  if ( $(".attr-option-"+products_options+" tbody").find('.js-option-value').length>1 ) {
                    $(".attr-option-"+products_options+" tbody").sortable('enable');
                  }
                }
                ajax_attribute_add_in_progress -= 1;
                updateInventoryBox();
            } else {
              alert("Request error.");
            }
        },"json");
    }
          //group not exist product-new-option
    if (new_products_options_ids != '') {
      ajax_attribute_add_in_progress += 1;
      $.post(
        "{Yii::$app->urlManager->createUrl('options-templates/new-option')}",
        { 'options_templates_id': {$tInfo->options_templates_id}, 'products_options_id' : new_products_options_ids, 'products_options_values_id' : products_options_values_ids },
        function(dataJ, status, xhr, dataType) {
            if (status == "success") {
                for (i=0;i<dataJ.length; i++) {
                  products_options = dataJ[i]['products_options_id'];
                  data = dataJ[i]['data'];
                  var insert_order_locate = ',';
                  var $added_options = $("#selected_attributes_box .js-option");
                  $added_options.each(function(){
                    insert_order_locate = insert_order_locate + $(this).attr('data-option_id')+',';
                  });
                  var after_opt_id = '', before_opt_id = '', id_pass = false;
                  $('select[name="attributes"] optgroup').each(function() {
                    if ( before_opt_id ) return;
                    if ( this.id==products_options ) {
                      id_pass = true;
                    }else
                    if ( insert_order_locate.indexOf(','+this.id+',')!=-1 ){
                      if ( id_pass ) {
                        before_opt_id = this.id;
                      }else{
                        after_opt_id = this.id;
                      }
                    }
                  });
                  if ( after_opt_id ) {
                    $added_options.filter('[data-option_id="'+after_opt_id+'"]').after(data);
                  }else if( before_opt_id ) {
                    $added_options.filter('[data-option_id="'+before_opt_id+'"]').before(data);
                  }else {
                    $("#selected_attributes_box").append(data);
                  }
                  if ( $(".attr-option-"+products_options+" tbody").find('.js-option-value').length>1 ) {
                    $(".attr-option-"+products_options+" tbody").sortable('enable');
                  }else{
                      if ($(".attr-option-"+products_options+" tbody").data('uiSortable')) {
                          $(".attr-option-"+products_options+" tbody").sortable('disable');
                      }
                  }
                }
                ajax_attribute_add_in_progress -= 1;
                updateInventoryBox();
            } else {
              alert("Request error.");
            }
      },"json");
      }

    return false;
  }

  function deleteSelectedAttribute(obj) {
    var optionBox = $(obj).parent().parent();
    var option_value_id = $(obj).parents('.js-option-value').attr('data-option_value_id');;
    var option_id = $(obj).parents('.js-option').attr('data-option_id');;
    $(obj).parent().remove();
    var $sort_input = $('input[name="products_option_values_sort_order['+option_id+']"]');
    if ($sort_input.length>0) $sort_input.val($sort_input.val().replace(','+option_value_id+',',','));
    var findtr = $(optionBox).find('tr');
    if (findtr[0] == undefined) {
      $(optionBox).parent().parent().parent().remove();
    }
    if ( $(".attr-option-"+option_id +" tbody").find('.js-option-value').length==1 ) {
      $(".attr-option-"+option_id +" tbody").sortable('disable');
    }

    return false;
  }


    function invPriceTabsShown(clicked='') {
        var el = $(this).attr('href');
        if (typeof(el) === 'undefined' && clicked !== '') {
            el = clicked;
        }
        updateVisibleGrossInputs($(el));

        // init new visible bootstrapSwitch
        tab = $(el).not(".inited");
        if (tab.length) {
            tab.addClass('inited');

            $('.check_qty_discount_prod:visible, .check_supplier_price_mode:visible, .attr_file_switch:visible', tab).bootstrapSwitch({
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
                },
                onText: "{$smarty.const.SW_ON}",
                offText: "{$smarty.const.SW_OFF}",
                handleWidth: '20px',
                labelWidth: '24px'
            });
        }
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

  var color = '#ff0000';
  var athighlight = function(obj, reg){
    if (reg.length == 0) return;
    $(obj).html($(obj).text().replace( new RegExp( "(" +  reg  + ")" , 'gi' ), '<font style="color:'+color+'">$1</font>'));
    return;
  }
  var atunhighlight = function(obj){
    $(obj).html($(obj).text());
  }
  var atsearch = null;
  var atstarted = false;
  $(document).ready(function() {
    $('#search-by-attributes').on('focus keyup', function(e){
      $('select[name="attributes"]').find('option').parent().hide();
      if ($(this).val().length == 0){
        atstarted = false;
      }
      if (!atstarted && e.type == 'focus'){
        $('select[name="attributes"]').find('option').show();
        $('select[name="attributes"]').find('option').parent().show();
      }
      atstarted = true;
      var str = $(this).val();
      atsearch = new RegExp(str, 'i');
      $.each($('select[name="attributes"]').find('option'), function(i, e){
        atunhighlight(e);
        if (!atsearch.test($(e).text())){
          $(e).hide();
        } else {
          $(e).show();
          $(e).parent().show();
          athighlight(e, str);
        }
      });
    });
  });

  function currencyFormat(num, id=0) {

      if (!(parseInt(id)>0)) {
          id={$default_currency['id']|json_encode};
      }


      var sep_th_a = { {$default_currency['id']|json_encode}:{$default_currency['thousands_point']|json_encode}{foreach $app->controller->view->currenciesTabs as $c},{$c['id']|json_encode}:{$c['thousands_point']|json_encode}{/foreach} };
      var sep_dec_a = { {$default_currency['id']|json_encode}:{$default_currency['decimal_point']|json_encode}{foreach $app->controller->view->currenciesTabs as $c},{$c['id']|json_encode}:{$c['decimal_point']|json_encode}{/foreach} };
      var symbol_right_a = { {$default_currency['id']|json_encode}:{$default_currency['symbol_right']|json_encode}{foreach $app->controller->view->currenciesTabs as $c},{$c['id']|json_encode}:{$c['symbol_right']|json_encode}{/foreach} };
      var symbol_left_a = { {$default_currency['id']|json_encode}:{$default_currency['symbol_left']|json_encode}{foreach $app->controller->view->currenciesTabs as $c},{$c['id']|json_encode}:{$c['symbol_left']|json_encode}{/foreach} };
      var decimal_places_a = { {$default_currency['id']|json_encode}:{$default_currency['decimal_places']|json_encode}{foreach $app->controller->view->currenciesTabs as $c},{$c['id']|json_encode}:{$c['decimal_places']|json_encode}{/foreach} };

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

  function doRound(x, places) {
      return Math.round(x * Math.pow(10, places)) / Math.pow(10, places);
  }

  function getTaxRate()
  {
      var parameterVal = $('#selTaxClass').val();
      if ( (parameterVal > 0) && (tax_rates[parameterVal] > 0) ) {
          return tax_rates[parameterVal];
      } else {
          return 0;
      }
  }

  if ( typeof window.unformatMaskField !== 'function' ){
      window.unformatMaskField = function(selector) {
          var result = $(selector).val();
          return result;
      }
  }


    function updateGrossPrice(el) {
      var taxRate = getTaxRate(el);
      var roundTo = 6;
      $(el).focus();
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

      var grossValue = parseFloat(el.value) || 0; // net value by default
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
      /// update all visible gross price (on change tax class)
      /// inputs (visible) + lists (all)
      if ( !uprid ) {
          updateVisibleGrossInputs();
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
        $('input.price-options[id^="' + id.replace(/\d$/, '') + '"]').not('[id="' + id + '"]').prop("checked", false);
        var mainPriceSwitched = id.match(/_m\d$/); //not special
        var isInventory = id.match(/^iop/);
        var val = $(this).val(),
            id_suffix = $(this).attr('data-idsuffix'),
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
                base_price = parseFloat($('#special_price' + base_suffix).val());
                if (base_price<=0) {
                    base_price = $('#group_price_container' + id_suffix).attr('data-base_special_price');
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

                $('.check_sale_prod:visible, .check_points_prod:visible, .check_supplier_price_mode:visible, .check_qty_discount_prod:visible, .check_gift_wrap:visible, .check_shipping_surcharge:visible', tab).bootstrapSwitch(bsPriceParams);
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

            if (parseFloat($('#' + toshow[0] + id_suffix).val())<0) {
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
                _vals[this.name] = this.checked;
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
                            try {
                                if ($(this).parent().is('div.bootstrap-switch-container'))
                                    $(this).bootstrapSwitch('state', _vals[this.name]);
                            } catch (err) { }
                            this.checked = _vals[this.name];
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

    $(document).ready(function(){
      $('input.price-options').off('click').on('click', priceOptionsClick);

      $('.js_group_price').on('change_state',function(event, state){
          //deprecated
          //!!! was recalculate prices (gross/net) for each group js_group_price => div includes base or special prices (switcher + inputs)
          //for particular group+currency  ;)
          //
          var $block = $(this);
          var $all_input = $block.find('[name^="products_groups_prices_"]');
          var base_ref = '#base_price';
          if ( $all_input.length==0 ) {
              $all_input = $block.find('[name^="special_groups_prices_"]');
              base_ref = '#base_sale_price';
          }
          var $main_input = $block.find('.js_price_input');
          //
          var base_val = parseFloat($(base_ref).val()) || 0;
          if ( base_ref=='#base_sale_price' && $(base_ref).val().indexOf('%')!==-1 ) {
              var main_price = $('#base_price').val(),
                  base_percent = parseFloat($(base_ref).val().substring(0,$(base_ref).val().indexOf('%')));
              base_val = main_price - ((base_percent/100)*main_price);
          }
          var new_val = ((100-parseFloat($block.attr('data-group_discount')))/100*base_val);

          //dependant block (specials, points, q-ty discount
          var $dep_block = $block.closest('.tab-pane').find('.js_price_dep');

          if (base_ref == '#base_sale_price') $dep_block = $([]);
          if ( parseFloat(state)==-1 ) {
              $all_input.removeAttr('readonly');
              $all_input.removeAttr('disabled');
              $main_input.val('-1');
              $block.find('.js_price_block').hide();
              $dep_block.hide();
          }else if(parseFloat(state)==-2){
              if ( $dep_block.is(':hidden') ) $dep_block.show();
              $all_input.removeAttr('readonly');
              $all_input.removeAttr('disabled');
              $main_input.val(new_val);
              $main_input.trigger('keyup');
              $all_input.attr({ readonly:'readonly',disabled:'disabled' });
              $block.find('.js_price_block').show();
          }else{
              if ( $dep_block.is(':hidden') ) $dep_block.show();
              $all_input.removeAttr('readonly');
              $all_input.removeAttr('disabled');
              if ( parseFloat($main_input.val())<=0 ) {
                  $main_input.val(new_val);
                  $main_input.trigger('keyup');
              }
              $block.find('.js_price_block').show();
          }
      });

      $('.js_group_price [name^="popt_"]').on('click',function(){
          $(this).parents('.js_group_price').trigger('change_state',[$(this).val()]);
          if ( parseFloat($(this).val()) ==-1) {
              $('.js_group_price').find('[name^="s'+this.name+'"]').filter('[value="-1"]').trigger('click');
          }
      });
      $('.js_group_price [name^="spopt_"]').on('click',function(){
          $(this).parents('.js_group_price').trigger('change_state',[$(this).val()]);
      });
  });
</script>