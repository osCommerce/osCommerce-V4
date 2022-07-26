{use class="frontend\design\IncludeTpl"}
{use class="frontend\design\Info"}
<div class="bestsellers">
  <div class="heading-3">{$smarty.const.BEST_SELLERS}</div>

  {if !$settings[0].view_as}
    <div class="bestsellers-list">
      {foreach $products as $product}
        <div class="item"><span class="counter">{counter}</span><a href="{$product.link}" class="name">{$product.products_name}</a></div>
      {/foreach}
    </div>
  {else}

    <div class="products-box columns-{$settings[0].col_in_row}{if $settings[0].view_as == 'carousel'} products-carousel carousel{/if}">
      {IncludeTpl::widget(['file' => 'boxes/products-listing.tpl', 'params' => ['only_column'=>true, 'products' => $products, 'settings' => $settings, 'languages_id' => $languages_id]])}
    </div>
    <div class="view-all"><a href="{tep_href_link('catalog/products-new')}" class="btn">{$smarty.const.VIEW_ALL_NEW_PRODUCTS}</a></div>

  {if $settings[0].view_as == 'carousel'}
    <script>
      tl('{Info::themeFile('/js/slick.min.js')}', function(){

        var box = $('#box-{$id}');

        var carousel = $('.carousel', box);
        var tabs = carousel.parents('.tabs');
        tabs.find('> .block').show();

        var show = {if $settings[0].col_in_row}{$settings[0].col_in_row}{else}4{/if};

        {Info::addBoxToCss('slick')}
        $('.carousel > div', box).slick({
          slidesToShow: show,
          slidesToScroll: show,
          infinite: false,
          responsive: [
            {foreach $settings.colInRowCarousel as $width => $val}
            {
              breakpoint: {$width},
              settings: {
                slidesToShow: {$val},
                slidesToScroll: {$val}
              }
            },
            {/foreach}
          ]
        });
        setTimeout(function(){ tabs.trigger('tabHide') }, 100)

      })
    </script>
  {/if}
  {/if}
</div>
