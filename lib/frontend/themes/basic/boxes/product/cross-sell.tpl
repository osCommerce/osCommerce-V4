{use class="frontend\design\IncludeTpl"}
{use class="frontend\design\Info"}
{use class="Yii"}
<div class="heading-2">
{if empty($settings[0].custom_title) || empty(constant($settings[0].custom_title)) }
  {$smarty.const.WE_ALSO_RECOMMEND}
{else}
  {constant($settings[0].custom_title)}
{/if}
</div>
<div class="cross-sell-box cross-sell-box-container {$settings['listing_type']}">
{$col_in_row = $settings[0].col_in_row}
{if $settings[0]['show_cart_button']}
  {$col_in_row--}
{/if}
<div class="products-box columns-{$col_in_row}{if $settings[0].view_as == 'carousel'} products-carousel carousel-6{/if}">
{IncludeTpl::widget(['file' => 'boxes/products-listing.tpl', 'params' => ['products' => $products, 'settings' => $settings]])}
</div>

{if $settings[0]['show_cart_button']}
<div class="cross-sell-box-summary box-summary columns-1">
  <div class="cross-sell-box-products box-products"></div>
  <div class="cross-sell-box-totals box-totals"></div>
  <div class="cross-sell-box-buttons box-buttons" style="position:relative">
    <a href="#" class="select-all" title="{$smarty.const.TEXT_SELECT_ALL}">{$smarty.const.TEXT_SELECT_ALL}</a> <a href="#" class="select-none" style="display:none" title="{$smarty.const.TEXT_SELECT_NONE}">{$smarty.const.TEXT_SELECT_NONE}</a>
    <div class="buy-button"><a class="btn-1 btn-buy btn-buy-aj btn-buy-all-aj add-to-cart disable-buy-form" href="{Yii::$app->urlManager->createUrl(['shopping-cart', 'action' => 'add_all'])}" title="{$smarty.const.ADD_ALL_TO_CART}">{$smarty.const.ADD_ALL_TO_CART}</a>
      </div>
  </div>
</div>
{/if}
</div>

{if $settings[0].view_as == 'carousel'}
  <script type="text/javascript">
    tl(['{Info::themeFile('/js/main.js')}', '{Info::themeFile('/js/slick.min.js')}'], function(){


      var carousel = $('.carousel-6');
      var tabs = carousel.parents('.tabs');
      tabs.find('> .block').show();
      var show = {if $settings[0].col_in_row}{$settings[0].col_in_row}{else}4{/if};
      {Info::addBoxToCss('slick')}
      $('.carousel-6 > div').slick({
        slidesToShow: show,
        slidesToScroll: show,
        slide: 'div',
        infinite: false,
        responsive: [
          {
            breakpoint: 800,
            settings: {
              slidesToShow: 3,
              slidesToScroll: 3
            }
          },
          {
            breakpoint: 600,
            settings: {
              slidesToShow: 2,
              slidesToScroll: 2
            }
          },
          {
            breakpoint: 400,
            settings: {
              slidesToShow: 1,
              slidesToScroll: 1
            }
          }
        ]
      });
      setTimeout(function(){ tabs.trigger('tabHide') }, 100)
    })
  </script>
{/if}

<script type="text/javascript">
  tl('{Info::themeFile('/js/main.js')}', function(){
    $('.products-listing').inRow(['.image', '.name', '.price'], {if $settings[0].col_in_row}{$settings[0].col_in_row}{else}4{/if})
  })
  
</script>