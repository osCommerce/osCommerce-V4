{use class="frontend\design\Info"}
{use class = "yii\helpers\Html"}
{if !($only_element > 0)}
<div id="product-configurator" class="pc_wrapper">
    <div class="pc_table">
{/if}
    {foreach $elements as $element}
    <div id="product-configurator-element-{$element['elements_id']}">
        <div class="pc_name heading-4">{if $element['elements_image'] != ''}<img src="{$smarty.const.DIR_WS_IMAGES}{$element['elements_image']}" alt="{$element['elements_name']}" width="30">{/if} {$element['elements_name']} {if $element['is_mandatory']} <span class="inputRequirement">*</span>{tep_draw_hidden_field('mandatory['|cat:$element['elements_id']|cat:']', $element['elements_id'])} {/if}</div>
        <div class="pc_row">
            <div class="pc_item">
              {if $settings[0].display_type=='list'}
                <ul class="pc-item">
                  {foreach $element['products_array'] as $el}
                  <li>{Html::radio('elements['|cat:$element['elements_id']|cat:']', $element['selected_id']==$el.id, [
                    'value' => $el.id,
                    'label' => $el.text,
                    'id' => 'elements-'|cat:$element['elements_id'],
                    'onchange' => 'update_template_options(this.form, '|cat:intval($element['elements_id'])|cat:')'])}</li>
                  {/foreach}
                </ul>
              {else}
                {tep_draw_pull_down_menu('elements['|cat:$element['elements_id']|cat:']', $element['products_array'], $element['selected_id'], 'id="elements-'|cat:$element['elements_id']|cat:'" onchange="update_template_options(this.form, '|cat:intval($element['elements_id'])|cat:')"')}
              {/if}
            </div>


          {if $element['selected_id'] > 0}
              <div class="item-content">
                  <div class="pc_details">
                      <span class="pc_details_img"><a href="{$element['selected_link']}"><img src="{$element['selected_image']}" title="{$element['selected_name']}" /></a></span>
                  </div>
                  <div class="pc_stock">
                      <a class="pc_more" href="{tep_href_link('catalog/product-configurator-info', 'tID='|cat:$pctemplates_id|cat:'&eID='|cat:$element['elements_id']|cat:'&pID='|cat:$element['selected_id'])}">More details</a>
                      <span class="{$element.selected_stock_indicator.text_stock_code}"><span class="{$element.selected_stock_indicator.stock_code}-icon">&nbsp;</span>{$element.selected_stock_indicator.stock_indicator_text}</span>
                  </div>
                  <div class="pc_attr">
                    {foreach $element.attributes_array as $item}
                        <div class="col-2">
                            <select name="{$item.name}" data-required="{$smarty.const.PLEASE_SELECT} {$element.selected_name|escape:'html'} - {$item.title}" onchange="update_template_options(this.form, {intval($element['elements_id'])});">
                                <option value="0">{$smarty.const.SELECT} {$item.title}</option>
                              {foreach $item.options as $option}
                                  <option value="{$option.id}"{if $option.id==$item.selected} selected{/if}{if {strlen($option.params)} > 0} {$option.params}{/if}>{$option.text}</option>
                              {/foreach}
                            </select>
                        </div>
                    {/foreach}
                    {if is_array($element['bundle_products']) and count($element['bundle_products']) > 0}
                      {foreach $element['bundle_products'] as $bundle_product}
                        <div class="col-full">
                            {$bundle_product['num_product'] * $element['elements_qty']} x {$bundle_product['products_name']}
                        </div>
                        {foreach $bundle_product.attributes_array as $item}
                        <div class="col-2">
                            <select name="elements_attr[{$element['elements_id']}][{$element['selected_id']}][{$item.id}]" data-required="{$smarty.const.PLEASE_SELECT} {$bundle_product.products_name|escape:'html'} - {$item.title}" onchange="update_template_options(this.form, {intval($element['elements_id'])});">
                                <option value="0">{$smarty.const.SELECT} {$item.title}</option>
                              {foreach $item.options as $option}
                                <option value="{$option.id}"{if $option.id==$item.selected} selected{/if}{if {strlen($option.params)} > 0} {$option.params}{/if} data-popup="{$option.popup}">{$option.text}</option>
                              {/foreach}
                            </select>
                        </div>
                        {/foreach}
                      {/foreach}
                    {/if}
                  </div>
                  <div class="pc_qty">
                      <div class="pc_qty_wrapper">
                        {tep_draw_input_field('elements_qty['|cat:$element['elements_id']|cat:']', $element['elements_qty'], 'class="pc-qty-inp" data-min="'|cat:$element['selected_min']|cat:'" data-max="'|cat:$element['selected_max']|cat:'" data-step="1" onchange="update_template_options(this.form, '|cat:intval($element['elements_id'])|cat:');"')}
                      </div>
                  </div>
                  <div class="pc_price price">
                    {if strlen($element['selected_price']) > 0}
                        <span class="current">{$element['selected_price']}</span>
                    {else}
                        <span class="old">{$element['selected_price_old']}</span>
                        <span class="specials">{$element['selected_price_special']}</span>
                    {/if}
                  </div>
              </div>
          {/if}
        </div>
{if $only_element > 0}
<script type="text/javascript">
    tl('{Info::themeFile('/js/main.js')}', function(){
      {\frontend\design\Info::addBoxToCss('quantity')}
        $('input.pc-qty-inp', $('#product-configurator-element-{$only_element}')).quantity();
        $('.pc_more', $('#product-configurator-element-{$only_element}')).popUp({
            box_class: "pc_popup_info"
        })
    })
</script>
{/if}
    </div>
    {/foreach}
{if !($only_element > 0)}
    </div>
    <div class="pc-total-price">{$smarty.const.TEXT_TOTAL_PRICE} <span id="product-price-configurator" class="price"></span></div>
<script type="text/javascript">
    tl('{Info::themeFile('/js/main.js')}', function(){
      {\frontend\design\Info::addBoxToCss('quantity')}
        $('input.pc-qty-inp').quantity();
        $('.pc_more').popUp({
            box_class: "pc_popup_info"
        })
    })
</script>
<script type="text/javascript">
{if not $isAjax}
  tl(function() {
    update_template_options(document.forms['cart_quantity']);
  });
{/if}
  function update_template_options(theForm, only_element = 0) {
    if (only_element > 0) {
      $.get("{Yii::$app->urlManager->createUrl('catalog/product-configurator')}", $(theForm).serialize() + '&only_element=' + only_element, function(data, status) {
        if (status == "success") {
            $('#product-configurator-element-' + only_element).replaceWith(data.product_configurator);
        }
      },'json');
    }
    $.get("{Yii::$app->urlManager->createUrl('catalog/product-configurator')}", $(theForm).serialize(), function(data, status) {
      if (status == "success") {
        $('#product-price-old').html(data.product_price);
        $('#product-price-current').html(data.product_price);
        $('#product-price-special').html(data.special_price);
        $('#product-attributes').replaceWith(data.product_attributes);
        if ( !(only_element > 0) ) {
          $('#product-configurator').replaceWith(data.product_configurator);
        }
        $('#product-price-configurator').html(data.configurator_price);
        $('#product-price-current').html(data.configurator_price);
        if ( typeof data.configurator_selection != 'undefined' ) {
            $('#configurator-selection').html(data.configurator_selection.map(function(val) {
                return('<span class="selection-product">' + val + '</span>');
            }).join('<br>'));
        }

        if (data.product_valid > 0) {
            if (data.product_in_cart) {
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
                  if (data.product_in_cart) {
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
                  if (data.product_in_cart) {
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
      update_attributes = update_template_options;
    }
  });
</script>
</div>
{/if}