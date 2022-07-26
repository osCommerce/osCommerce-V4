<div id="cart-box-quote" class="cart-box{if $settings[0].show_products == 'dropdown'} hover-box{/if}">
  <a class="cart-box-link" href="{tep_href_link('quote-cart/index')}">
    <span class="no-text">
      <strong class="strong">{$smarty.const.TEXT_HEADING_QUOTE_CART}</strong>
      {if $settings[0].items}<span class="items">{$count_contents} {$smarty.const.BOX_SHOPPING_CART_FULL}</span>{/if}
      {if $settings[0].total}<span>{$total}</span>{/if}
    </span>
  </a>
  {if $settings[0].show_products}
  <div class="cart-content">
    {foreach $products as $item}
      <a href="{$item.link}" class="item">
        <span class="image"><img src="{$item.image}" alt="{str_replace('"', '″', $item.name)}"></span>

        <span class="name"><span class="qty">{$item.quantity}</span>{$item.name}</span>
        <span class="price">{$item.price}</span>
      </a>
    {/foreach}
    <div class="cart-total">{$smarty.const.SUB_TOTAL} {$total}</div>
    <div class="buttons">
      <div class="left-buttons"><a href="{tep_href_link('quote-cart')}" class="btn">{$smarty.const.TEXT_HEADING_SHOPPING_CART}</a></div>
    </div>
  </div>
  {/if}

  <script type="text/javascript">
    tl(function(){
      var cart_change = function(){
        var cart_id = $('#cart-box-quote').parent().attr('id').substring(4);
        $.get("{tep_href_link('get-widget/one')}", {
          id: cart_id,
          action: 'main'
        }, function(d){
          $('#box-'+cart_id).html(d)
        })
      };
        $(window).one('cart_change', cart_change)
    })
  </script>
  {if $settings[0].show_products == 'dropdown'}
    <script type="text/javascript">
      tl(function(){
        var cart_content = $('.cart-box.hover-box .cart-content');
        var key = true;
        var cart_content_position = function(){
          if (key){
            key = false;
            setTimeout(function(){
              cart_content.show();
              key = true;
              cart_content.css({
                'top': $('.cart-box.hover-box').height() - 1,
                'width': '410',
                'right': 0
              });
              if (cart_content.width() > $(window).width()){
                var w = $(window).width() * 1 - 20;
                cart_content.css({
                  width: w + 'px'
                })
              }
              if (cart_content.offset().left < 0){
                var r = cart_content.offset().left * 1 - 15;
                cart_content.css({
                  right: r + 'px'
                })
              }
              cart_content.hide();
            }, 300)
          }
        };

        cart_content_position();
        $(window).off('resize', cart_content_position).on('resize', cart_content_position)
      })
    </script>
  {/if}
</div>