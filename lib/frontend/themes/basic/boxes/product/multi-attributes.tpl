{use class="Yii"}
{use class="\yii\helpers\Html"}
{use class="frontend\design\Info"}
<div data-id="multi-attributes" class="attributes multiattributes">
<style>
.mult-qty-input .qty-box { display: grid; }
.mult-qty-input .qty-box > span, .mult-qty-input .qty-box > input { width: 60px;margin:0; }
.mult-qty-input .qty-box > span.smaller{ order: 1; }
.mult-qty-input .qty-box > span.bigger{ order: -1; }
.mult-qty-input .qty-box > input.qty-inp{ order: 0; }
.mix-attributes .attribute-qty-block{ display: inline-block;text-align: center; }
</style>
  {Html::hiddenInput('attributes_mode', 'multi')}
  {foreach $attributes as $iter => $item}
    {if $iter == count($attributes)-1}
        {\frontend\design\boxes\product\MultiMix::widget(['item' => $item])}
    {else}
        {if $item['type'] == 'radio'}
          {include file="./attributes/radio.tpl" item=$item}
        {else}
          {include file="./attributes/select.tpl" item=$item}
        {/if}
    {/if}
  {/foreach}
{if !Yii::$app->request->get('list_b2b')}

<script type="text/javascript">
{if not $isAjax}
  tl(function(){
    if (document.forms['cart_quantity']) {
      update_attributes(document.forms['cart_quantity']);
    }
  });
{/if}
  function update_attributes(theForm) {
    $.get("{Yii::$app->urlManager->createUrl('catalog/product-attributes')}", $(theForm).serialize(), function(data, status) {
      if (status == "success") {
        /*$('#product-price-old').html(data.product_price);
        $('#product-price-current').html(data.product_price);
        if(data.hasOwnProperty('special_price') && data.special_price.length > 0){
            $('#product-price-special').show().html(data.special_price);
            if (!$('#product-price-old').hasClass('old')) $('#product-price-old').addClass('old');
            if ($('#product-price-current').hasClass('price_1')){
                $('#product-price-current').html(data.special_price);
            }
        } else {
            $('#product-price-old').removeClass('old');
            $('#product-price-special').hide();
        }*/

        $('.mix-attributes').replaceWith(data.product_attributes);
        if (data.product_valid) {
            if ( data.stock_indicator ) {
                var stock_data = data.stock_indicator;
                if ( stock_data.add_to_cart ) {
                    if (data.add_to_cart_text) {
                        try {
                            $('#btn-cart button')[0].innerHTML = data.add_to_cart_text;
                        } catch (e) { }
                    }
                  $('#btn-cart').show();
                  $('.multi-attributes .qty-input').show();
                  //$('.add-to-cart').show();
                  if (data.product_in_cart && !isElementExist( ['themeSettings', 'showInCartButton'], entryData)){
                      $('.add-to-cart').hide();
                      $('.in-cart').show();
                      $('.multi-attributes .qty-input').hide()
                  } else {
                      $('.add-to-cart').show();
                      $('.in-cart').hide();
                      $('.multi-attributes .qty-input').show()
                  }
                  $('#btn-cart-none:visible').hide();
              }else{
                  $('#btn-cart').hide();
                  if ($('.multi-attributes .qty-input').length == 1){
                    $('.multi-attributes .qty-input').hide();
                  }
                  //$('.add-to-cart').hide();
                  if (data.product_in_cart && !isElementExist( ['themeSettings', 'showInCartButton'], entryData)){
                      $('.add-to-cart').hide();
                      $('.in-cart').show();
                      $('.multi-attributes .qty-input').hide()
                  } else {
                      $('.add-to-cart').show();
                      $('.in-cart').hide();
                      $('.multi-attributes .qty-input').show()
                  }
                  $('#btn-cart-none:hidden').show();
              }
              if ( stock_data.request_for_quote ) {
                  $('.btn-rfq, #btn-rfq').show();
                  $('#btn-cart-none:visible').hide();
              }else{
                  $('.btn-rfq, #btn-rfq').hide();
              }
              if ( stock_data.ask_sample ) {
                  $('.btn-sample, #btn-sample').show();
              }else{
                  $('.btn-sample, #btn-sample').hide();
              }
              $('#btn-notify').hide();
          }else{
              $('#btn-cart').hide();
              $('#btn-cart-none').show();
              $('.multi-attributes .qty-input').hide();
              $('#btn-notify').hide();
          }          
        } else {
          if ($('.multi-attributes .qty-input').length == 1){
            $('.multi-attributes .qty-input').hide();
          }
          $('#btn-cart').hide();
          $('#btn-cart-none').show();
          $('#btn-notify').show();
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
                  file: 'boxes/product/multi-attributes.tpl'
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
		$('#product-attributes select').addClass('form-control');
        $(theForm).trigger('attributes_updated', [data]);
        return data;
      }
    },'json').then(function(data){ if (typeof sProductsReload == 'function'){ sProductsReload(data); } });
  }
</script>

{/if}
</div>

