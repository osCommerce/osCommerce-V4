<div class="cart-listing w-cart-listing{\frontend\design\Info::addBlockToWidgetsList('cart-listing')} type-2">
  <div class="headings">
    <div class="head image">{$smarty.const.PRODUCTS}</div>
    <div class="head name">&nbsp;</div>
    <div class="head qty">{$smarty.const.QTY}</div>
    <div class="head price">{$smarty.const.PRICE}</div>
  </div>

  {foreach $products as $product}
    <div class="item">
      <div class="image"><a href="{$product.link}"><img src="{$product.image}" alt=""></a></div>
      <div class="name">
        <a href="{$product.link}">{$product.name}</a> {if $product.stock_info.order_instock_bound && $smarty.const.TEXT_INSTOCK_BOUND_MARKER}<span class="attention_mark">{$smarty.const.TEXT_INSTOCK_BOUND_MARKER}</span>{/if}
        <div class="attributes">
            {use class="\frontend\design\boxes\product\Packs"}          
            {Packs::widget(['product' => $product])}
          {if isset($product.attr)}
            {foreach $product.attr as $attr}
            <div class="">
              <strong>{$attr.products_options_name}:</strong>
              <span>{$attr.products_options_values_name}</span>
            </div>
            {/foreach}
          {/if}
          {if isset($product.gift_wrapped) && $product.gift_wrapped}
            <div class="">
              <strong>{$smarty.const.GIFT_WRAP_OPTION}:</strong>
              <span>{$smarty.const.GIFT_WRAP_VALUE_YES}</span>
            </div>
          {/if}
        </div>
        {if isset($product.is_bundle) && $product.is_bundle}
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
        {if isset($product.stock_info) && $product.stock_info}
          <div class="{$product.stock_info.text_stock_code}"><span class="{$product.stock_info.stock_code}-icon">&nbsp;</span>{$product.stock_info.stock_indicator_text}</div>
        {/if}
      </div>
        <div class="qty">
          {$product.quantity}
        </div>
        {if $ext = \common\helpers\Extensions::isAllowed('Quotations')}
            <div class="price">{if $ext::optionIsPriceShow()}{$product.final_price}{/if}</div>
        {else}
            <div class="price">{$product.final_price}</div>
        {/if}
    </div>
  {/foreach}

</div>