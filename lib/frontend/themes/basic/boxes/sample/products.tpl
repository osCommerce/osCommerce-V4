<div class="cart-listing w-cart-listing{\frontend\design\Info::addBlockToWidgetsList('cart-listing')}">
  <div class="headings">
    <div class="head remove">{$smarty.const.REMOVE}</div>
    <div class="head image">{$smarty.const.PRODUCTS}</div>
    <div class="head name"></div>
  </div>

  {foreach $products as $product}
    <div class="item">
      <div class="remove">{if $product.remove_link}<a href="{$product.remove_link}" class="remove-btn"><span style="display: none">{$smarty.const.REMOVE}</span></a>{/if}</div>
      <div class="image"><a href="{$product.link}"><img src="{$product.image}" alt=""></a></div>
      <div class="name">
        <a href="{$product.link}">{$product.name}</a> {if $product.stock_info.order_instock_bound && $smarty.const.TEXT_INSTOCK_BOUND_MARKER}<span class="attention_mark">{$smarty.const.TEXT_INSTOCK_BOUND_MARKER}</span>{/if}<br>
        
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
      </div>
        {if $product.stock_info}
            <div class="price">&nbsp;</div>
            <div class="qty">
              <div class="{$product.stock_info.text_stock_code}">
                <span class="{$product.stock_info.stock_code}-icon">&nbsp;</span>
                  {$product.stock_info.stock_indicator_text}
              </div>
            </div>
        {/if}
    </div>
  {/foreach}


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