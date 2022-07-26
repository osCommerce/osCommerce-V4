<div class="account_history">
  <h1>{$smarty.const.HEADING_PRODUCTS_REVIEWS_TITLE}</h1>

  {*frontend\design\boxes\ListingFunctionality::widget($params)*}
  {frontend\design\boxes\PagingBar::widget($params)}
  {*frontend\design\boxes\Listing::widget($params)*}
  <div class="contentBoxContents">
    {if count($reviews)>0}
      <table cellspacing="0" cellpadding="0" width="100%" class="orders-table">
        <tr>
          <th>{$smarty.const.TEXT_REVIEW_COLUMN_PRODUCT_NAME}</th>                          
          <th>{$smarty.const.TEXT_REVIEW_COLUMN_RATED}</th>
          <th>{$smarty.const.TEXT_REVIEW_COLUMN_DATE_ADDED}</th>
          <th>{$smarty.const.TEXT_REVIEW_COLUMN_STATUS}</th>
          <th></th>
        </tr>
        {foreach $reviews as $_review}
          <tr class="moduleRow">
            <td>
              {if $_review.products_link}
                <a href="{$_review.products_link}">{$_review.products_name}</a>
              {else}
                {$_review.products_name}
              {/if}
            </td>
            <td><span class="rating-{$_review.reviews_rating}"></span></td>
            <td>{$_review.date_added_str}</td>
            <td>{$_review.status_name}</td>
            <td class="td-alignright">
              {if $_review.status_name == 'Approved'}
              <a class="view_link" href="{$_review.view}">{$smarty.const.SMALL_IMAGE_BUTTON_VIEW}</a>
              {/if}
            </td>
          </tr>
        {/foreach}
      </table>
    {else}
      {$smarty.const.OVERVIEW_MY_REVIEW_NONE}
    {/if}
  </div>

  {frontend\design\boxes\PagingBar::widget($params)}

  <div class="buttonBox buttons"><div class="button2 right-buttons"><a class="btn" href="{$account_back_link}">{$smarty.const.IMAGE_BUTTON_BACK}</a></div></div>
</div>