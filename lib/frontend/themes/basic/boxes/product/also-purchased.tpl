{use class="frontend\design\IncludeTpl"}
{use class="frontend\design\Info"}
{use class="Yii"}
<div class="heading-2">{$smarty.const.PRODUCT_ALSO_PURCHASED}</div>

<div class="products-box columns-{$settings[0].col_in_row}{if $settings[0].view_as == 'carousel'} products-carousel carousel-5{/if}">
{IncludeTpl::widget(['file' => 'boxes/products-listing.tpl', 'params' => ['products' => $products, 'settings' => $settings]])}
</div>

{if $settings[0].view_as == 'carousel'}
  <script type="text/javascript">
    tl('{Info::themeFile('/js/slick.min.js')}' , function(){

      var carousel = $('.carousel-5');
      var tabs = carousel.parents('.tabs');
      tabs.find('> .block').show();
      var show = {if $settings[0].col_in_row}{$settings[0].col_in_row}{else}4{/if};
      var width = carousel.width();
      if (width < 800 && show > 3) show = 3;
      if (width < 600 && show > 2) show = 2;
      if (width < 400 && show > 1) show = 1;
      {Info::addBoxToCss('slick')}
      $('.carousel-5 > div').slick({
        slidesToShow: show,
        slidesToScroll: show,
        slide: 'div',
        infinite: false
      });
      setTimeout(function(){ tabs.trigger('tabHide') }, 100)

    });
  </script>
{/if}


<script type="text/javascript">
  tl('{Info::themeFile('/js/main.js')}' , function(){
    $('.products-listing').inRow(['.image', '.name', '.price'], {if $settings[0].col_in_row}{$settings[0].col_in_row}{else}4{/if})
  });
</script>