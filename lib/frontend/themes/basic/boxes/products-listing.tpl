{use class="frontend\design\IncludeTpl"}
{use class="frontend\design\Info"}
{use class = "yii\helpers\Html"}
{$list_type = Info::listType($settings[0])}

{$presellectXsell=true}
{$list_type_file = 'boxes/listing-product/'|cat:$list_type|cat:'.tpl'}
{if !$settings[0].show_attributes}
<style>
 .disable-buy-form{ background-color: #fff; height: 100%; left: 0; opacity: 0.7; position: absolute; width: 100%; z-index: 70; }
</style>
{/if}
<div class="products-listing
{if !$only_column && Info::get_gl() == 'list'} listing-list{/if}
{if $settings[0].col_in_row} cols-{$settings[0].col_in_row}{/if}
{if $settings[0].products_align} align-{$settings[0].products_align}{/if}
 list-{$list_type}
 w-list-{$list_type}">
  {Info::addBlockToWidgetsList('list-'|cat:$list_type)}
  {Info::addBlockToPageName($list_type)}
  {Info::addBoxToCss('products-listing')}

  {$page_block = Info::pageBlock()}
  {if isset($settings[0]['page_block'])}
      {$page_block = $settings[0]['page_block']}
  {/if}
  {if Info::get_gl() == 'b2b' && ($page_block == 'categories' || $page_block == 'products')}
      {Html::beginForm(['shopping-cart/', 'action' => 'add_all'], 'post', ['id' => 'add_all-form'])}
      <script type="text/javascript">

        function update_attributes_list(item) {

          var request = {
            products_id: $('input[name="products_id[]"]', item).val()
          };
          var id = '?';
          $('select, input[type="radio"]:checked', item).each(function(i){
            if (i != 0) id += '&';
            id += $(this).attr('name').replace('id[' + request.products_id + ']', 'id') + '=' + $(this).val();
          });
          request = $.extend({
            list_b2b: 1
          }, request);
          request = $.extend({
            qty: 1
          }, request);
          request = $.extend({
            personalCatalogButtonWrapId: $(item).find('input[name="personalCatalogButtonWrapId"]').val(),
          }, request);

          $.get('{Yii::$app->urlManager->createUrl('catalog/product-attributes')}' + id, request, function(data) {
            $('.old', item).html(data.product_price);
            $('.current', item).html(data.product_price);
            $('.specials', item).html(data.special_price);
            $('.attributes', item).html(data.product_attributes);
            if (data.product_valid > 0) {
              {if $smarty.const.STOCK_CHECK != 'false'}
                if ( data.stock_indicator ) {
                    if (typeof data.stock_indicator.quantity_max !== 'undefined') {
                        var $qty = $('input[name="qty[]"]', item);
                        $qty.attr('data-max', data.stock_indicator.quantity_max).trigger('changeSettings');
                    }
                    if (data.stock_indicator.can_add_to_cart && data.stock_indicator.add_to_cart){
                        $('.qty-input', item).removeClass('hidden');
                    }else{
                        $('.qty-input', item).addClass('hidden');
                        $('input[name="qty[]"]', item).val('0');
                    }
                }
              {else}
                if (data.product_qty > 0) {
                    $('.qty-input', item).removeClass('hidden');
                } else {
                  {if $smarty.const.STOCK_CHECK != 'false'}
                    $('.qty-input', item).addClass('hidden');
                    $('input[name="qty[]"]', item).val('0');
                  {/if}
                }
              {/if}
            } else {
              {if $smarty.const.STOCK_CHECK != 'false'}
              $('.qty-input', item).addClass('hidden');
              $('input[name="qty[]"]', item).val('0');
              {/if}
            }
            if ( typeof data.stock_indicator !== 'undefined' ) {
                $('.js-stock', item).html('<span class="'+data.stock_indicator.text_stock_code+'"><span class="'+data.stock_indicator.stock_code+'-icon">&nbsp;</span>'+data.stock_indicator.stock_indicator_text+'</span>');

                if ( typeof data.stock_indicator.products_date_available != 'undefined' ) {
                    $('.js-date-available').html('<span class="date-available">'+data.stock_indicator.products_date_available+'</span>');
                }
            }
            $('select, input[type="radio"]', item).each(function(){
              $(this).attr('name', $(this).attr('name').replace('id', 'id[' + request.products_id + ']'))
            });
            $('input.qty-inp').quantity();
            $('.attributes select, .attributes input[type="radio"]', item).on('change', function(){
              update_attributes_list(item)
            });
            /// multiselect - hide main q-ty
            if ($(data.product_attributes).hasClass('multiattributes')) {
              {literal}
              $('#item-' +data.current_uprid.replace(/\{.+/, '') +' div.qty-input').hide();
              {/literal}
            }
            if(
                    data.hasOwnProperty('personalCatalogButton') &&
                    data.hasOwnProperty('personalCatalogButtonWrapId') &&
                    data.personalCatalogButton.length > 0
            ){
              $('#personal-button-wrap-'+data.personalCatalogButtonWrapId).html(data.personalCatalogButton);
            }
            $('form.form-buy',item).trigger('attributes_updated', [data]);
            if ((typeof(data.flexifi_credit_plan_button) != 'undefined') && (data.flexifi_credit_plan_button != '')) {
                $('div.flexifi-credit-plan-information', item).closest('div.box').html(data.flexifi_credit_plan_button);
            }
          },'json');
        }

        function update_bundle_attributes_list(item) {

          var request = {
            products_id: $('input[name="products_id[]"]', item).val()
          };
          var id = '?';
          $('select, input[type="radio"]:checked', item).each(function(i){
            if (i != 0) id += '&';
            id += $(this).attr('name').replace('id[' + request.products_id + ']', 'id') + '=' + $(this).val();
          });
          request = $.extend({
            list_b2b: 1
          }, request);
          request = $.extend({
            qty: 1
          }, request);

          $.get('{Yii::$app->urlManager->createUrl('catalog/product-bundle')}' + id, request, function(data) {
            $('.old', item).html(data.product_price);
            $('.current', item).html(data.product_price);
            $('.specials', item).html(data.special_price);
            $('.attributes', item).html(data.product_attributes);
            data.product_bundle = data.product_bundle.replace('id="full-bundle-price" class="old"', 'class="full-bundle-price old"');
            data.product_bundle = data.product_bundle.replace('id="actual-bundle-price" class="specials"', 'class="actual-bundle-price specials"');
            $('.bundle', item).html(data.product_bundle);

            $('.full-bundle-price', item).html(data.full_bundle_price);
            $('.actual-bundle-price', item).html(data.actual_bundle_price);

            if (data.product_valid > 0) {
              {if $smarty.const.STOCK_CHECK != 'false'}
                if ( data.stock_indicator ) {
                    if (typeof data.stock_indicator.quantity_max !== 'undefined') {
                        var $qty = $('input[name="qty[]"]', item);
                        $qty.attr('data-max', data.stock_indicator.quantity_max).trigger('changeSettings');
                    }
                    if (data.stock_indicator.can_add_to_cart && data.stock_indicator.add_to_cart){
                        $('.qty-input', item).removeClass('hidden');
                    }else{
                        $('.qty-input', item).addClass('hidden');
                        $('input[name="qty[]"]', item).val('0');
                    }
                }
              {else}
                if (data.product_qty > 0) {
                    $('.qty-input', item).removeClass('hidden');
                } else {
                  {if $smarty.const.STOCK_CHECK != 'false'}
                    $('.qty-input', item).addClass('hidden');
                    $('input[name="qty[]"]', item).val('0');
                  {/if}
                }
              {/if}
            } else {
              {if $smarty.const.STOCK_CHECK != 'false'}
              $('.qty-input', item).addClass('hidden');
              $('input[name="qty[]"]', item).val('0');
              {/if}
            }
            if ( typeof data.stock_indicator !== 'undefined' ) {
                $('.js-stock', item).html('<span class="'+data.stock_indicator.text_stock_code+'"><span class="'+data.stock_indicator.stock_code+'-icon">&nbsp;</span>'+data.stock_indicator.stock_indicator_text+'</span>');

                if ( typeof data.stock_indicator.products_date_available != 'undefined' ) {
                    $('.js-date-available').html('<span class="date-available">'+data.stock_indicator.products_date_available+'</span>');
                }
            }
            $('select', item).each(function(){
              $(this).attr('name', $(this).attr('name').replace('id', 'id[' + request.products_id + ']'))
            });
            $('.attributes select, .attributes input[type="radio"]', item).on('change', function(){
              update_bundle_attributes_list(item)
            });
            $('.bundle select, .bundle input[type="radio"]', item).on('change', function(){
              update_bundle_attributes_list(item)
            });
            if ((typeof(data.flexifi_credit_plan_button) != 'undefined') && (data.flexifi_credit_plan_button != '')) {
                $('div.flexifi-credit-plan-information', item).closest('div.box').html(data.flexifi_credit_plan_button);
            }
          },'json');
        }

      </script>
  {/if}

{foreach $products as $product}{trim(IncludeTpl::widget(['file' => $list_type_file, 'params' => ['product' => $product, 'settings' => $settings, 'languages_id' => $languages_id, 'products_carousel' => $products_carousel]]))}
{/foreach}

    {$is_b2b = false}
  {if Info::get_gl() == 'b2b' && ($page_block == 'categories' || $page_block == 'products') && !GROUPS_DISABLE_CART}
      <button type="submit" class="btn-2" id="add_all">{$smarty.const.ADD_TO_CART}</button>
    {Html::endForm()}
      {$is_b2b = true}
  {else}
  {if $settings[0].show_paypal_button}
    {$app->controller->view->paypal_express_js}
   {/if}
  {/if}
</div>

{if !$is_b2b}
<script  type="text/javascript">
   {if !$settings[0].show_attributes}

  function update_attributes_list(elOrHolder) {
    if ($(elOrHolder).hasClass('item-holder')) {
      var holder = $(elOrHolder);
    } else {
      if  ($(elOrHolder).data('item')) {
        var holder = $(elOrHolder).closest('.item-holder[data-item='+$(elOrHolder).data('item')+']');
      } else {
        var holder = $(elOrHolder).closest('.item-holder');
      }
    }
    var _data=$(holder).find("select, textarea, input").serialize().replace(/pid%5B%5D=/, 'products_id='),
    pid = $('input[name="pid[]"]', holder).val();
    if ($('input[name="pid[]"]', holder).length==0) {
      pid = $('input[name="products_id"]', holder).val();
    }
    $.get("{Yii::$app->urlManager->createUrl(['catalog/product-attributes', 'type' => 'listing'])}", _data, function(data, status) {
      if (status == "success") {
        $('.current', holder).html(data.product_price);
        if(data.hasOwnProperty('special_price') && data.special_price.length > 0){
            $('.specials', holder).show().html(data.special_price);
            $('.old', holder).show().html(data.product_price);
            $('.current', holder).hide();
        } else {
            $('.specials', holder).hide();
            $('.old', holder).hide();
            $('.current', holder).show();
        }

        $('.product-attributes', holder).replaceWith(data.product_attributes);
        if (data.product_valid > 0) {
            $('.disable-buy-form', holder).hide();
            $('.buy-all-checkbox', holder).prop('disabled', false);
            if (data.product_in_cart){
                $('.add-to-cart', holder).hide();
                $('.in-cart', holder).show();
                $('.qty-input', holder).hide()
            } else {
                $('.add-to-cart', holder).show();
                $('.in-cart', holder).hide();
                $('.qty-input', holder).show()
            }
            if ( data.stock_indicator ) {
              var stock_data = data.stock_indicator;
              if ( stock_data.add_to_cart ) {
                  $('.disable-buy-form', holder).hide();
                  $('.buy-all-checkbox', holder).prop('disabled', false);
                  $('.buy-button', holder).show();
                  if (data.product_in_cart){
                      $('.add-to-cart').hide();
                      $('.in-cart').show();
                      $('.qty-input', holder).hide()
                  } else {
                      $('.add-to-cart').show();
                      $('.in-cart').hide();
                      $('.qty-input', holder).show()
                  }
              }else{
                  $('.disable-buy-form', holder).show();
                  $('.buy-all-checkbox', holder).prop('checked', false).prop('disabled', true);
                  $('.buy-button', holder).hide();
                      $('.qty-input', holder).hide()
                  if (data.product_in_cart){
                      $('.add-to-cart', holder).hide();
                      $('.in-cart', holder).show();
                  } else {
                      $('.add-to-cart', holder).show();
                      $('.in-cart', holder).hide();
                      //$('.qty-input', holder).show()
                  }
              }
              if ( stock_data.quantity_max > 0 ) {
                  var qty = $('.qty-inp', holder);
                  $.each(qty, function(i, e){
                      $(e).attr('data-max', stock_data.quantity_max).trigger('changeSettings');
                      if ($(e).val() > stock_data.quantity_max) {
                          $(e).val(stock_data.quantity_max);
                      }
                  });
              }
          }
          ///add list to name of attributes and qty
          $('select, input[type="radio"]', holder).each(function(){
            $(this).attr('name', $(this).attr('name').replace('listid', 'listid[' + pid + ']'))
          });
          $('input[name="qty[]"]', holder).each(function(){
            $(this).attr('name', 'listqty[]')
          });

        } else {
          $('.disable-buy-form', holder).show();
          $('.buy-all-checkbox', holder).prop('checked', false).prop('disabled', true);
        }

        // update x-sell summary
        $list = $(holder).closest('.cross-sell-box-container');
        if ($list.length) {
          updateXListSummary($list);
        }
        //

        if ( typeof data.stock_indicator != 'undefined' ) {
            $('.stock', holder).html('<span class="'+data.stock_indicator.text_stock_code+'"><span class="'+data.stock_indicator.stock_code+'-icon">&nbsp;</span>'+data.stock_indicator.stock_indicator_text+'</span>');
            if ( typeof data.stock_indicator.products_date_available != 'undefined' ) {
                $('.js-date-available').html('<span class="date-available">'+data.stock_indicator.products_date_available+'</span>');
            }
        }
		    $('.product-attributes select', holder).addClass('form-control');
        /// multiselect - hide main q-ty
        if ($(data.product_attributes).hasClass('multiattributes')) {
          $('div.qty-input', holder).hide();
        }
        $('form.form-buy',holder).trigger('attributes_updated', [data]);
      }
    },'json');
  }

  {/if}

    tl('{Info::themeFile('/js/main.js')}', function(){
/// init events
/// select/hide all - click all checkboxes
      $('.box-summary .select-all:not(".inited"), .box-summary .select-none:not(".inited")').each(function() {
        $(this).addClass('inited').on('click', function(e) {
          e.preventDefault();
          var $list = $(this).closest('.box-summary').parent();
          if ($(this).hasClass('select-all')) {
            $list = $('.buy-all-choose .buy-all-checkbox:not(:checked)', $list);
          } else {
            $list = $('.buy-all-choose .buy-all-checkbox:checked', $list)
          }
          $list.each(function() {
            $(this).trigger('click');
          })
        })
      });

///checkboxes click - copy/hide product title, calculate price
      $('.products-listing .buy-all-choose:not(".inited")').each(function() {
        $(this).addClass('inited');

        $('.buy-all-checkbox', $(this)).on('click', function(e) {
          var $item = $(this).closest('.item'),
          //$list = $(this).closest('.cross-sell-box-container');
          $list = $(this).closest('.products-listing').parent().parent();
          try {
            //set qty 1 if selected and qty is less 1
            var $qty = $('input[name="qty[]"], input[name="listqty[]"]', $item);
            if (parseInt($qty.val())<=0) $qty.val(1);
          } catch (e) { }
          updateXListSummary($list);
        })
      });
      {if $presellectXsell}
      $('.select-all.inited').click();
      {/if}
    })

/**
 * $list - list container, includes product list and total container
 */
    function updateXListSummary($list) {
      {if !$presellectXsell}
      $('.box-products, .box-totals', $list).html('');
      {/if}
      var total = 0, totalqty = 0,
      $price,
      totalOk = true;
      $('.buy-all-checkbox', $list).each(function() {
        if (this.checked) {
          var $item = $(this).closest('.item'),
              $title = $('.title', $item),
              $pricesBlock = $('.price', $item);
          try {
            var qty = parseInt($('input[name="qty[]"], input[name="listqty[]"]', $item).val());
            $price = $('.specials:visible, .current:visible', $pricesBlock);
            var priceVal = parseFloat($('span[itemprop="price"]:visible', $price).attr('content') );
          } catch (e) { }
          if (qty>0 && priceVal>0) {
            total += qty*priceVal;
          } else {
            totalOk = false;
          }
          totalqty += qty;
          {if $presellectXsell}
            var $item = $('.box-products .p'+ $(this).data('id'), $list);
            if ($item.length == 0 ) {
              $('.box-products', $list).append('<div class="p'+$(this).data('id')+' buy-all-selected"><span data-id="' +$(this).data('id') +'" class="buy-all-checker"></span>' + $title.html() + ' <span class="qty-price">' + qty + ' <span class="buyall-item-price">x ' + $price.html() + '</span></span></div>');
            } else {
              $item.removeClass('unselected').html('<span data-id="' + $(this).data('id') + '" class="buy-all-checker"></span>' + $title.html() + ' <span class="qty-price">' + qty + ' <span class="buyall-item-price">x ' + $price.html() + '</span></span>');
            }
          {else}
            $('.box-products', $list).append('<div class="p'+$(this).data('id')+' buy-all-selected"><span data-id="' +$(this).data('id') +'" class="buy-all-checker"></span>' + $title.html() + ' <span class="qty-price">' + qty + ' <span class="buyall-item-price">x ' + $price.html() + '</span></span></div>');
          {/if}
          if (isNaN(priceVal)) {
            $('.box-products .p'+ $(this).data('id') + ' .buyall-item-price', $list).hide();
          }

          $('.box-products .p'+ $(this).data('id') + ' .buy-all-checker', $list).on('click', function(e){
            e.preventDefault();
            $('.add-all-choose input[data-id="'+$(this).attr('data-id')+'"]').trigger('click');
          })
        } else {
          {if $presellectXsell}
            var $item = $('.box-products .p'+ $(this).data('id'), $list);
            $('.qty-price', $item).hide();
            $item.addClass('unselected');
          {else}
            $('.box-products .p'+ $(this).data('id'), $list).remove();
          {/if}
        }
      });
      if (totalqty>0) {
        try {
          $price = $price.clone();
          $price.find('[itemprop="price"]').attr('content', total.toFixed(2)).text(total.toFixed(2));
        } catch (e) {
          $price = '<span>' + total.toFixed(2) + '</span>';
        }
        if (totalOk) {
          $('.box-totals', $list).html('<span class="total-price">' + $price.html() + '</span>');
        }
        $('.box-summary .add-to-cart', $list).removeClass('disable-buy-form');
      } else {
        $('.box-summary .add-to-cart', $list).addClass('disable-buy-form');
        {if $presellectXsell}
        $('.box-totals', $list).html('');
        {/if}
      }
      $('.select-all, .select-none', $list).hide();
      var chkrs = $('.buy-all-checkbox:visible', $list);
      if (chkrs.length) {
        if ($('.buy-all-checkbox:checked', $list).length < chkrs.length) {
          $('.select-all', $list).show();
        }
        if ($('.buy-all-checkbox:not(:checked)', $list).length < chkrs.length) {
          $('.select-none', $list).show();
        }
      }

    }

</script>

  {/if}
{\common\components\google\widgets\GoogleTagmanger::getJsEvents([[ 'class' => '.products-listing .item a, .btn-buy.add-to-cart', 'action' => 'click' , 'php_action' => 'productClick', 'page' => 'current' ]])}

{if !$settings[0].list_demo}
<script type="text/javascript">
  tl(['{Info::themeFile('/js/main.js')}'{if $settings[0].lazy_load}, '{Info::themeFile('/js/jquery.lazy.min.js')}'{/if}] , function(){
      {if Info::get_gl() == 'grid'}
    setTimeout(function(){
      $('.products-listing').inRow(['.image', '.name', '.price', '.description-2', '.buttons', '.qty-input', '.buy-button', '.add-height'], {if $settings[0].col_in_row}{$settings[0].col_in_row}{else}4{/if})
    }, 500);
      {/if}

      {if $settings[0].lazy_load}$('.lazy').lazy( { bind: 'event' } );{/if}

    {if $settings[0].fbl && !Info::isAdmin()}

    var page = 2;
    var key = true;
    var count = 0;
    var container = { };
    window.numberOfRows = {$params.number_of_rows};

    var getListByScroll = function(){
      var products_listing = $('.products-listing');
      if (products_listing.offset().top + products_listing.height() - $(window).scrollTop() < $(window).height()){

        {if Info::get_gl() == 'b2b' && ($page_block == 'categories' || $page_block == 'products')}
        count = $('.products-listing > form > div').length;
        container = $('.products-listing > form');
        {else}
        count = $('.products-listing > div').length;
        container = $('.products-listing');
        {/if}
        if (key && count < numberOfRows){
          key = false;
          var url = $('#filters_url_full').val();
          if (!url) {
            url = '{$params.url}';
          }
          $.get(url, { fbl: 1, page: page }, function(d){
            page++;
            key = true;
            //$(window).off('scroll', getListByScroll);
            container.append(d);
              {if $settings[0].lazy_load}$('.lazy').lazy( { bind: 'event' } );{/if}
              $('input.qty-inp').quantity();
              {if Info::get_gl() == 'grid'}
            $('.products-listing').inRow(['.image', '.name', '.price', '.description-2', '.buttons', '.qty-input', '.buy-button', '.add-height'], {if $settings[0].col_in_row}{$settings[0].col_in_row}{else}4{/if})
              {/if}
          })
        }
      }
    };

    $(window).on('scroll', getListByScroll);

    {/if}

    $('.form-whishlist').popUp({
      box_class: 'cart-popup'
    });
    {\frontend\design\Info::addBoxToCss('quantity')}
    $('input.qty-inp').quantity();

    {assign var=after_add value=Info::themeSetting('after_add')}
    {if $after_add == 'popup'}

        $('.btn-buy').on('click', function(){
            th = $(this);
            //$('.loaded-qty').remove();
            var qty = th.parents('.item').find('.qty-inp').val();
            if (qty == undefined) {
                qty = 1;
            }
            th.addClass('preloader');
            th.after('<div class="loaded-qty" style="display:none">('+qty+' {$smarty.const.TEXT_LISTING_ADDED})</div>');
          });

      $('.btn-buy:not(.btn-buy-aj), .form-buy').popUp({
        box_class: 'cart-popup',
        success: function(data, popup_box){
            var n = $(window).scrollTop();
            $('.pop-up-content:last').html(data);
            $(window).scrollTop(n);
            $('.add_product_success').show();
            $('.loaded-qty').show();
            th.removeClass('preloader');
        },
        opened: function(obj){
          obj.closest('.item').find('.add-to-cart').hide();
          obj.closest('.item').find('.in-cart').show();
          obj.closest('.item').find('.qty-input').hide()
        }
      });

      $('.btn-buy-aj:not(.btn-buy-all-aj)').each( function() {
        $(this).popUp({
          box_class: 'cart-popup',
          holder: $(this).closest('.item-holder[data-item='+$(this).data('item')+']'),
          pid: $(this).data('item'),
          beforeSend: function() {
            var holder = $(this.holder),
                _data = $(holder).find("select, textarea, input").serializeArray();

          _data.push({ name: 'popup', value: 'true' });
          _data.push({ name: '_csrf', value: $('input[name=_csrf]').val() });
          {literal}
          _data = _data.reduce((ret, {name, value}) => ({...ret, [name]: value}),{});
          {/literal}
            return _data;
          },
          type: "POST",
          opened: function(obj){          }
        });
      });

      $('.btn-buy-all-aj').each( function() {
        $(this).popUp({
          box_class: 'cart-popup',
          holder: $(this).closest('.box-summary').parent(),
          pid: $(this).data('item'),
          beforeSend: function() {
            var holder = $(this.holder), _data = [];
            $('.buy-all-checkbox:checked', holder).each(function () {
              var h = $(this).closest('.item-holder');
              _data = _data.concat($(h).find("select, textarea, input").serializeArray());
            });
          _data.push({ name: 'popup', value: 'true' });
          _data.push({ name: '_csrf', value: $('input[name=_csrf]').val() });

          {literal}
          var idx = {};
          _data = _data.reduce(function (res, item) {
                            if (item.name.match(/\[\]/g) ) {
                              if(typeof(idx[item.name]) === 'undefined') {
                                idx[item.name] = 0;
                              } else {
                                idx[item.name]++;
                              }
                              item.name = item.name.replace('[]', '['+idx[item.name]+']');
                            }
                            res[item.name] = item.value;
                            return res;
                        }, {});
          {/literal}
            return _data;
          },
          type: "POST",
          opened: function(obj){
            //obj.closest('.item').find('.add-to-cart').hide();
            //obj.closest('.item').find('.in-cart').show();
            //obj.closest('.item').find('.qty-input').hide()
          }
        });
      });
    {elseif $after_add == 'animate'}


    {/if}
  });

</script>
{/if}
