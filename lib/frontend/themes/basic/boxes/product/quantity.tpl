{use class="Yii"}
{use class="frontend\design\Info"}
{if \common\helpers\Acl::checkExtensionAllowed('PackUnits', 'allowed')}
{if $product.pack_unit > 0 || $product.packaging > 0}
<div class="qty_packs"{if !$show_quantity_input} style="display: none"{/if}>
<div class="qty-input"{if $product_in_cart} style="display: none"{/if}>
<div class="qty_t">{$smarty.const.UNIT_QTY}:</div>
  <div class="input">{$product_in_cart}
  {if $product.pack_unit > 0 || $product.packaging > 0}
    {$order_quantity_data.order_quantity_minimal = 0}
  {/if}
        <span class="price_1" id="product-price-current">{$single_price['unit']}</span>
        <input type="text" name="qty_[0]" value="{if $qty != ''}0{else}1{/if}" class="qty-inp check-spec-max"  data-type="unit" {if $quantity_max>0} data-max="{$quantity_max}"{/if} data-min="{$order_quantity_data.order_quantity_minimal}"  {if $order_quantity_data.order_quantity_step>1} data-step="{$order_quantity_data.order_quantity_step}"{/if}>
        <input type="hidden" name="qty[0]" value="0" class="depended" data-type="unit" data-stepmareed="{$order_quantity_data.order_quantity_step}"/>
				<span class="qty_price"></span>
  </div>
  </div>

{if $product.pack_unit > 0}
<div class="qty-input"{if $product_in_cart} style="display: none"{/if}>
	<div class="qty_t">{$smarty.const.PACK_QTY}:<span>({$product.pack_unit} items)</span></div>
  <div class="input inps">
        <span class="price_1">{$single_price['pack']}</span>
        <input type="text" name="qty_[1]" value="0" class="qty-inp check-spec-max" data-type="pack_unit" data-min="0"  {if $quantity_max>0} data-max="{floor($quantity_max/$product.pack_unit)}"{/if} >
        <input type="hidden" name="qty[1]" value="0" class="depended"  {if $product.pack_unit>0} data-step="1"{/if} data-stepmareed="{$product.pack_unit}" />
				<span class="qty_price"></span>
  </div>
  </div>
{/if}
{if $product.packaging > 0}
<div class="qty-input"{if $product_in_cart} style="display: none"{/if}>
	<div class="qty_t">{$smarty.const.CARTON_QTY}:<span>({$product.packaging * $product.pack_unit} items)</span></div>
  <div class="input inps">
        <span class="price_1">{$single_price['package']}</span>
        <input type="text" name="qty_[2]" value="0" class="qty-inp"  data-type="packaging" data-min="0" {if $quantity_max>0} data-max="{floor($quantity_max/($product.packaging*$product.pack_unit))}"{/if} >
        <input type="hidden"  name="qty[2]" value="0" class="depended" data-min="0" {if $product.packaging>0} data-step="1"{/if}  data-stepmareed="{$product.packaging * $product.pack_unit}"/>
				<span class="qty_price"></span>
  </div>
	</div>
{/if}
<div class="total-qty after">
	<div class="qty_t">{$smarty.const.TEXT_TOTAL}:</div>
  <div class="input inps">
			<span >{$smarty.const.QTY}:</span>
      <span class="price_2" id="total_qty"></span>
      <span class="qty_price" id="total_sum"></span>
  </div>
</div>
</div>
{else}
    {if !$disapear_quantity_input}
<div class="qty-input"{if !$show_quantity_input && strtolower(\common\helpers\PlatformConfig::getVal('SHOW_QUANTITY_INPUT_FOR_QUOTE_BUTTON', 'false')) != 'true' || $product_in_cart} style="display: none"{/if}>
  <label class="product-quantity-label" for="qty">{output_label const="QTY"}</label>
  <div class="input">
    <input type="text" id="qty" name="qty" value="{if $qty != ''}{$qty}{else}1{/if}" class="qty-inp"{if $quantity_max>0} data-max="{$quantity_max}"{/if}
        {if \common\helpers\Acl::checkExtensionAllowed('MinimumOrderQty', 'allowed')}{\common\extensions\MinimumOrderQty\MinimumOrderQty::setLimit($order_quantity_data)}{/if}
        {if \common\helpers\Acl::checkExtensionAllowed('OrderQuantityStep', 'allowed')}{\common\extensions\OrderQuantityStep\OrderQuantityStep::setLimit($order_quantity_data)}{/if}
    />
  </div>
</div>
    {/if}
{/if}
<script type="text/javascript">
    tl('{Info::themeFile('/js/main.js')}', function(){

      {\frontend\design\Info::addBoxToCss('quantity')}
        $('input.qty-inp').quantity();

        $('input.qty-inp').not('inited').on('check_quantity keyup', function(e, param){
          var $qtyInp = $(this);
            $(window).trigger('changedQty', $qtyInp.val());
          $qtyInp.addClass('inited');
          /// either form on list, or div.item or cart_quantity form
          var $form = $(document.forms['cart_quantity']);
          var pid = $('input[name="products_id"]:first', $form).val();
          if ($qtyInp.hasClass('qty-inp-nf') || $qtyInp.parents('.products-listing').length>0) {
            $form = $(this).closest(".item");
            pid = $('input[name="products_id[]"], input[name="pid[]"], input[name="products_id"]', $form).val();
          }
          var data = {
            'pid': pid,
            'qty': ($(this).data('value-real') || $(this).val()),
            'type': $(this).data('type'),
            _csrf: $('input[name=_csrf]').val(),
          };
          //packaging/cartons
          var packs = ($(this).attr('name').substring(0,4) == 'qty_');
          if ($('input[name="qty_[0]"]', $form).length>0){
            data['qty_[0]'] = $('input[name="qty_[0]"]', $form).val();
          }
          if ($('input[name="qty_[1]"]', $form).length>0){
            data['qty_[1]'] = $('input[name="qty_[1]"]', $form).val();
          }
          if ($('input[name="qty_[2]"]', $form).length>0){
            data['qty_[2]'] = $('input[name="qty_[2]"]', $form).val();
          }
          var _attributes = $('.attributes select, .attributes input:radio:checked, .bundle_attributes input:radio:checked, .bundle_attributes select', $form);
          if (!$form.hasClass('item')) {
            _attributes = _attributes.filter(function() { return $(this).closest(".item").length == 0; });
          }
          if (_attributes.size()){
            $.each(_attributes, function(i, item){
                data[item.name] = item.value;
            })
          }

        if (typeof data.pid !== 'undefined'){
          $.post('catalog/get-price', data, function(data, status){
            if (status == 'success'){
              if (packs) {
                var _p = $qtyInp.val() * $qtyInp.parent().next().data('stepmareed');
                if (_p > 0 && $qtyInp.data('type') != 'unit') {
                  _p = '<span class="pr_attr">' + data.type + '</span>';
                } else {
                  _p = '';
                }
                $qtyInp.parent().next().val($qtyInp.val()* $qtyInp.parent().next().data('stepmareed'));
                $qtyInp.parent().next().next().html( data.price);

                if($qtyInp.parent().find('.pr_attr').length > 0){
                        $qtyInp.parent().find('.pr_attr').html(_p);
                }else{
                        $qtyInp.parent().append(_p);
                }
                $qtyInp.parent().prev().html( data.single_price);
                $('#total_sum').text(data.total_price);
                $('#total_qty').text(data.total_quantity);
              } else if ($qtyInp.hasClass('qty-inp-nf') || $qtyInp.parents('.products-listing').length>0) {
                if (data.special_price.length==0) {
                  $('.current', $form).html(data.product_price).show();
                  $('.specials, .old', $form).hide();
                } else {
                  $('.specials', $form).html(data.special_price);
                  $('.old', $form).html(data.product_price);
                  $('.specials, .old', $form).show();
                  $('.current', $form).hide();
                }
              } else {
                ///2do add bundle total price update here (attributes are not sent) check pc-conf
              }
              ///
              if ($qtyInp.hasClass('qty-inp-nf') || $qtyInp.parents('.products-listing').length>0) {
                // could be totals and buy all.
                var $list = $qtyInp.closest('.products-listing').parent().parent();
                updateXListSummary($list);
              }

              if (data.hasOwnProperty('total_price_clear')) {
                  $(window).trigger('getFullFinalPrice', data.total_price_clear);
              }

            }
          }, 'json');
        }
          
        })

    })
</script>
{else}
    {if !$disapear_quantity_input}
<div class="qty-input"{if !$show_quantity_input && strtolower(\common\helpers\PlatformConfig::getVal('SHOW_QUANTITY_INPUT_FOR_QUOTE_BUTTON', 'false')) != 'true' || $product_in_cart} style="display: none"{/if}>
  <label for="qty">{output_label const="QTY"}</label>
  <div class="input">
    <input type="text" id="qty" name="qty" value="{if $qty != ''}{$qty}{else}1{/if}" class="qty-inp"{if $quantity_max>0} data-max="{$quantity_max}"{/if}
        {if \common\helpers\Acl::checkExtensionAllowed('MinimumOrderQty', 'allowed')}{\common\extensions\MinimumOrderQty\MinimumOrderQty::setLimit($order_quantity_data)}{/if}
        {if \common\helpers\Acl::checkExtensionAllowed('OrderQuantityStep', 'allowed')}{\common\extensions\OrderQuantityStep\OrderQuantityStep::setLimit($order_quantity_data)}{/if}
    />
  </div>
</div>
<script type="text/javascript">
  tl('{Info::themeFile('/js/main.js')}', function(){
    {\frontend\design\Info::addBoxToCss('quantity')}
    $('input.qty-inp').quantity();
  })
</script>
    {/if}
{/if}
<script>
    tl(function(){
        $('input.qty-inp').on('qty_max', function(e, qty, max){
            //see  themes/basic/js/main.full.js (+) - bigger disabled (and should) when qty == max but message shouldn't be shown
            if (qty > max) {
                var contentText = '{$smarty.const.SELECTED_TOO_MANY_ITEMS|escape}';
                var buttonOk = '{$smarty.const.CANCEL|escape}';
                var buttonAdd = '{$smarty.const.ADD_S_TO_CART|escape}';
                var $addButton = $('.w-product-buttons button')

                var content = $(`
    <div class="qty-more-max">
        <div class="qty-more-max-content">${ contentText.replace('%s', max)}</div>
        <div class="buttons">
            <span class="btn btn-close">${ buttonOk}</span>
            ${ $addButton.length ? `<span class="btn btn-add">${ buttonAdd.replace('%s', max)}</span>` : ''}
        </div>
    </div>`);

                alertMessage(content)

                $('.btn-close', content).on('click', function(){
                    $(this).closest('.popup-box-wrap').find('.pop-up-close').trigger('click')
                })
                if ($addButton.length) {
                    $('.btn-add', content).on('click', function(){
                        $(this).closest('.popup-box-wrap').find('.pop-up-close').trigger('click')
                        $addButton.trigger('click')
                    })
                }
            }
        })
    })
</script>