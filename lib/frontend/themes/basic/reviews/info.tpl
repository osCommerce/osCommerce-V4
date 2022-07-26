<h1>
    {if $review.products_link}
        <a href="{$review.products_link}">{$HEADING_TITLE}</a>
    {else}
        {$HEADING_TITLE}
    {/if}
</h1>

{if $review}
  <div class="review-info-content after">
    <div class="review-info-image">
      {$review.products_image}
    </div>
    <div class="review-info-head">
     <p class="review-info-text">
      {$review.reviews_text|escape:'html'|nl2br}
    </p>
    <div class="review_by"><span class="review-info-reviewed_by">{$review.reviewed_by_formatted}</span><span class="rating-{$review.reviews_rating}"></span> {if $smarty.const.DISPLAY_REVIEW_RATING_TITLE=='True' &&  $item.reviews_rating_description}<span class="rating-description">{$item.reviews_rating_description}</span>{/if}</div>
    <div class="review-info-date">{$review.date_added_formatted}</div>
    </div>
  </div>
{else}
    <p class="aligncenter">{$smarty.const.TEXT_NO_REVIEWS}</p>
{/if}

<div class="buttons">
  <a class="btn" href="{$back_link_href}">{$smarty.const.IMAGE_BUTTON_BACK}</a>
</div>