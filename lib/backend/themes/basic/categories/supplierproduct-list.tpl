    <div id="suppliers-placeholder{(int)$pInfo->products_id}">
        {if isset($app->controller->view->suppliers) && $app->controller->view->suppliers|@count > 0}
          {assign var = cMap value = \yii\helpers\ArrayHelper::map($currencies->currencies, 'id', 'title')}
          {foreach $app->controller->view->suppliers as $suppliers_id => $supplier}
            {include file="supplierproduct.tpl" sInfo=$supplier cMap = $cMap}
          {/foreach}
        {/if}
    </div>

    <script>

    // -- new supplier calc
  var productCategories = {$pInfo->current_assigned_categories|json_encode};

  function onSupplierCalculateArrive(serverData) {
      if (!serverData.data) return;

      var taxRate = getTaxRate();

      var $_mainPriceInput = $("input.default_price[name^='products_group_price'");
      var ourNetPrice = $_mainPriceInput.val();
      if (typeof unformatMaskField === 'function'){
          ourNetPrice = unformatMaskField($_mainPriceInput);
      }
      ourNetPrice = doRound(ourNetPrice, 6);
      var mainNetPrice = ourNetPrice;

      var countInventorySuppliers = { };

      for( var supIndex in serverData.data ) {
          if ( !serverData.data.hasOwnProperty(supIndex) ) continue;
          var priceInfo = serverData.data[supIndex];

          var $root = $('#'+priceInfo.htmlId),
              idPart = '' + $root.data('id-part');

          var RESULT_PRICE = 0,
              TAX_RATE = 0,
              price_with_tax = true,
              APPLIED_DISCOUNT = 0,
              SUPPLIER_COST = 0;

          if (!priceInfo.result) {
              $('.js-applied-rule', $root).html(typeof priceInfo.error !== 'undefined'?priceInfo.error:'');
              $('.js-applied-formula', $root).html('');
          }else{
              // all good
              $('.js-applied-rule', $root).html(priceInfo.result.label);
              $('.js-applied-formula', $root).html(priceInfo.result.formula.text);
              $('.js-supplier-surcharge',$root).attr('placeholder',priceInfo.result.SURCHARGE);
              $('.js-supplier-discount',$root).attr('placeholder',priceInfo.result.DISCOUNT);
              $('.js-supplier-margin',$root).attr('placeholder',priceInfo.result.MARGIN);

              RESULT_PRICE = priceInfo.result.resultPrice;
              TAX_RATE = parseFloat(priceInfo.result.applyParams.tax_rate);
              price_with_tax = priceInfo.result.applyParams.price_with_tax;
              APPLIED_DISCOUNT = priceInfo.result.applyParams.DISCOUNT;
              SUPPLIER_COST = priceInfo.result.applyParams.PRICE;
          }

          if ( idPart && idPart.indexOf('_')!==-1 ){
              var upridPart = idPart.substring(0,idPart.indexOf('_'));
              countInventorySuppliers[upridPart] = upridPart;

              if (typeof unformatMaskField === 'function'){
                  ourNetPrice = unformatMaskField('input.default_currency[id^="products_group_price-' + upridPart + '"]:first');
              }else{
                  ourNetPrice = $('input.default_currency[id^="products_group_price-' + upridPart + '"]:first').val();
              }

              ourNetPrice = doRound(ourNetPrice, 6);
              if ($('#full_add_price').val() !== '1') {
                  if ($('select.default_currency[id^="invPricePrefix-' + upridPart + '"]:first').val() === '+') {
                      ourNetPrice = mainNetPrice + ourNetPrice;
                  } else {
                      ourNetPrice = mainNetPrice - ourNetPrice;
                  }
              }
          }

          var supplierPrice = SUPPLIER_COST;
          var supplierCostPrice = doRound(supplierPrice * (1 - APPLIED_DISCOUNT / 100), 6);
          var supplierNetCostPrice = supplierCostPrice;
          if ( TAX_RATE>0 ) {
              supplierNetCostPrice = doRound(supplierCostPrice * 100 / (100 + TAX_RATE), 6);
          }

          var calcNetPrice = RESULT_PRICE;

          var calcGrossPrice = calcNetPrice * ((taxRate / 100) + 1);
          var calcProfit = calcNetPrice - supplierCostPrice;

          var ourGrossPrice = ourNetPrice * ((taxRate / 100) + 1);
          var ourProfit = ourNetPrice - supplierCostPrice;

          $('#supplier_price_' + idPart).html(currencyFormat(supplierPrice));
          $('#supplier_cost_price_net_' + idPart).html(currencyFormat(supplierNetCostPrice));
          $('#supplier_cost_price_gross_' + idPart).html(currencyFormat(supplierCostPrice));

          $('#calc_net_price_' + idPart).html(currencyFormat(calcNetPrice));
          $('#calc_gross_price_' + idPart).html(currencyFormat(calcGrossPrice));
          $('#calc_profit_' + idPart).html(currencyFormat(calcProfit));

          $('#our_net_price_' + idPart).html(currencyFormat(ourNetPrice));
          $('#our_gross_price_' + idPart).html(currencyFormat(ourGrossPrice));
          $('#our_profit_' + idPart).html(currencyFormat(ourProfit));

          /*
          if (calcNetPrice==0 || doRound(ourNetPrice, 2) === doRound(calcNetPrice, 2)) {
              $('#calc_div_' + idPart).hide();
              $('.calc_div_width_' + idPart).addClass('tab-sup03-width-full');
          } else {
              $('#calc_div_' + idPart).show();
              $('.calc_div_width_' + idPart).removeClass('tab-sup03-width-full');
          }
          */
      }

      var inventoryProcessed = false;
      for( var invPart in countInventorySuppliers ) {
          if ( !countInventorySuppliers.hasOwnProperty(invPart) ) continue;
          inventoryProcessed = true;
          var root = $('#id-'+invPart);
          getCountSuppliersPricesInv(root)
      }
      if ( !inventoryProcessed ) {
          getCountSuppliersPrices();
      }
  }

  var supplierQueueClass = function(){
      var _self = this;
      var $xhr = null;
      this.queue = { };

      this.timer = 0;

      this.onDataArrive = false;

      this.push = function(data, ms){
          this.queue[data.supplier_id] = data;

          clearTimeout (this.timer);
          this.timer = setTimeout(function(){ _self.onTimeout(_self) }, ms || 300);
      };

      this.onTimeout = function () {
          if($xhr && $xhr.readyState != 4) {
              $xhr.abort();
          }

          $xhr = $.ajax({
              url: '{Yii::$app->urlManager->createUrl('categories/calculate-supplier-price')}',
              data: { queue: this.queue },
              method: 'post',
              dataType: 'json',
              success: function (data) {
                  if ( typeof _self.onDataArrive === 'function' ) {
                      _self.onDataArrive(data);
                  }else {
                      onSupplierCalculateArrive(data);
                  }
              }
          });
          this.queue = { };
      };
  };

  var supplierQueue = new supplierQueueClass();

  $(document).ready(function(){
      $('#save_product_form').on('keyup change','.js-supplier-product',function(event){
          var $target = $(event.target);
          if (!$target.hasClass('js-supplier-product') && !$target.hasClass('js-supplier-recalc')) return;
          var $updatedBlock = $(event.currentTarget);
          supplierQueue.push({
              //$updatedBlock
              htmlId: $updatedBlock.attr('id'),
              supplier_id: $updatedBlock.data('supplier-id'),
              products_id: 0,
              categories_id: productCategories,
              manufacturers_id: $('#save_product_form .js-product-manufacturer').val(),
              currencies_id : $('.js-supplier-currency',$updatedBlock).val(),
              PRICE: $('.js-supplier-cost',$updatedBlock).val(),
              MARGIN: $('.js-supplier-margin',$updatedBlock).not('[disabled]').textInputNullableValue(),
              SURCHARGE: $('.js-supplier-surcharge',$updatedBlock).not('[disabled]').textInputNullableValue(),
              DISCOUNT: $('.js-supplier-discount',$updatedBlock).not('[disabled]').textInputNullableValue(),
              tax_rate: $('.js-supplier-tax-rate',$updatedBlock).not('[disabled]').textInputNullableValue(),
              price_with_tax: $('.js-supplier-tax-rate-flag',$updatedBlock).not('[disabled]').get(0).checked?1:0
          });
      });

      $('.js-supplier-product').trigger('change');

      $('.js-product-manufacturer').on('change',function(){
          $('.js-supplier-product').trigger('change');
      });


  });

  // -- new supplier calc
    function deleteSupplier(id) {
        $.post('{Yii::$app->urlManager->createUrl('categories/check-supplier-delete')}', { pId: '{$pInfo->products_id}', sId: id }, function(response, status) {
            if (status == "success") {
                if (response.status == 'ok') {
                    $('#suppliers{$pInfo->products_id}-' + id).remove();
                    getCountSuppliersPrices();
                    if (!countActiveSuppliers) {
                        $('.supplier-product-status:first').bootstrapSwitch('state', true);
                    }
                }
                if (response.message && response.message != '') {
                    alert(response.message);
                }
            }
        }, 'json');
        return false;
    }

  var countSuppliersPrices = 0;
  var countActiveSuppliers = 0;
  function getCountSuppliersPrices() {
    countSuppliersPrices = 0;
    countActiveSuppliers = 0;
    $('[name^="suppliers_id["]:not([readonly])').each(function(i, e) {
        var supplierPrice = 0;
        if ( document.forms['product_edit'].elements['suppliers_data[{$pInfo->products_id}][' + e.value + '][suppliers_price]'] ) {
            supplierPrice = doRound(document.forms['product_edit'].elements['suppliers_data[{$pInfo->products_id}][' + e.value + '][suppliers_price]'].value, 6);
        }
      countActiveSuppliers++;
      if (supplierPrice > 0) {
        countSuppliersPrices++;
      }
    });
    if (countSuppliersPrices > 0) {
      $('#tab_1_3 .supplier-price-cost').removeClass('disable-btn');
    } else {
      $('#tab_1_3 .supplier-price-cost').addClass('disable-btn');
    }
  }

  function getCountSuppliersPricesInv(inventoryPartId) {
      if ( typeof inventoryPartId === 'object') {
          var $root = inventoryPartId;
      }else{
          var $root = $('#id-'+inventoryPartId);
      }

      var activeCount = 0;
      $('.js-supplier-product', $root).each(function(){
          var $costInput = $(this).find('.js-supplier-cost:not([readonly])');
          if ( $costInput.length>0 && doRound($costInput.val(),6)>0 ) activeCount++;
      });
      if (activeCount > 0) {
          $('.supplier-price-cost',$root).removeClass('disable-btn');
      } else {
          $('.supplier-price-cost',$root).addClass('disable-btn');
      }
      return activeCount;
  }

  function chooseSupplierAutoPrice(target_id) {
      var supplierCount = countSuppliersPrices;
      var isInventory = target_id.indexOf('-')!==-1;
      var supplierInventoryUprid = '';
      if ( isInventory ) {
          var $targetInput = $('#'+target_id);
          var $popupInventory = $targetInput.parents('.popup-box-wrap-page');
          var supplierCostInputName = $('.js-supplier-cost',$popupInventory).get(0).name;
          supplierInventoryUprid = supplierCostInputName.substring(supplierCostInputName.indexOf('[')+1,supplierCostInputName.indexOf(']',supplierCostInputName.indexOf('[')));
          supplierCount = getCountSuppliersPricesInv( $popupInventory );
      }
      if ( supplierCount> 1) {
        $.post('{Yii::$app->urlManager->createUrl('categories/auto-supplier-price')}?tID=' + target_id+'&inventoryUprid='+supplierInventoryUprid, $('#save_product_form').serialize(), function(data, status) {
          if (status == "success") {
              if(data.id > 0) {
                selectSupplierPrice(data.id, target_id);
              }
          } else {
            alert("Request error.");
          }
        },"json");
        return false;
      }
  }

  function chooseSupplierPrice(target_id) {
      var supplierCount = countSuppliersPrices;
      var isInventory = target_id.indexOf('-')!==-1;
      var supplierInventoryUprid = '';
      if ( isInventory ) {
          var $targetInput = $('#'+target_id);
          var $popupInventory = $targetInput.parents('.popup-box-wrap-page');
          var supplierCostInputName = $('.js-supplier-cost',$popupInventory).get(0).name;
          supplierInventoryUprid = supplierCostInputName.substring(supplierCostInputName.indexOf('[')+1,supplierCostInputName.indexOf(']',supplierCostInputName.indexOf('[')));
          supplierCount = getCountSuppliersPricesInv( $popupInventory );
      }
    if ( supplierCount> 1) {
      $.post('{Yii::$app->urlManager->createUrl('categories/supplier-price')}?tID=' + target_id+'&inventoryUprid='+supplierInventoryUprid, $('#save_product_form').serialize(), function(data, status) {
        if (status == "success") {
            $('body').append("<div class='popup-box-wrap js-supplier-select-popup' style='top:200px;'><div class='around-pop-up'></div><div class='popup-box popupEditCat'><div class='popup-heading cat-head'>{$smarty.const.TEXT_SELECT_PRICE_FROM_SUPPLIER}</div><div class='pop-up-close'></div><div class='pop-up-content'><div class='popupCategory'>" + data + "</div></div></div></div>");
            var $supPopUp = $('.js-supplier-select-popup');
            $supPopUp.css('top', (window.scrollY + 200) + 'px');
            $('.pop-up-close',$supPopUp).click(function(){
                $('.js-supplier-select-popup .popup-box:last').trigger('popup.close');
                $('.js-supplier-select-popup.popup-box-wrap:last').remove();
                return false
            });

        } else {
          alert("Request error.");
        }
      },"html");
      return false;
    } else {
        var supplierPriceInputs = $('#suppliers-placeholder{(int)$pInfo->products_id} .js-supplier-cost').not('[readonly]');
        if ( isInventory ) {
            // inventory
            supplierPriceInputs = $('#'+target_id).parents('.popup-box-wrap-page').find('.js-supplier-cost').not('[readonly]');
        }
        for(var __idx=0; __idx<supplierPriceInputs.length; __idx++ ) {
            supplierPriceInput = supplierPriceInputs[__idx];
            var supplierPrice = doRound(supplierPriceInput.value, 6);
            if (supplierPrice > 0) {
              selectSupplierPrice($(supplierPriceInput).parents('.js-supplier-product').data('supplier-id'), target_id);
              break;
            }
        }
    }
  }

  var supplierSelect = new supplierQueueClass();
  function selectSupplierPrice(id, target_id) {
      var supplierPriceInput = document.forms['product_edit'].elements['suppliers_data[{$pInfo->products_id}][' + id + '][suppliers_price]'];
      if ( !supplierPriceInput ) {
          // inventory
          supplierPriceInput = $('#'+target_id).parents('.popup-box-wrap-page').find('.js-supplier-cost').get(0);
      }
      var supplierPrice = doRound(supplierPriceInput.value, 6);
      if (supplierPrice > 0) {
          supplierSelect.onDataArrive = function (serverData) {
              if (serverData.data && serverData.data[id] && serverData.data[id].result) {
                  var priceCalculated = serverData.data[id];
                  if (typeof(target_id) == "object") {
                      $(target_id).val(doRound(priceCalculated.result.resultPrice, 6)).keyup();
                  } else {
                      $('#' + target_id).val(doRound(priceCalculated.result.resultPrice, 6)).keyup();
                  }
              }
          };

          var $updatedBlock = $(supplierPriceInput).parents('.js-supplier-product');
          supplierSelect.push({
              //$updatedBlock
              htmlId: $updatedBlock.attr('id'),
              supplier_id: $updatedBlock.data('supplier-id'),
              products_id: 0,
              categories_id: productCategories,
              manufacturers_id: $('#save_product_form .js-product-manufacturer').val(),
              currencies_id : $('.js-supplier-currency',$updatedBlock).val(),
              PRICE: $('.js-supplier-cost',$updatedBlock).val(),
              MARGIN: $('.js-supplier-margin',$updatedBlock).not('[disabled]').textInputNullableValue(),
              SURCHARGE: $('.js-supplier-surcharge',$updatedBlock).not('[disabled]').textInputNullableValue(),
              DISCOUNT: $('.js-supplier-discount',$updatedBlock).not('[disabled]').textInputNullableValue()
          },0);
    }
  }

  function deleteSupplierInv(id, uprid) {
    $('#suppliers' + uprid + '-' + id).remove();
    if ($('.popup-content:visible [name^="suppliers_id_"]:not([readonly])').size() == 0){
        $('.popup-content:visible .supplier-product-status:first').bootstrapSwitch('state', true);
    }
    getCountSuppliersPricesInv(uprid);
  }

    if (typeof changeSPStatus != 'function'){
      changeSPStatus = function(target, status) {
        $.each($(target).parents('.js-supplier-product').find('input, select'), function(i, el){
            $(el).attr('readonly', (status?false:'readonly'));
            if($(el).is('select')){
                $(el).attr('disabled', (status?false:'disabled'));
            }
            if ($(el).next().hasClass('js-input-nullable-btn')){
                $(el).next().css('visibility', (status?'':'hidden'));
            }
        });
      }
    }

    initBTSattus = function($items){
        $($items).bootstrapSwitch({
              onText: "{$smarty.const.SW_ON}",
              offText: "{$smarty.const.SW_OFF}",
              handleWidth: '20px',
              labelWidth: '24px',
              onSwitchChange: function (e, status) {
                    changeSPStatus(e.target, status);
                    getCountSuppliersPrices();
                    if (!countActiveSuppliers){
                        changeSPStatus(e.target, true);
                        $(this).bootstrapSwitch('state', true);
                    }
              }
          });
    }

    $(document).ready(function(){
        initBTSattus($('.supplier-product-status'));
    })
    </script>