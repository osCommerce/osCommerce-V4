<table class="table statuses-table table-st" border="0" cellspacing="0" cellpadding="0" width="100%">
    <thead>
        <tr>
            <th class="smallText" align="left">{$smarty.const.TABLE_HEADING_DATE_ADDED}</th>
            <th class="smallText" align="left">{$smarty.const.TABLE_HEADING_CUSTOMER_NOTIFIED}</th>
            <th class="smallText" align="left">{$smarty.const.TABLE_HEADING_STATUS}</th>
            <th class="smallText" align="left">{$smarty.const.TABLE_HEADING_COMMENTS}</th>
            {if $smsEnabled}
            <th class="smallText" align="left">{$smarty.const.TABLE_HEADING_SMSCOMMENTS}</th>
            {/if}
            <th class="smallText" align="left">{$smarty.const.TABLE_HEADING_PROCESSED_BY}</th>
        </tr>
    </thead>
    {$withComments=0}
    {foreach $orders_history_items as $row => $orders_history name="ordersHistoryItems"}
      {if $smarty.foreach.ordersHistoryItems.last && ($orders_history_items|default:array()|@count)>1}
        <tr><td colspan="6" align="center" id="order_history_collapse_td">
                <div class="row">
          <div class="col-md-3 tr-more" id="oStatusHistoryShow">
            <label class="plus-right">{$smarty.const.TEXT_SHOW_ALL}</label></div>
          <div class="col-md-3 tr-more {if $withComments>0}dis_module{/if}" id="oStatusHistoryShowComents">
            <label class="plus-right">{$smarty.const.TEXT_SHOW_WITH_COMMENTS}</label></div>
          <div class="col-md-3 tr-less {if $withComments==0}dis_module{/if}" id="oStatusHistoryHideComents">
            <label class="minus-right">{$smarty.const.TEXT_HIDE_WITH_COMMENTS}</label></div>
          <div class="col-md-3 tr-least" id="oStatusHistoryHide">
            <label class="minus-right">{$smarty.const.TEXT_HIDE}</label></div>
                </div>
          </td><tr>
      {/if}
        <tr class="{if ($row < count($orders_history_items) -1) && empty(trim($orders_history['comments'])) }collapse use-collapse{/if}{if !empty(trim($orders_history['comments'])) && !$smarty.foreach.ordersHistoryItems.last }{$withComments=1}collapse-comments{/if}">
            <td>{\common\helpers\Date::datetime_short($orders_history['date_added'])}</td>
            <td>
                {if $orders_history['customer_notified'] eq '1'}
                    <span class="st-true"></span></td>
                {else}
                    <span class="st-false"></span></td>
                {/if}
            </td>
            <td><span class="or-st-color">
                <i style="background: {$orders_history['group']['orders_status_groups_color']};"></i>
                {$orders_history['group']['orders_status_groups_name']}&nbsp;/&nbsp;</span>{$orders_history['status']['orders_status_name']}</td>
            <td>{nl2br(tep_db_output($orders_history['comments']))}&nbsp;</td>
            {if $smsEnabled}
            <td>{nl2br(tep_db_output($orders_history['smscomments']))}&nbsp;</td>
            {/if}
            <td>{$orders_history['admin']}</td>
        </tr>
      {foreachelse}
        <tr>
            <td colspan="6">{$smarty.const.TEXT_NO_ORDER_HISTORY}</td>
        </tr>
      {/foreach}
</table>
<script>
    $(document).ready(function(){
        if ($('#order_history_collapse_td').length){

            $('#oStatusHistoryHideComents, #oStatusHistoryShowComents').on('click', function(e){
              if (!$(this).hasClass("dis_module")) {
                $('tr.collapse-comments').toggleClass('collapse');
                $('#oStatusHistoryHideComents').toggleClass('dis_module');
                $('#oStatusHistoryShowComents').toggleClass('dis_module');
              }
            }).trigger('click');

            $('#oStatusHistoryShow').on('click', function(e){
              if (!$(this).hasClass("dis_module")) {
                $('tr.use-collapse, tr.collapse-comments').removeClass('collapse');
                $('#oStatusHistoryShow, #oStatusHistoryShowComents').addClass('dis_module');
                $('#oStatusHistoryHideComents, #oStatusHistoryHide').removeClass('dis_module');
              }
            });

            $('#oStatusHistoryHide').on('click', function(e){
              if (!$(this).hasClass("dis_module")) {
                $('tr.use-collapse, tr.collapse-comments').addClass('collapse');
                $('#oStatusHistoryHideComents, #oStatusHistoryHide').addClass('dis_module');
                $('#oStatusHistoryShow, #oStatusHistoryShowComents').removeClass('dis_module');
              }
            });
            
        }
    })
</script>
