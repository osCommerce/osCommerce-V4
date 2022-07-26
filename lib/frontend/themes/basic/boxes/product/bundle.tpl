{use class="Yii"}
{use class="frontend\design\Info"}
<div id="product-bundle">
<div class="heading-2 bundle_title">{$smarty.const.TEXT_BUNDLE_PRODUCTS_NEW}</div>
    <div class="bundle-products">
  {foreach $products as $product name=bundles}
    <div class="bundle_item">
      <div class="bundle_image">
          {if $product._status}
              <a href="{$product.product_link}"><img src="{$product.image}" alt="{$product.products_name|escape:'html'}" title="{$product.products_name|escape:'html'}"></a>
          {else}
              <span><img src="{$product.image}" alt="{$product.products_name|escape:'html'}" title="{$product.products_name|escape:'html'}"></span>
          {/if}
      </div>
      <div class="right-area-bundle">
				<div class="bundle_name">
                    {if $product._status}
                        <a href="{$product.product_link}">{$product.products_name}</a>
                    {else}
                        <span>{$product.products_name}</span>
                    {/if}
        <div class="bundle_attributes after">
          {foreach $product.attributes_array as $item}
            <div>
              <select name="{$item.name}" data-required="{$smarty.const.PLEASE_SELECT} {$product.products_name|escape:'html'} - {$item.title}"{if !Yii::$app->request->get('list_b2b')} onchange="update_bundle_attributes(this.form);"{/if}>
                <option value="0">{$smarty.const.SELECT} {$item.title}</option>
                {foreach $item.options as $option}
                  <option value="{$option.id}"{if $option.id==$item.selected} selected{/if}{if {strlen($option.params)} > 0} {$option.params}{/if}>{$option.text}</option>
                {/foreach}
              </select>
            </div>
          {/foreach}
        </div>
        <span class="{$product.stock_indicator.text_stock_code}"><span class="{$product.stock_indicator.stock_code}-icon">&nbsp;</span>{$product.stock_indicator.stock_indicator_text}</span>
      </div>
        <div class="bundle_qty">
          {$product.num_product} {$smarty.const.TEXT_ITEMS}
        </div>
        <div class="bundle_price">
          {if $product.price}
            <span class="current">{$product.price}</span>
          {else}
            <span class="old">{$product.price_old}</span>
            <span class="specials">{$product.price_special}</span>
          {/if}
        </div>
      </div>
    </div>
  {/foreach}
    </div>
  <div class="bundle-total-price">
      <div class="title">{$smarty.const.TEXT_TOTAL_PRICE}</div>
      <div class="price">
          <span id="full-bundle-price" class="old"></span>
          <span id="actual-bundle-price" class="specials"></span>
      </div>
  </div>
{if !Yii::$app->request->get('list_b2b')}
<script type="text/javascript">
{if not $isAjax}
  tl(function() {
    update_bundle_attributes(document.forms['cart_quantity']);
  });
{/if}
  function update_bundle_attributes(theForm) {
    var _data = $(theForm).find('input, select, textarea').filter(function() { return $(this).closest(".item").length == 0; }).serialize();
    $.get("{Yii::$app->urlManager->createUrl('catalog/product-bundle')}", _data, function(data, status) {
      if (status == "success") {
        if ($('.price_1').length){ //packs?
            if (data.special_price){
                $('#product-price-current').html(data.special_price).show();
            } else {
                $('#product-price-current').html(data.product_price).show();
            }
            //$('#product-price-old').html(data.product_price);
            //$('#product-price-special').html(data.special_price);
        } else {
        $('#product-price-old').html(data.product_price);
        $('#product-price-current').html(data.product_price);
        $('#product-price-special').html(data.special_price);
        }

        $('#product-attributes').replaceWith(data.product_attributes);
        $('#product-bundle').replaceWith(data.product_bundle);
        $('#full-bundle-price').html(data.full_bundle_price);
        $('#actual-bundle-price').html(data.actual_bundle_price);
        $('#product-price-current').html(data.actual_bundle_price);
        $('#product-price-old').html(data.full_bundle_price);
        $('#product-price-special').html(data.actual_bundle_price);

        if (data.product_valid > 0) {
            if (data.product_in_cart && !isElementExist( ['themeSettings', 'showInCartButton'], entryData)) {
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
                  $('.qty-input').show();
                  //$('.add-to-cart').show();
                  if (data.product_in_cart && !isElementExist( ['themeSettings', 'showInCartButton'], entryData)) {
                      $('.add-to-cart').hide();
                      $('.in-cart').show()
                  } else {
                      $('.add-to-cart').show();
                      $('.in-cart').hide()
                  }
                  $('#btn-cart-none:visible').hide();
              } else {
                  $('#btn-cart').hide();
                  $('.qty-input').hide();
                  //$('.add-to-cart').hide();
                  if (data.product_in_cart && !isElementExist( ['themeSettings', 'showInCartButton'], entryData)) {
                      $('.add-to-cart').hide();
                      $('.in-cart').show()
                  } else {
                      $('.add-to-cart').show();
                      $('.in-cart').hide()
                  }
                  $('#btn-cart-none:hidden').show();
              }
              if ( stock_data.request_for_quote ) {
                  $('#btn-rfq').show();
                  $('#btn-cart-none:visible').hide();
              } else {
                  $('#btn-rfq').hide();
              }
              if ( stock_data.notify_instock ) {
                  $('#btn-notify').show();
              } else {
                  $('#btn-notify').hide();
              }
              if ( stock_data.quantity_max > 0 ) {
                  var qty = $('.qty-inp');
                  $.each(qty, function(i, e) {
                      $(e).attr('data-max', stock_data.quantity_max).trigger('changeSettings');
                      if ($(e).val() > stock_data.quantity_max) {
                          $(e).val(stock_data.quantity_max);
                      }
                  });
              }
          } else {
              $('#btn-cart').hide();
              $('#btn-cart-none').show();
              $('#btn-notify').hide();
              $('.qty-input').hide();
          }
        } else {
            $('.qty-input').hide();
            $('#btn-cart').hide();
            $('#btn-cart').hide();
            $('#btn-cart-none').show();
            $('#btn-notify').hide();
        }
        if ( typeof data.stock_indicator != 'undefined' ) {
            $('.js-stock').html('<span class="'+data.stock_indicator.text_stock_code+'"><span class="'+data.stock_indicator.stock_code+'-icon">&nbsp;</span>'+data.stock_indicator.stock_indicator_text+'</span>');

            if ( typeof data.stock_indicator.products_date_available != 'undefined' ) {
                $('.js-date-available').html('<span class="date-available">'+data.stock_indicator.products_date_available+'</span>');
            }
        }
        if ((typeof(data.flexifi_credit_plan_button) != 'undefined') && (data.flexifi_credit_plan_button != '')) {
            $('div.flexifi-credit-plan-information').closest('div.box').html(data.flexifi_credit_plan_button);
        }
      }
    },'json');
  }
  tl(function() {
    if ( typeof update_attributes === 'function' ) {
      update_attributes = update_bundle_attributes;
    }
  });
</script>
{/if}
</div>
