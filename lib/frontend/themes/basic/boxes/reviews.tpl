{use class="frontend\design\Info"}
<div class="reviews">
<div class="heading-3">{$smarty.const.REVIEWS}</div>

  <div class="reviews-list">
  {foreach $items as $item}
    <div class="item">
      <div class="product-image"><a href="{$item.link}">{$item.img}</a></div>
      <div class="product-name"><a href="{$item.link}">{strip_tags($item.products_name)}</a></div>

      <div class="rating"><span class="rating-{$item.reviews_rating}"></span> {if $smarty.const.DISPLAY_REVIEW_RATING_TITLE=='True' && $item.reviews_rating_description}<span class="rating-description">{$item.reviews_rating_description}</span>{/if}</div>
      <div class="date">{$item.date}</div>
      <div class="review">{strip_tags($item.reviews_text)}</div>

      <div class="author">{strip_tags($item.customers_name)} </div>
    </div>
  {/foreach}
</div>
</div>
{if $lazy_load}
  <script>
      tl('{Info::themeFile('/js/jquery.lazy.min.js')}', function(){
          $('.lazy').lazy( { bind: 'event' } );
      })
  </script>
{/if}