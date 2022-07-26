<div class="rating">
{if $settings[0].reviews_count == 'left'}<span class="review-count">(<span>{$count}</span>)</span>{/if}
<span class="rating-{$rating}"></span>
{if $settings[0].reviews_count == 'right'}<span class="review-count">(<span>{$count}</span>)</span>{/if}
</div>