{use class="\frontend\design\boxes\PagingBar"}

{if $reviews == ''}
    <p>{$smarty.const.NO_PRODUCT_REVIEW}</p>
{else}

    {if $settings[0].pagination == 'top' || $settings[0].pagination == 'top-bottom'}
        {PagingBar::widget(['params' => ['listing_split' => $reviews_split, 'this_filename' => 'reviews']])}
    {/if}

    <div class="reviews-list">
        {foreach $reviews as $item}
            <div class="item">
                <div class="product-image"><a href="{$item.link}">{$item.img}</a></div>
                <div class="product-name"><a href="{$item.link}">{$item.products_name}</a></div>

                <div class="rating"><span class="rating-{$item.reviews_rating}"></span></div>
                <div class="date">{$item.date}</div>
                <div class="review">{$item.reviews_text}</div>

                <div class="author">{$item.customers_name} </div>
            </div>
        {/foreach}
    </div>

{if $settings[0].pagination == 'bottom' || $settings[0].pagination == 'top-bottom'}
    {PagingBar::widget(['params' => ['listing_split' => $reviews_split, 'this_filename' => 'reviews']])}
{/if}

{/if}