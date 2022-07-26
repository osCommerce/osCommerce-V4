<h1>{$smarty.const.HEADING_TITLE}</h1>

{if count($products)==0}
  <p>{$smarty.const.TEXT_NO_PRODUCTS_IN_WISHLIST}</p>

  <div class="buttons">
    <div class="buttons">
      <div class="left-buttons"><a href="{$link_back_href}" class="btn">{$smarty.const.IMAGE_BUTTON_CONTINUE}</a></div>
    </div>
  </div>

{else}
<div class="cart-listing w-cart-listing{\frontend\design\Info::addBlockToWidgetsList('cart-listing')}">
  <div class="headings">
    <div class="head remove">{$smarty.const.TEXT_REMOVE_CART}</div>
    <div class="head image">{$smarty.const.PRODUCTS}</div>
    <div class="head name"></div>
    <div class="head qty">{$smarty.const.PRICE}</div>
    <div class="head price"></div>
  </div>

  {foreach $products as $product}
    <div class="item">
      <div class="remove">{if $product.remove_link}<a href="{$product.remove_link}" class="remove-btn"><span style="display: none">{$smarty.const.REMOVE}</span></a>{/if}</div>
      <div class="image">
        {if $product.status}
          <a href="{$product.link}"><img src="{$product.image}" alt="{$product.name|escape:'html'}" title="{$product.name|escape:'html'}"></a>
        {else}
          <img src="{$product.image}" alt="{$product.name|escape:'html'}" title="{$product.name|escape:'html'}">
        {/if}
      </div>
      <div class="name">
        {if $product.status}
          <a href="{$product.link}">{$product.name}</a>
        {else}
          {$product.name}
        {/if}
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
        {if $product.stock_info}
          <div class="{$product.stock_info.text_stock_code}"><span class="{$product.stock_info.stock_code}-icon">&nbsp;</span>{$product.stock_info.stock_indicator_text}</div>
        {/if}
      </div>
      <div class="right-area">
        <div class="qty">
          {$product.final_price_formatted}
        </div>
        <div class="price">
          {if $product.status}
            {if $product.stock_info}
              {if $product.stock_info.allow_out_of_stock_checkout}
                <a href="{$product.move_in_cart}" class="view_link">{$smarty.const.TEXT_MOVE_TO_CART_WISHLIST}</a>
              {/if}
            {else}
              <a href="{$product.move_in_cart}" class="view_link">{$smarty.const.TEXT_MOVE_TO_CART_WISHLIST}</a>
            {/if}
          {else}
            {$smarty.const.TEXT_PRODUCT_DISABLED}
          {/if}
        </div>
      </div>
    </div>
  {/foreach}
</div>
  <div class="buttons">
    <div class="buttons">
      <div class="left-buttons"><a href="{$link_back_href}" class="btn">{$smarty.const.IMAGE_BUTTON_BACK}</a></div>
    </div>
  </div>
{/if}

