{use class="Yii"}
{use class="\frontend\design\boxes\PagingBar"}

{if $message_review !=''}
    {$message_review}
{/if}

{if $link_write}
    <div class="write-review-button"><a href="{$link_write}" class="btn">{$smarty.const.WRITE_REVIEW}</a></div>
{/if}

{if $reviews == ''}
    <div class="no-reviews">{$smarty.const.NO_PRODUCT_REVIEW}</div>
{else}

    {if $rating}
        <div class="middle-rating">{$smarty.const.RATING} <span class="rating-{$rating}"></span> ({$count})</div>
    {/if}

    <div class="reviews-list">
        {foreach $reviews as $item}
            <div class="item">
                <div class="date">{$item.date}</div>

                <div class="review">{$item.reviews_text}</div>
                <div class="name">{$item.customers_name} <span class="rating-{$item.reviews_rating}"></span> {if $smarty.const.DISPLAY_REVIEW_RATING_TITLE=='True' && $item.reviews_rating_description}<span class="rating-description">{$item.reviews_rating_description}</span>{/if}</div>
            </div>
        {/foreach}
    </div>

    {PagingBar::widget(['params' => ['listing_split' => $reviews_split, 'this_filename' => 'reviews']])}

{/if}