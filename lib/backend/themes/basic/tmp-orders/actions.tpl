{use class="\common\helpers\Date"}
{use class="\common\helpers\Html"}
<div class="or_box_head">{$smarty.const.TEXT_ORDER_NUM}
  {if !empty($oInfo->order_number)}
    <span class="order-number">{$oInfo->order_number}<br></span>
  {/if}
    <span class="order-id">{$oInfo->orders_id}</span>
</div>
<div class="row_or">
    <div>{$smarty.const.TEXT_DATE_ORDER_CREATED}</div>
    <div>{Date::datetime_short($oInfo->date_purchased)}</div>
</div>
{if tep_not_null($oInfo->last_modified)}
<div class="row_or">
    <div>{$smarty.const.TEXT_DATE_ORDER_LAST_MODIFIED}</div>
    <div>{Date::date_short($oInfo->last_modified)}</div>
</div>
{/if}
<div class="row_or">
    <div>{$smarty.const.TEXT_INFO_PAYMENT_METHOD}</div>
    <div>{strip_tags($oInfo->payment_method)}</div>
</div>
<div class="btn-toolbar btn-toolbar-order">
    {Html::a(TEXT_PROCESS_ORDER_BUTTON, \Yii::$app->urlManager->createUrl(['tmp-orders/process-order', 'orders_id' => $oInfo->orders_id]), ['class' => 'btn btn-primary btn-process-order'])}
    
    <span class="disable_wr">
        <span class="dis_popup">
            <span class="dis_popup_img"></span>
            <span class="dis_popup_content">{$smarty.const.TEXT_COMPLITED}</span>
        </span>
    </span>
    {if !common\helpers\Affiliate::isLogged() && \common\helpers\Acl::rule(['ACL_ORDER', 'IMAGE_DELETE'])}{if empty($oInfo->child_id)}
    {Html::button(IMAGE_DELETE, ['class' => 'btn btn-no-margin btn-delete', 'onclick' => "confirmDeleteOrder("|cat:$oInfo->orders_id|cat:")"])}{if \common\helpers\Acl::rule(['ACL_ORDER', 'IMAGE_REASSIGN'])}{Html::input('button', '', IMAGE_REASSIGN, ['class' => "btn", 'onclick' => "reassignOrder("|cat:$oInfo->orders_id|cat:")" ])}{/if}{else}
    <a href="{$app->urlManager->createUrl(['orders/process-order', 'orders_id' => $oInfo->child_id])}" class="btn btn-no-margin" title="{$smarty.const.TEXT_CONVERTED_ORDER|escape}{$oInfo->child_id}"><i class="icon-file-text"></i> <span class="title">{$smarty.const.TABLE_HEADING_ORDER} {$oInfo->child_id}</span></a>{/if}{/if}


</div>

