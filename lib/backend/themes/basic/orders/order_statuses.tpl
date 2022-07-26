<div class="widget box box-no-shadow widget-closed">
<style>
.dataTables_scrollHeadInner{ width: 100%!important; }
.dataTables_scrollHeadInner .table-st{ width: 100%!important; }
</style>
        <div class="widget-header widget-header-order-status">
            <h4>{$smarty.const.TEXT_ORDER_STATUS}</h4>
            <div class="toolbar no-padding">
                <div class="btn-group">
                    <span id="orders_list_collapse" class="btn btn-xs widget-collapse"><i class="icon-angle-up"></i></span>
                </div>
            </div>
        </div>
        <div class="widget-content">
               <table class="{if $orders_history_items|count>0}datatable{/if} table table-st" border="0" cellspacing="0" cellpadding="0" width="100%" verticalHeight="200px">
                   <thead>
                       <tr>
                        <th class="smallText" align="left">{$smarty.const.TABLE_HEADING_DATE_ADDED}</th>
                        <th class="smallText" align="left">{$smarty.const.TABLE_HEADING_CUSTOMER_NOTIFIED}</th>
                        <th class="smallText" align="left">{$smarty.const.TABLE_HEADING_STATUS}</th>
                        {if $CommentsWithStatus}
                        <th class="smallText" align="left">{$smarty.const.TABLE_HEADING_COMMENTS}</th>
                        {/if}
                        <th class="smallText" align="left">{$smarty.const.TABLE_HEADING_PROCESSED_BY}</th>
                      </tr>
                   </thead>
          {foreach $orders_history_items as $orders_history}
			<tr>
				<td>{\common\helpers\Date::datetime_short($orders_history['date_added'])}</td>
				<td>
					{if $orders_history['customer_notified'] eq '1'}
						<span class="st-true"></span></td>
					{else}
						<span class="st-false"></span></td>
					{/if}
				</td>
				<td><span class="or-st-color">{$orders_status_group_array[$orders_history['orders_status_id']]}&nbsp;/&nbsp;</span>{$orders_status_array[$orders_history['orders_status_id']]}</td>
				{if $CommentsWithStatus}
				<td>{nl2br(tep_db_output($orders_history['comments']))}&nbsp;</td>
				{/if}
				<td>{$orders_history['admin']}</td>
			</tr>
		  {foreachelse}
			<tr>
             <td colspan="6">{$smarty.const.TEXT_NO_ORDER_HISTORY}</td>
             </tr>
		  {/foreach}
        </table>          
          <br/>
          <b>{$smarty.const.ENTRY_NOTIFY_COMMENTS}</b>
          {tep_draw_textarea_field('comment', 'soft', 10, 3, '', 'class="from-control"')}
          {tep_draw_hidden_field('status', $order->info['order_status'])} 
          <br/><br/>
          {tep_draw_checkbox_field('notify', '', false, '', 'class="uniform"')}<b>{$smarty.const.ENTRY_NOTIFY_CUSTOMER}</b>
        </div>
</div>