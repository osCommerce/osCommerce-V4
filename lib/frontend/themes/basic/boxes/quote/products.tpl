<div class="cart-listing w-cart-listing{\frontend\design\Info::addBlockToWidgetsList('cart-listing')}">
    {if $ext = \common\helpers\Extensions::isAllowed('Quotations')}
        <div{if !$ext::optionIsPriceShow()} class="no-price"{/if}>
    {/if}
  <div class="headings">
    <div class="head remove">{$smarty.const.REMOVE}</div>
    <div class="head image">{$smarty.const.PRODUCTS}</div>
    <div class="head name"></div>
    <div class="head qty">{$smarty.const.QTY}</div>
    <div class="head price">{$smarty.const.PRICE}</div>
  </div>

  {foreach $products as $product}
    <div class="item">
      <div class="remove">{if $product.remove_link}<a href="{$product.remove_link}" class="remove-btn"><span style="display: none">{$smarty.const.REMOVE}</span></a>{/if}</div>
      <div class="image"><a href="{$product.link}"><img src="{$product.image}" alt=""></a></div>
      <div class="name">
        <a href="{$product.link}">{$product.name}</a> {if $product.stock_info.order_instock_bound && $smarty.const.TEXT_INSTOCK_BOUND_MARKER}<span class="attention_mark">{$smarty.const.TEXT_INSTOCK_BOUND_MARKER}</span>{/if}<br>
        {if $product.stock_info}
          <div class="{$product.stock_info.text_stock_code}"><span class="{$product.stock_info.stock_code}-icon">&nbsp;</span>{$product.stock_info.stock_indicator_text}</div>
        {/if}
				{use class="\frontend\design\boxes\product\Packs"}          
        {Packs::widget(['product' => $product])}
        <div class="attributes">
          {foreach $product.attr as $attr}
            <div class="">
              <strong>{$attr.products_options_name}:</strong>
              <span>{$attr.products_options_values_name}</span>
            </div>
          {/foreach}
        </div>
        {if $product.is_bundle}
          {foreach $product.bundles_info as $bundle_product }
          <div class="bundle_product">
            {$bundle_product.x_name}
            {if $bundle_product.with_attr}
              <div class="attributes">
                {foreach $bundle_product.attr as $attr}
                  <div class="">
                    <strong>{$attr.products_options_name}:</strong>
                    <span>{$attr.products_options_values_name}</span>
                  </div>
                {/foreach}
              </div>
            {/if}
          </div>
          {/foreach}
        {/if}
      </div>
        <div class="qty">
          {if $product.ga}
          <input type="hidden" name="cart_quantity[]" value="{$product.quantity}"/>
          <span class="qty-readonly">{$product.quantity}</span>
          {else}
              {if $product.is_pack > 0 }
                  <input type="hidden" name="cart_quantity[]" value="{$product.quantity}"/>
                  <div class="qty_cart_colors">
                      <span class="qc_title">{$smarty.const.UNIT_QTY}: </span>
                      <input type="text" name="cart_quantity_[{$product.id}][0]" value="{$product.units}" class="qty-inp-s" data-min="0"{if $product.in_stock != false} data-max="{$product.in_stock}"{/if}/>
                  </div>
                  <div class="qty_cart_colors">
                      <span class="qc_title">{$smarty.const.PACK_QTY}: </span>
                      <input type="text" name="cart_quantity_[{$product.id}][1]" value="{$product.packs}" class="qty-inp-s" data-min="0"{if $product.in_stock != false} data-max="{$product.in_stock/$product.packs}"{/if}/>
                  </div>
                  <div class="qty_cart_colors">
                      <span class="qc_title">{$smarty.const.CARTON_QTY}: </span>
                      <input type="text" name="cart_quantity_[{$product.id}][2]" value="{$product.packagings}" class="qty-inp-s" data-min="0"{if $product.in_stock != false} data-max="{$product.in_stock/($product.packs*$product.packagings)}"{/if}/>
                  </div>
              {else}
                <input type="text" name="cart_quantity[]" value="{$product.quantity}" class="qty-inp-s"
                        {if $product.quantity_max != false} data-max="{$product.quantity_max}"{/if}
                        {if $moq = \common\helpers\Extensions::isAllowed('MinimumOrderQty')}{$moq::setLimit($product.order_quantity_data)}{/if}
                        {if $oqs = \common\helpers\Extensions::isAllowed('OrderQuantityStep')}{$oqs::setLimit($product.order_quantity_data)}{/if}
                />
            {/if}
          {/if}
          {$product.hidden_fields}
        </div>
        {if $ext = \common\helpers\Extensions::isAllowed('Quotations')}
            <div class="price">{if $ext::optionIsPriceShow()}{$product.final_price}{/if}</div>
        {else}
            <div class="price">{$product.final_price}</div>
        {/if}
        {if $product.gift_wrap_allowed}
        <div class="gift-wrap"><label>{$smarty.const.BUYING_GIFT} ({$product.gift_wrap_price_formated}) <input type="checkbox" name="gift_wrap[{$product.id}]" class="check-on-off" {if $product.gift_wrapped} checked="checked"{/if}/></label></div>
        {/if}
    </div>
  {/foreach}

  {if $bound_quantity_ordered}
    <div class="checkout-attention-message">{sprintf($smarty.const.TEXT_INSTOCK_BOUND_MESSAGE, '<span class="attention_mark">'|cat:$smarty.const.TEXT_INSTOCK_BOUND_MARKER|cat:'</span>', '<span class="attention_mark">'|cat:$smarty.const.TEXT_INSTOCK_BOUND_MARKER|cat:'</span>')}</div>
  {/if}
  {if $oos_product_incart}
    <div class="checkout-attention-message">{$smarty.const.TEXT_INFO_OUT_OF_STOCK_IN_CART}</div>
  {/if}

    </div>
  <script type="text/javascript">
    tl(function(){
      $('.btn-to-checkout').each(function(){
  {if $allow_checkout == false}
          $(this).css({
            'opacity': '0.5',
            'cursor': 'default'
          });
        $(this).attr('data-href', $(this).attr('href')).removeAttr('href')
  {else}
        $(this).css({
          'opacity': '',
          'cursor': ''
        });
        if ($(this).attr('data-href')){
          $(this).attr('href', $(this).attr('data-href'))
        }
  {/if}
      })
    })
  </script>
</div>