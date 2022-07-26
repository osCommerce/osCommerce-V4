{use class="frontend\design\Info"}
{$rating = Info::getProductsRating($product.id)}
{if $rating > 0}
    ({Info::getProductsRating($product.id, 'count')})
{/if}