<div class="cart-listing w-cart-listing{\frontend\design\Info::addBlockToWidgetsList('cart-listing')} type-3">

  {foreach $products as $product}
    <div class="item">
      <div class="image"><a href="{$product.link}"><img src="{$product.image}" alt=""></a></div>
      <div class="name">
        {$product.quantity} x {$product.name} {if $product.stock_info.order_instock_bound && $smarty.const.TEXT_INSTOCK_BOUND_MARKER}<span class="attention_mark">{$smarty.const.TEXT_INSTOCK_BOUND_MARKER}</span>{/if}
        <div class="attributes">
					{use class="\frontend\design\boxes\product\Packs"}          
					{Packs::widget(['product' => $product])}
          {foreach $product.attr as $attr}
            <div class="">
              <strong>{$attr.products_options_name}:</strong>
              <span>{$attr.products_options_values_name}</span>
            </div>
          {/foreach}
          {if $product.gift_wrapped}
            <div class="">
              <strong>{$smarty.const.GIFT_WRAP_OPTION}:</strong>
              <span>{$smarty.const.GIFT_WRAP_VALUE_YES}</span>
            </div>
          {/if}
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
        {if $product.stock_info}
          <div class="{$product.stock_info.text_stock_code}"><span class="{$product.stock_info.stock_code}-icon">&nbsp;</span>{$product.stock_info.stock_indicator_text}</div>
        {/if}
      </div>
      <div class="right-area">
        <div class="price">{*$product.final_price*}</div>
      </div>
    </div>
  {/foreach}

</div>