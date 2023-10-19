{use class="frontend\design\Info"}
<div id="product-custom-bundle">
<div class="heading-2">{$smarty.const.BUILD_YOUR_OWN_COMBO}</div>

<div class="chosen-products products-carousel">
  {foreach $chosenProducts as $prod}
    <div class="item" id="item-{$prod.products_id}" data-id="{$prod.products_id}">
      <input type="hidden" name="custom_bundles[]" value="{$prod.products_id}">

      <div class="image">
        <a href="{$prod.link}"><img src="{$prod.image}" alt="{str_replace('"', '″', $prod.products_name)}" title="{str_replace('"', '″', $prod.products_name)}"></a>
      </div>

      <div class="stock">
        <span class="{$prod.stock_indicator.text_stock_code}"><span class="{$prod.stock_indicator.stock_code}-icon">&nbsp;</span>{$prod.stock_indicator.stock_indicator_text}</span>
      </div>

      <div class="price">
        {if isset($prod.price)}
          <span class="current">{$prod.price}</span>
        {else}
          <span class="old">{$prod.price_old}</span>
          <span class="specials">{$prod.price_special}</span>
        {/if}
      </div>

      <div class="title">
        <a href="{$prod.link}">{$prod.products_name}</a>
      </div>

      <div class="qty">
        <input type="text" name="custom_bundles_qty[{$prod.products_id}]" value="{$prod.custom_bundles_qty}" class="qty-inp" data-max="{$prod.stock_indicator.max_qty}" data-min="{$prod.order_quantity_minimal}" data-step="{$prod.order_quantity_step}" onchange="update_custom_bundle_attributes(this.form);">
      </div>

      <div class="attributes">
      {foreach $prod.attributes_array as $item}
        <div class="select-box">
          <select class="select" name="{$item.name}" data-required="{$smarty.const.PLEASE_SELECT} {$prod.products_name|escape:'html'} - {$item.title}" onchange="update_custom_bundle_attributes(this.form);">
              <option value="0">{$smarty.const.SELECT} {$item.title}</option>
              {foreach $item.options as $option}
                <option value="{$option.id}"{if $option.id==$item.selected} selected{/if}{if {strlen($option.params)} > 0} {$option.params}{/if}>{$option.text}</option>
              {/foreach}
          </select>
        </div>
      {/foreach}
      </div>

      <div class="remove-button">
          <span class="btn btn-remove" data-id="{$prod.products_id}" onclick="remove_from_custom_bundle('{$prod.products_id}');">{$smarty.const.TEXT_REMOVE_CART}</span>
      </div>
    </div>
  {/foreach}
</div>

<div class="right-area">
{if {$chosenProducts|default:array()|@count} > 0}
  <div class="price">
    <span class="separate-text">{$smarty.const.TEXT_TOTAL_PRICE}</span>
    <span class="separate">{$price}</span>
  </div>
  <div class="main-button">
    <button name="custom_bundle_submit" type="submit" class="btn-2 add-custom-bundle" style="display:none">
      {$smarty.const.ADD_COMBO_TO_CART}
    </button>
    <span class="btn add-custom-bundle-none">{$smarty.const.ADD_COMBO_TO_CART}</span>
  </div>
{/if}
</div>

<div class="choose-products">
{if {$products|default:array()|@count} > 0}
  <div class="heading-3">
    {$smarty.const.CHOOSE_FROM_COLLECTION}
  </div>
  <div class="custom-bundle-list products-carousel">
    {foreach $products as $prod}
      <div class="item">

        <div class="image">
          <a href="{$prod.link}"><img src="{$prod.image}" alt="{str_replace('"', '″', $prod.products_name)}" title="{str_replace('"', '″', $prod.products_name)}"></a>
        </div>

        <div class="stock">
          <span class="{$prod.stock_indicator.text_stock_code}"><span class="{$prod.stock_indicator.stock_code}-icon">&nbsp;</span>{$prod.stock_indicator.stock_indicator_text}</span>
        </div>

        <div class="price">
          {if isset($prod.price)}
            <span class="current">{$prod.price}</span>
          {else}
            <span class="old">{$prod.price_old}</span>
            <span class="specials">{$prod.price_special}</span>
          {/if}
        </div>

        <div class="title">
          <a href="{$prod.link}">{$prod.products_name}</a>
        </div>

        <div class="button">
          <span class="btn add-to-custom-bundle" data-id="{$prod.products_id}" onclick="add_to_custom_bundle('{$prod.products_id}');">{$smarty.const.ADD_TO_COMBO}</span>
        </div>
      </div>

    {/foreach}
  </div>
{/if}
</div>
<script type="text/javascript">
  tl('{Info::themeFile('/js/slick.min.js')}', function(){
    var box = $('.w-product-custom-bundle');

    {Info::addBoxToCss('slick')}
    $('.chosen-products', box).slick({
      slidesToShow: 3,
      slidesToScroll: 3,
      infinite: false,
      responsive: [{
          breakpoint: 1000,
          settings: {
            slidesToShow: 2,
            slidesToScroll: 2
          }
        },{
          breakpoint: 800,
          settings: {
            slidesToShow: 1,
            slidesToScroll: 1
          }
        }
      ]
    });

    {Info::addBoxToCss('slick')}
    $('.custom-bundle-list', box).slick({
      slidesToShow: 5,
      slidesToScroll: 5,
      infinite: false,
      responsive: [{
          breakpoint: 1000,
          settings: {
            slidesToShow: 4,
            slidesToScroll: 4
          }
        },{
          breakpoint: 800,
          settings: {
            slidesToShow: 3,
            slidesToScroll: 3
          }
        },{
          breakpoint: 600,
          settings: {
            slidesToShow: 2,
            slidesToScroll: 2
          }
        },{
          breakpoint: 400,
          settings: {
            slidesToShow: 1,
            slidesToScroll: 1
          }
        }
      ]
    });

  });
</script>
{if !Yii::$app->request->get('list_b2b')}
<script type="text/javascript">
{if not $isAjax}
  tl(function() {
    update_custom_bundle_attributes(document.forms['cart_quantity']);
  });
{/if}
  function update_custom_bundle_attributes(theForm) {
    let formData = $(theForm).serializeArray();
    formData.push({ name: 'box_id', value: '{$id}'})
    $.get("{Yii::$app->urlManager->createUrl('catalog/product-custom-bundle')}", formData, function(data, status) {
      if (status == "success") {
//        $('#product-price-old').html(data.product_price);
//        $('#product-price-current').html(data.product_price);
//        $('#product-price-special').html(data.special_price);
        $('#product-custom-bundle').replaceWith(data.product_custom_bundle);
        {\frontend\design\Info::addBoxToCss('quantity')}
        $('input.qty-inp').quantity();
        if (data.product_valid > 0) {
            $('.add-custom-bundle').show();
            if ( data.stock_indicator ) {
              var stock_data = data.stock_indicator;
              if ( stock_data.add_to_cart ) {
                  $('.add-custom-bundle').show();
                  $('.add-custom-bundle-none:visible').hide();
              } else {
                  $('.add-custom-bundle').hide();
                  $('.add-custom-bundle-none:hidden').show();
              }
/*
              if ( stock_data.request_for_quote ) {
                  $('#btn-rfq').show();
                  $('.add-custom-bundle-none:visible').hide();
              } else {
                  $('#btn-rfq').hide();
              }
*/
          } else {
              $('.add-custom-bundle').hide();
              $('.add-custom-bundle-none').show();
          }
        } else {
            $('.add-custom-bundle').hide();
            $('.add-custom-bundle-none').show();
        }
/*
        if ( typeof data.stock_indicator != 'undefined' ) {
            $('.js-stock').html('<span class="'+data.stock_indicator.text_stock_code+'"><span class="'+data.stock_indicator.stock_code+'-icon">&nbsp;</span>'+data.stock_indicator.stock_indicator_text+'</span>');
        }
*/
      }
    },'json');
  }
  function add_to_custom_bundle(prod_id) {
    $('#product-custom-bundle').append('<input type="hidden" name="custom_bundles[]" value="' + prod_id + '">');
    update_custom_bundle_attributes(document.forms['cart_quantity']);
  }
  function remove_from_custom_bundle(prod_id) {
    $('input[name="custom_bundles[]"][value=' + prod_id + ']').remove();
    update_custom_bundle_attributes(document.forms['cart_quantity']);
  }
</script>
{/if}
</div>
