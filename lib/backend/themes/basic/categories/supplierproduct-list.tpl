    <div id="suppliers-placeholder{(int)$pInfo->products_id}">
        {if isset($app->controller->view->suppliers) && $app->controller->view->suppliers|default:array()|@count > 0}
          {assign var = cMap value = \yii\helpers\ArrayHelper::map($currencies->currencies, 'id', 'title')}
          {foreach $app->controller->view->suppliers as $suppliers_id => $supplier}
            {include file="supplierproduct.tpl" sInfo=$supplier cMap = $cMap}
          {/foreach}
        {/if}
    </div>

    <script>

    // -- new supplier calc
  var productCategories = {$pInfo->current_assigned_categories|json_encode};

    function getSupplierLandedOrCalcField(supplierBlock) {
        return $('.js-supplier-landed-price-gross-displayed', supplierBlock);
    }

    function getSupplierCostField(supplierBlock) {
        return $('.js-supplier-cost', supplierBlock);
    }

    function getSupplierLandedPriceField(supplierBlock) {
        return $('.js-supplier-landed-price-field', supplierBlock);
    }

    function getConvertedWithCurrencyRisk(field, val = undefined) {
        if (val == undefined) {
            var converted = field.data('converted');
            return converted ? converted : field.val();
        } else {
            field.data('converted', val);
        }
    }

    function getPushedObj($updatedBlock) {
        supplierId = $updatedBlock.data('supplier-id');
        if (supplierId == null) return;
        defValueIsUndefined = !$suppliersIdWithDefValues.includes(parseInt(supplierId));

        return {
            htmlId: $updatedBlock.attr('id'),
            supplier_id: $updatedBlock.data('supplier-id'),
            products_id: 0,
            categories_id: productCategories,
            manufacturers_id: $('#save_product_form .js-product-manufacturer').val(),
            currencies_id : $('.js-supplier-currency',$updatedBlock).val(),
            PRICE: getSupplierCostField($updatedBlock).val(),
            MARGIN: getControlValue(defValueIsUndefined, $('.js-supplier-margin',$updatedBlock)),
            SURCHARGE: getControlValue(defValueIsUndefined, $('.js-supplier-surcharge',$updatedBlock)),
            DISCOUNT: getControlValue(defValueIsUndefined, $('.js-supplier-discount',$updatedBlock)),
            LANDED: getSupplierLandedPriceField($updatedBlock).val(),
            tax_rate: $('.js-supplier-tax-rate',$updatedBlock).textInputNullableValue(),
            price_with_tax: $('.js-supplier-tax-rate-flag',$updatedBlock).get(0).checked?1:0
        }
    }

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
          var supplierId = parseInt(supIndex);

          var $root = $('#'+priceInfo.htmlId),
              idPart = '' + $root.data('id-part');

          var RESULT_PRICE = 0,
              TAX_RATE = 0,
              price_with_tax = true,
              APPLIED_DISCOUNT = 0,
              SUPPLIER_COST = 0,
              LANDED = 0;

          if (!priceInfo.result) {
              $('.js-applied-rule', $root).html(typeof priceInfo.error !== 'undefined'?priceInfo.error:'');
              $('.js-applied-formula', $root).html('');
          }else{
              // all good
              $('.js-applied-rule', $root).html(priceInfo.result.label);
              $('.js-applied-formula', $root).html(priceInfo.result.formula.text);
              $('.js-supplier-surcharge',$root).attr('placeholder',priceInfo.result.SURCHARGE).trigger('update-state');
              $('.js-supplier-discount',$root).attr('placeholder',priceInfo.result.DISCOUNT).trigger('update-state');
              $('.js-supplier-margin',$root).attr('placeholder',priceInfo.result.MARGIN).trigger('update-state');
              if (!$suppliersIdWithDefValues.includes(supplierId)) $suppliersIdWithDefValues.push(supplierId);

              RESULT_PRICE = priceInfo.result.resultPrice;
              TAX_RATE = parseFloat(priceInfo.result.applyParams.tax_rate);
              price_with_tax = priceInfo.result.applyParams.price_with_tax;
              APPLIED_DISCOUNT = priceInfo.result.applyParams.DISCOUNT;
              SUPPLIER_COST = priceInfo.result.SUPPLIER_COST;
              LANDED = priceInfo.result.applyParams.LANDED;

              $('#calc_net_price_' + idPart).html(currencyFormat(RESULT_PRICE));
              $('#calc_net_price_' + idPart).data('value', RESULT_PRICE);
              getConvertedWithCurrencyRisk( getSupplierCostField($root), SUPPLIER_COST);
              getConvertedWithCurrencyRisk( getSupplierLandedPriceField($root), LANDED);

              $(document).trigger('suppliers:calc-price-changed', { 'updateBlock': $root, 'supplierId': supIndex});
          }
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

      this.push = function(data, ms = null){
          this.queue[data.supplier_id] = data;

          clearTimeout (this.timer);
          if (ms == null) ms = 200;
          this.timer = setTimeout(function(){ _self.onTimeout(_self) }, ms);
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
  let $suppliersIdWithDefValues = [];

  function getControlValue(defValueIsUndefined, field) {
      value = field.textInputNullableValue();
      ret = (defValueIsUndefined && value == '0')? null : value;
      return ret;
  }

  function updateSupplierAt($updatedBlock, $target = null) {
      if ($target != null && !$target.hasClass('js-supplier-product') && !$target.hasClass('js-supplier-recalc')) return;
      supplierQueue.push(getPushedObj($updatedBlock));
  }

  function updateSupplierAll() {
      $('#save_product_form .js-supplier-product').each(function () {
          updateSupplierAt($(this));
      });
  }

  $(document).ready(function(){
      $('#save_product_form').on('keyup change','.js-supplier-product',function(event){
          updateSupplierAt($(event.currentTarget), $(event.target));
      });

      $('.js-supplier-product').trigger('change');

      $('.js-product-manufacturer').on('change',function(){
          $('.js-supplier-product').trigger('change');
      });

      //supliers_stock change
        $('.supplier-qty.js-input-nullable-save').not(".inited").on('click', function(e) {
            var $holder = $(this).parents('.input-group'); //parent();
            var val = $('input.supplier-qty', $holder).val();
            var defVal = $('.js-input-nullable-default-val', $holder).text();
            try {
                if (!isNaN(parseInt(val)) && parseInt(val) == parseInt(defVal)) {
                    return true;
                }
            } catch ( e ) { }
            
            e.preventDefault();

            if (isNaN(parseInt(val)) ) {
                $('input.supplier-qty', $holder).css('color', 'var(--color-danger)');
                $('input.supplier-qty', $holder).once('keydown', function(){
                    $(this).css('color', 'inherit')
                });
                return false;
            }

            $.post('{Yii::$app->urlManager->createUrl('categories/set-suppliers-stock')}', $('input', $holder).serialize(), function(data, status) {
                if (status == "success") {
                    if (typeof(data.value) != 'undefined') {
                        $('.js-input-nullable-default-val', $holder).text(data.value);
                        $('input.supplier-qty', $holder).attr('placeholder', data.value);
                        $('input.supplier-qty', $holder).val(data.value);
                        $('input.js-input-nullable-close', $holder).click();
                        $('.stock-info-reload a').click(); //.popup-content  for inventory 
                    }
                } else {
                    alert("Request error.");
                }
            }, "json");
            $('.supplier-qty.js-input-nullable-save').addClass("inited");


        });
      //supliers_stock eof


  });

  // -- new supplier calc
    function deleteSupplier(id) {
        bootbox.dialog({
            message: '<div class="align-center">{\common\helpers\Translation::getTranslationValue("TEXT_DELETE_INTRO", "admin/suppliers")}</div>',
            //title: '',
            buttons: {
                success: {
                    label: "{$smarty.const.TEXT_BTN_YES}",
                    className: "btn-delete",
                    callback: function () {
                        $.post('{Yii::$app->urlManager->createUrl('categories/check-supplier-delete')}', { pId: '{$pInfo->products_id}', sId: id }, function(response, status) {
                            if (status == "success") {
                                if (response.status == 'ok') {
                                    $('#suppliers{$pInfo->products_id}-' + id).remove();
                                    getCountSuppliersPrices();
                                    if (!countActiveSuppliers) {
                                        $('.supplier-product-status:first').bootstrapSwitch('state', true);
                                    }
                                    $(document).trigger('suppliers:deleted', [{ 'supplierId': id}]);
                                }
                                if (response.message && response.message != '') {
                                    alert(response.message);
                                }
                            }
                        }, 'json');
                    }
                },
                cancel: {
                    label: "{$smarty.const.TEXT_BTN_NO}",
                    className: "btn-cancel",
                }
            }
        });
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
      if (supplierPrice >= 0) {
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

  function getSupplierPriceInputs(target_id = '') {
      var supplierPriceInputs = $('#suppliers-placeholder{(int)$pInfo->products_id} .js-supplier-cost').not('[readonly]');
      var isInventory = target_id.indexOf('-')!==-1;
      if ( isInventory ) {
          // inventory
          supplierPriceInputs = $('#'+target_id).parents('.popup-box-wrap-page').find('.js-supplier-cost').not('[readonly]');
      }
      if (supplierPriceInputs.length < 1) {
          connsole.log("Can't find price input for supplier: " + target_id);
      }
      return supplierPriceInputs;
  }

  function getSupplierPriceInput(suppliersId, target_id = '') {
      return $('#suppliers{(int)$pInfo->products_id}-' + suppliersId).find('.js-supplier-cost');
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
        var supplierPriceInputs = getSupplierPriceInputs(target_id);
        for(var __idx=0; __idx<supplierPriceInputs.length; __idx++ ) {
            supplierPriceInput = supplierPriceInputs[__idx];
            var supplierPrice = doRound(supplierPriceInput.value, 6);
            if (supplierPrice >= 0) {
              selectSupplierPrice($(supplierPriceInput).parents('.js-supplier-product').data('supplier-id'), target_id);
              var supplierName =  $(supplierPriceInput).parents('.js-supplier-product').find('.widget-header h4').clone().children().remove().end().text().trim();
              //alert('Price will be changed to price from supplier "' +supplierName+ '"');
              break;
            }
        }
    }
  }

  var undoPrice = null;
  function clickUndoPriceBtn(target) {
      if (undoPrice != null) {
          $('input.default_price').filter(function() {
              return this.id.match(/products_group_price.*/);
          }).val(undoPrice).change().keyup();
          undoPrice = null;
          $(document).trigger('suppliers:group-price-changed');
      }
      $(target).hide();
  }

  var supplierSelect = new supplierQueueClass();
  function selectSupplierPrice(id, target_id) {
      var supplierPriceInput = getSupplierPriceInput(id, target_id)[0];
      var supplierPrice = doRound(supplierPriceInput.value, 6);
      if (supplierPrice >= 0) {
          supplierSelect.onDataArrive = function (serverData) {
              if (serverData.data && serverData.data[id] && serverData.data[id].result) {
                  var priceCalculated = serverData.data[id];
                  var price = doRound(priceCalculated.result.resultPrice, 6);
                  console.log(price)
                  if (typeof(target_id) == "object") {
                      var prev = parseFloat($(target_id).val().replace(/[^(\d+)\.(\d+)]/g, '')) || 0;
                      $(target_id).val(price).keyup();
                  } else {
                      var prev = parseFloat($('#' + target_id).val().replace(/[^(\d+)\.(\d+)]/g, '')) || 0;
                      $('#' + target_id).val(price).keyup();
                  }
                  if (prev == price) {
                      alert('{$smarty.const.TEXT_SUPPLIER_SET_PRICE_ERR_SAME}');
                  } else {
                      undoPrice = prev;
                      $('#products_group_price_undo_btn').show();
                      $(document).trigger('suppliers:group-price-changed');
                  }
              }
              $(document).trigger('suppliers:selected-manually', { 'suppliers_id': id, 'price': price });
          };

          supplierSelect.push(getPushedObj($(supplierPriceInput).parents('.js-supplier-product')), 0);
    }
  }

  function deleteSupplierInv(id, uprid) {
    $('#suppliers' + uprid + '-' + id).remove();
    if ($('.popup-content:visible [name^="suppliers_id_"]:not([readonly])').length == 0){
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