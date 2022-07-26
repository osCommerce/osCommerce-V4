{use class="frontend\design\Info"}
{$rating = Info::getProductsRating($product.id)}
{if $rating > 0}
    <a href="{$product.link}#reviews"><span class="rating-{$rating}"></span></a>
{/if}