{use class="frontend\design\Info"}
<div class="wishlist-page">
<h1>{$smarty.const.HEADING_TITLE}</h1>

{if count($products)==0}
  <p>{$smarty.const.TEXT_NO_PRODUCTS_IN_WISHLIST}</p>


{else}
<div class="cart-listing w-cart-listing{\frontend\design\Info::addBlockToWidgetsList('cart-listing')}">
  <div class="headings">
    <div class="head remove">{$smarty.const.REMOVE}</div>
    <div class="head image">{$smarty.const.PRODUCTS}</div>
    <div class="head name"></div>
    <div class="head qty"></div>
    <div class="head price">{$smarty.const.PRICE}</div>
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
      </div>
        <div class="qty">
          {if $product.status}
            {if $product.oos}
              {$smarty.const.TEXT_PRODUCT_OUT_STOCK}
            {else}
              <a href="{$product.move_in_cart}">{$smarty.const.TEXT_MOVE_TO_CART_WISHLIST}</a>
            {/if}
          {else}
            {$smarty.const.TEXT_PRODUCT_DISABLED}
          {/if}
        </div>
        <div class="price">{$product.final_price_formatted}</div>
    </div>
  {/foreach}
</div>
  <div class="buttons">
      <div class="left-buttons"><span class="btn btn-cancel">{$smarty.const.IMAGE_BUTTON_CONTINUE}</span></div>
  </div>
{/if}
<script type="text/javascript">
  tl('{Info::themeFile('/js/main.js')}', function(){


    $('.cart-page .btn-cancel').on('click', function(){
      $('.popup-box-wrap:last').remove();
    });


    $('.remove-btn').on('click', function(){
      $.get($(this).attr('href'), function(d){
        $('.wishlist-page').replaceWith(d)
      });
      return false
    });


  })
</script>
</div>