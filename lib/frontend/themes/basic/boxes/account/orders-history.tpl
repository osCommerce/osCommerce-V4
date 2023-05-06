{use class="frontend\design\Info"}
{\frontend\design\Info::addBoxToCss('table-list')}
<div class="account-history">
    {if $orders_total > 0}
    {if $number_of_rows > 0 && $settings[0].show_pagenation}
        {frontend\design\Info::addBoxToCss('pagination')}
        <div class="pagination">
            <div class="left-area">
                {$history_count}
            </div>
            <div class="right-area">
                {if isset($links.prev_page.link)}
                    <a href="{$links.prev_page.link}" class="prev"></a>
                {else}
                    <span class="prev"></span>
                {/if}
                {if isset($links.prev_pages.link)}
                    <a href="{$links.prev_pages.link}" title="{$links.prev_pages.title}">...</a>
                {/if}

                {foreach $links.page_number as $page}
                    {if isset($page.link)}
                        <a href="{$page.link}">{$page.title}</a>
                    {else}
                        <span class="active">{$page.title}</span>
                    {/if}
                {/foreach}

                {if isset($links.next_pages.link)}
                    <a href="{$links.next_page.link}" title="{$links.next_page.title}">...</a>
                {/if}
                {if isset($links.next_page.link)}
                    <a href="{$links.next_page.link}" class="next"></a>
                {else}
                    <span class="next"></span>
                {/if}
            </div>
        </div>
    {/if}
        <div class="main">
            <table class="order-info orders-table table-list">
                <tr class="headings">
                    <th class="orders-id">{$smarty.const.TEXT_ORDER_NUMBER}</th>
                    <th class="date">{$smarty.const.TEXT_ORDER_DATE}</th>
                    <th class="shipped-to">{$smarty.const.TEXT_ORDER_SHIPPED_TO}</th>
                    <th class="products td-alignright">{$smarty.const.TEXT_ORDER_PRODUCTS}</th>
                    <th class="total td-alignright">{$smarty.const.TEXT_ORDER_TOTAL}</th>
                    <th class="status">{$smarty.const.TEXT_ORDER_STATUS}</th>
                    <th class="links"></th>
                </tr>
                {foreach $history_array as $hisarray}
                    <tr class="item {if $hisarray.pay_link}moduleRowDue{/if}">
                        <td class="orders-id">
                            <span class="hidden">{$smarty.const.TEXT_ORDER_NUMBER}</span>
                            {if !empty($hisarray.order_number)}
                              <span class="order-number">{$hisarray.order_number}<br></span>
                            {else}
                              <span class="order-id">{$hisarray.orders_id}</span>
                            {/if}
                            {if $hisarray.pay_link && false}
                                <div class="not_fully_paid_td">{$smarty.const.TEXT_NOT_FULLY_PAID}</div>
                            {/if}
                        </td>
                        <td class="date">
                            <span class="hidden">{$smarty.const.TEXT_ORDER_DATE}</span>
                            {$hisarray.date}
                        </td>
                        <td class="shipped-to name">
                            {\common\helpers\Output::output_string_protected($hisarray.name)}
                        </td>
                        <td class="products td-alignright">
                            <span class="hidden">{$smarty.const.TEXT_ORDER_PRODUCTS}</span>
                            {$hisarray.count}
                        </td>
                        <td class="total order-total price td-alignright">{if $smarty.const.GROUPS_IS_SHOW_PRICE !== false}{strip_tags($hisarray.order_total)}{/if}</td>
                        <td class="status status-name">{$hisarray.orders_status_name}
                        <span class="or-st-color">
                        {foreach $hisarray.progress as $groupColor}
                            <i style="background: {$groupColor};"></i>
                        {/foreach}
                        </span>
                        </td>
                        <td class="links td-alignright">
                            {if $hisarray.pay_link}
                                <a class="btn-1" href="{$hisarray.pay_link}">{$text_link_pay}</a>
                            {/if}

                            {if $hisarray.pay_link == ''}
                                {if $hisarray.reorder_link}
                                    <a class="view_link" {if $hisarray.reorder_confirm}data-js-confirm="{$hisarray.reorder_confirm|escape:'html'}"{/if} href="{$hisarray.reorder_link}">{$smarty.const.SMALL_IMAGE_BUTTON_REORDER}</a>
                                {/if}
                            {/if}
                            <a class="history_link view_link" href="{$hisarray.link}">{$text_link}</a>
                        </td>
                    </tr>
                {/foreach}
            </table>
        </div>
        <script type="text/javascript">
            tl('{Info::themeFile('/js/main.js')}', function(){

                if ( typeof alertMessage !== 'function' ) return;
                $('a[data-js-confirm]').on('click', function () {
                    alertMessage('<p>'+$(this).attr('data-js-confirm')+'</p><div><a class="btn" href="'+$(this).attr('href')+'">{$smarty.const.IMAGE_BUTTON_CONTINUE|escape:javascript}</a></div>');
                    return false;
                });

            })
        </script>
    {else}
        <div class="noItems">{$smarty.const.TEXT_NO_PURCHASES}</div>
    {/if}
</div>