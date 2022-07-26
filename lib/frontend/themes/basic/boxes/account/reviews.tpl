{if $settings[0]['show_pagenation']}
{frontend\design\boxes\PagingBar::widget($params)}
{/if}
{\frontend\design\Info::addBoxToCss('table-list')}
<div class="contentBoxContents">
    {if count($reviews)>0}
        <table cellspacing="0" cellpadding="0" width="100%" class="orders-table table-list">
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
                        {*if $_review.status_name == 'Approved'}
                            <a class="view_link" href="{$_review.view}">{$smarty.const.SMALL_IMAGE_BUTTON_VIEW}</a>
                        {/if*}
                    </td>
                </tr>
            {/foreach}
        </table>
    {else}
        {$smarty.const.OVERVIEW_MY_REVIEW_NONE}
    {/if}
</div>
{if $settings[0]['show_pagenation']}
    {frontend\design\boxes\PagingBar::widget($params)}
{/if}