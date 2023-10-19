{use class="Yii"}
{use class = "yii\helpers\Html"}
{use class="frontend\design\Info"}
{\frontend\design\Info::addBoxToCss('form')}
<script>
{if $products_carousel}
    var useCarousel = true;
{else}
    var useCarousel = false;
{/if}
</script>
<div class="buttons" id="product-buttons">
    {if $cart_button == 1}
        <span id="btn-cart"{if $product_has_attributes || !$stock_info.flags.add_to_cart } style="display:none;"{/if}>
            {$buttonArray[$products_id]['quantity']='$("#qty").val()'}
            <button type="submit" class="btn-2 add-to-cart {$buttonArray[$products_id]['buttonId']}"{if $product_in_cart && $show_in_cart_button != 'no'} style="display: none"{/if}{\common\components\google\widgets\GoogleTagmanger::onclickAddToCart($products_id, '$("#qty").val()')}>
            {if !empty($stock_info.preorder_only) }
              {$smarty.const.BUTTON_TEXT_PREORDER}
            {else}
              {$smarty.const.ADD_TO_CART}
            {/if}
            </button>
            {$paypal_block}
            <a href="{tep_href_link(FILENAME_SHOPPING_CART)}" class="btn-2 in-cart"{if !$product_in_cart || $show_in_cart_button == 'no'} style="display: none"{/if}>{$smarty.const.TEXT_IN_YOUR_CART}</a>
        </span>
        <span class="btn-2" id="btn-cart-none"{if not $product_has_attributes || $stock_info.flags.add_to_cart} style="display:none;"{/if}>{$smarty.const.ADD_TO_CART}</span>
    {/if}

    {if isset($stock_info.flags.out_stock_action) && $stock_info.flags.out_stock_action == 2}
        {$contactName = Info::themeSetting('contact_name')}
        {if !$contactName}{$contactName = 'contact'}{/if}
        <a class="btn btn-notify-prod" {if $product_has_attributes || !(isset($stock_info.flags.notify_instock) && $stock_info.flags.notify_instock)} style="display:none;"{/if} href="{Yii::$app->urlManager->createUrl([$contactName, 'products_id' => $products_id])}">{$smarty.const.CONTACT_ABOUT_PRODUCT}</a>
    {else}
        <span class="btn btn-notify-prod" id="btn-notify"{if $product_has_attributes || !(isset($stock_info.flags.notify_instock) && $stock_info.flags.notify_instock)} style="display:none;"{/if}>
        {if $ext = \common\helpers\Acl::checkExtensionAllowed('NotifyBackInStockWaitDiscount', 'allowed')}
            <span id="notify_when_stock_text">{$ext::notify_when_stock_text($products_id)}</span>
            <span id="notify_when_stock_description" style="display:none;">{$ext::notify_when_stock_description($products_id)}</span>
        {else}
            {$smarty.const.NOTIFY_WHEN_STOCK}
        {/if}
        </span>
    {/if}

    {if !$settings['hide_additional_buttons']}
        {\frontend\design\boxes\product\ButtonsQuote::widget()}
    {/if}

    {if isset($settings.show_added) && $settings.show_added}
        <div class="loaded-qty"{if !$product_in_cart} style="display: none"{/if}>(<span>{$product_in_cart}</span> {$smarty.const.TEXT_LISTING_ADDED})</div>
    {/if}

</div>
{foreach \common\helpers\Hooks::getList('box/product', 'button-buy-attribute') as $filename}
    {include file=$filename}
{/foreach}
<script type="text/javascript">
    window.tr = {
        PREVIOUS_PRODUCT: '{$smarty.const.PREVIOUS_PRODUCT}',
        NEXT_PRODUCT: '{$smarty.const.NEXT_PRODUCT}',
    };
  tl('{Info::themeFile('/js/main.js')}' , function(){
      var $box = $('#box-{$id}');
    if (useCarousel){
        window.pCarousel.restoreItems();
        window.pCarousel.buildCursor(parseInt('{$products_id}'));
    }
    $('#btn-notify').on('click', function() {
      alertMessage(`
      <div class="notify-form">
          {Html::beginForm(Yii::$app->urlManager->createUrl('catalog/product-notify'), 'get')|strip}
            <div class="middle-form">
              <div class="heading-3">{$smarty.const.BACK_IN_STOCK}</div>
              <div class="col-full">` + $('#notify_when_stock_description').html() + `</div>
              <div class="col-full"><label>{$smarty.const.TEXT_NAME}<input type="text" id="notify-name"></label></div>
              <div class="col-full"><label>{$smarty.const.ENTRY_EMAIL_ADDRESS}<input type="text" id="notify-email"></label></div>
              <div class="center-buttons"><button type="submit" class="btn">{$smarty.const.NOTIFY_ME}</button></div>
            </div>
          {Html::endForm()}
      </div>`);
        $('.notify-form').closest('.alert-message').removeClass('alert-message');
        $('.notify-form form').on('submit', function(){
            ajax_notify_product();
			return false;
        })
    });

    var product_form = $('#product-form');

    {assign var=after_add value=Info::themeSetting('after_add')}
    {if $after_add == 'popup'}
    product_form.popUp({
      box_class: 'cart-popup',
      opened: function(){
          {if $show_in_cart_button != 'no'}
        $('.add-to-cart').hide();
        $('.in-cart').show();
        $('.qty-input').hide()
          {/if}
          var $loadedQty = $('.loaded-qty span', $box);
          var oldQty = $loadedQty.text() || 0;
          var currentQty = $('.w-product-quantity input[name="qty"]').val() || 1;
          $loadedQty.html(oldQty*1 + currentQty*1);
          $('.loaded-qty', $box).show()
      }
    });
    {elseif $after_add == 'animate'}

    {/if}


  });


  function ajax_notify_product() {
    if ($('#notify-name').val() < {$smarty.const.ENTRY_FIRST_NAME_MIN_LENGTH}) {
        alertMessage(`{sprintf($smarty.const.NAME_IS_TOO_SHORT, $smarty.const.ENTRY_FIRST_NAME_MIN_LENGTH)}`);
    } else {
      var email = $("#notify-email").val();
      if (!isValidEmailAddress(email)) {
          alertMessage(`{$smarty.const.ENTER_VALID_EMAIL}`);
      } else {
        var uprid = '&products_id=' + $('[name="products_id"]').val();
      if ($('input[name=inv_uprid]').length) {
        error = true;
        if ($('input[name=inv_uprid]:checked').length) {
          error = false;
          uprid += '&uprid=' + $('input[name=inv_uprid]:checked').val();
        }
      } else {
        var error = false;
        $('[name^="id\\["]').each(function(index) {
          if ($(this).is('select')) {
            uprid += '&id[' + this.name.match(/id\[([-\d]+)\]/)[1] + ']=' + $(this).val();
            if (!parseInt($(this).val())) {
              error = true;
            }
          } else if ($(this).is('input')) {
            if ($(this).is(':checked')) {
              uprid += '&id[' + this.name.match(/id\[([-\d]+)\]/)[1] + ']=' + $(this).val();
            }
            if (!$('input[name="' + $(this).attr('name') + '"]:checked') || !parseInt($('input[name="' + $(this).attr('name') + '"]:checked').val())) {
              error = true;
            }
          }
        });
      }
        if (error) {
            alertMessage(`{$smarty.const.PLEASE_CHOOSE_ATTRIBUTES}`);
        } else {
          $.ajax({
            url: "{Yii::$app->urlManager->createUrl('catalog/product-notify')}",
            data: "name=" + $('#notify-name').val() + "&email=" + $('#notify-email').val() + uprid + "&_csrf=" + $('.notify-form input[name="_csrf"]').val(),
            success: function(msg) {
              $('.notify-form').replaceWith(`<div class="notify-form">${ msg}</div>`);
            }
          });
        }
      }
    }
    return false;
  }
{literal}
  function isValidEmailAddress(emailAddress) {
    var pattern = new RegExp(/^(("[\w-\s]+")|([\w-]+(?:\.[\w-]+)*)|("[\w-\s]+")([\w-]+(?:\.[\w-]+)*))(@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$)|(@\[?((25[0-5]\.|2[0-4][0-9]\.|1[0-9]{2}\.|[0-9]{1,2}\.))((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\.) {2}(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\]?$)/i);
    return pattern.test(emailAddress);
  }
{/literal}
</script>