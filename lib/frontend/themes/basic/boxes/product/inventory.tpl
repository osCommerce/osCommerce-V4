<div id="product-inventory" class="inventory">
  {foreach $inventory as $item}
    <div>
        <div class="radio"><label><input onchange="update_inventory(this.form, this);" type="radio" name="inv_uprid" value="{$item.products_id}"{if $item.selected} checked{/if}>{$item.attributes_names_short} <span class="inventory-price">{$item.actual_price}</span></label></div>
    </div>
  {/foreach}
<script type="text/javascript">
{if not $isAjax}
  tl(function(){
    update_inventory(document.forms['cart_quantity']);
  });
{/if}
  function update_inventory(theForm) {
    $.get("{Yii::$app->urlManager->createUrl('catalog/product-inventory')}", $(theForm).serialize(), function(data, status) {
      if (status == "success") {
        $(document).off('change', 'input.qty-inp', qty_change);
        $('#product-price-old').html(data.product_price);
        $('#product-price-current').html(data.product_price);
        $('#product-price-special').html(data.special_price);
        $('#product-inventory').replaceWith(data.product_inventory);
        if (data.product_valid > 0) {
            if (data.product_in_cart && !isElementExist( ['themeSettings', 'showInCartButton'], entryData)){
                $('.add-to-cart').hide();
                $('.in-cart').show()
            } else {
                $('.add-to-cart').show();
                $('.in-cart').hide()
            }
            if ( data.stock_indicator ) {
              var stock_data = data.stock_indicator;
              if ( stock_data.add_to_cart ) {
                  $('#btn-cart').show();
                  //$('.qty-input').show();
                  //$('.add-to-cart').show();
                  if (data.product_in_cart && !isElementExist( ['themeSettings', 'showInCartButton'], entryData)){
                      $('.add-to-cart').hide();
                      $('.in-cart').show()
                      $('.qty-input').hide();
                  } else {
                      $('.add-to-cart').show();
                      $('.in-cart').hide()
                      $('.qty-input').show();
                  }
                  $('#btn-cart-none:visible').hide();
              }else{
                  $('#btn-cart').hide();
                  $('.qty-input').hide();
                  //$('.add-to-cart').hide();
                  if (data.product_in_cart && !isElementExist( ['themeSettings', 'showInCartButton'], entryData)){
                      $('.add-to-cart').hide();
                      $('.in-cart').show()
                  } else {
                      $('.add-to-cart').show();
                      $('.in-cart').hide()
                  }
                  $('#btn-cart-none:visible').hide();
              }
              if ( stock_data.request_for_quote ) {
                  $('#btn-rfq').show();
                  $('#btn-cart-none:visible').hide();
              }else{
                  $('#btn-rfq').hide();
              }
              if ( stock_data.notify_instock ) {
                  $('#btn-notify').show();
              }else{
                  $('#btn-notify').hide();
              }
              if ( stock_data.quantity_max > 0 ) {
                  var qty = $('.qty-inp');
                  $.each(qty, function(i, e){
                      $(e).attr('data-max', stock_data.quantity_max).trigger('changeSettings');
                      if ($(e).val() > stock_data.quantity_max) {
                          $(e).val(stock_data.quantity_max);
                      }
                  });
              }
          }else{
              $('#btn-cart').hide();
              $('#btn-cart-none').show();
              $('#btn-notify').hide();
              $('.qty-input').hide();
          }
          {*
          if (data.stock_indicator && data.stock_indicator.max_qty > 0) {
            {if $smarty.const.STOCK_CHECK != 'false'}
						var qty = $('.qty-inp');
            $.each(qty, function(i, e){
              $(e).attr('data-max', data.stock_indicator.max_qty).trigger('changeSettings');
              if ($(e).val() > data.stock_indicator.max_qty) {
                $(e).val(data.stock_indicator.max_qty);
              }
            });
            /*var qty = $('#qty');
            qty.attr('data-max', data.product_qty).trigger('changeSettings');
            if (qty.val() > data.product_qty) {
              qty.val(data.product_qty);
            }
						*/
            {/if}
            $('#btn-cart').show();
            $('#btn-cart-none').hide();
            $('#btn-notify').hide();
            if (data.product_in_cart){
              $('.add-to-cart').hide();
              $('.qty-input').hide();
              $('.in-cart').show()
            } else {
              $('.add-to-cart').show();
              $('.qty-input').show();
              $('.in-cart').hide()
            }
          } else {
            {if $smarty.const.STOCK_CHECK == 'false'}
            $('#btn-cart').show();
            $('#btn-cart-none').hide();
            $('#btn-notify').hide();
            {else}
            $('#btn-cart').hide();
            $('#btn-cart-none').hide();
            $('#btn-notify').show();
            {/if}
          }
          *}
        } else {
          $('.qty-input').hide();
          $('#btn-cart').hide();
          $('#btn-cart-none').show();
          $('#btn-notify').hide();
        }
        /*if ( typeof data.image_widget != 'undefined' ) {
          $('.js-product-image-set').replaceWith(data.image_widget);
        }*/
          if ( typeof data.images != 'undefined' ) {
              tl.store.dispatch({
                  type: 'CHANGE_PRODUCT_IMAGES',
                  value: {
                      id: data.productId,
                      defaultImage: data.defaultImage,
                      images: data.images,
                  },
                  file: 'boxes/product/inventory.tpl'
              });
          }
        if ( typeof data.dynamic_prop != 'undefined' ) {
            for( var prop_name in data.dynamic_prop ) {
                if ( !data.dynamic_prop.hasOwnProperty(prop_name) ) continue;
                var _value = data.dynamic_prop[prop_name];
                var $value_dest = $('.js_prop-'+prop_name);
                if ( $value_dest.length==0 ) continue;
                $value_dest.html(_value);
                $value_dest.parents('.js_prop-block').each(function() {
                    if (_value==''){
                        $(this).addClass('js-hide');
                    }else{
                        $(this).removeClass('js-hide');
                    }
                });
            }
        }
        if ( typeof data.stock_indicator != 'undefined' ) {
            $('.js-stock').html('<span class="'+data.stock_indicator.text_stock_code+'"><span class="'+data.stock_indicator.stock_code+'-icon">&nbsp;</span>'+data.stock_indicator.stock_indicator_text+'</span>');

            if ( typeof data.stock_indicator.products_date_available != 'undefined' ) {
                $('.js-date-available').html('<span class="date-available">'+data.stock_indicator.products_date_available+'</span>');
            }
        }
      }
    },'json');
  }
  var qty_change = function (e) {
    update_inventory(document.forms['cart_quantity']);
  }
  tl(function(){
    $(document).one('change', 'input.qty-inp', qty_change);
  })  
</script>
</div>
