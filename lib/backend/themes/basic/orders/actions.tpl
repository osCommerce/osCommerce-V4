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
    {Html::a(TEXT_PROCESS_ORDER_BUTTON, \Yii::$app->urlManager->createUrl(['orders/process-order', 'orders_id' => $oInfo->orders_id]), ['class' => 'btn btn-primary btn-process-order'])}

    {if \common\helpers\Acl::rule(['ACL_ORDER', 'IMAGE_EDIT'])}
        <a href="{\Yii::$app->urlManager->createUrl(['editor/order-edit', 'orders_id' => $oInfo->orders_id])}"
           class="btn btn-no-margin btn-edit"
           {*data-bs-toggle="tooltip" data-bs-placement="left"
           data-bs-custom-class="custom-tooltip"
           data-bs-title="{$smarty.const.TEXT_COMPLITED}"*}
        >{$smarty.const.IMAGE_EDIT}</a>
        <script>
            $(function(){
                new bootstrap.Tooltip($('.btn-edit')[0])
            })
        </script>
    {/if}

    {if !common\helpers\Affiliate::isLogged() && \common\helpers\Acl::rule(['ACL_ORDER', 'IMAGE_DELETE'])}
        {Html::button(IMAGE_DELETE, ['class' => 'btn btn-delete', 'onclick' => "confirmDeleteOrder("|cat:$oInfo->orders_id|cat:")"])}
    {/if}

    {if is_array($addedPages['invoice'])}
      <div id="choose-invoice" style="display: none">
            <div class="popup-heading">{$smarty.const.TEXT_INVOICE}</div>
            <div class="popup-content invoice-links">
              {Html::a(TEXT_INVOICE, \Yii::$app->urlManager->createUrl(['orders/ordersbatch', 'pdf' => 'invoice', 'action' => 'selected', 'orders_id' => $oInfo->orders_id]), ['class' => "btn btn-no-margin", 'target'=>"_blank"])}
              {foreach $addedPages['invoice'] as $fname}
                {Html::a($fname, \Yii::$app->urlManager->createUrl(['orders/ordersbatch', 'pdf' => 'invoice', 'page_name' => $fname, 'action' => 'selected', 'orders_id' => $oInfo->orders_id]), ['class' => "btn btn-no-margin", 'target'=>"_blank"])}
              {/foreach}
            </div>
            <div class="noti-btn">
              <div><button class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</button></div>
            </div>
            <script type="text/javascript">
              (function($){
                $(function(){
                  $('.popup-box-wrap .invoice-links a').on('click', function(){
                    $('.popup-box-wrap').remove()
                  });
                  $('.btn-choose-invoice').popUp();
                })
              })(jQuery)
            </script>
          </div>
    {/if}

    {if is_array($addedPages['invoice'])}
        <a href="#choose-invoice" class="btn btn-choose-invoice btn-no-margin">{$smarty.const.TEXT_INVOICE}</a>
    {else}
        {Html::a(TEXT_INVOICE, \Yii::$app->urlManager->createUrl(['orders/ordersbatch', 'pdf' => 'invoice', 'action' => 'selected', 'orders_id' => $oInfo->orders_id]), ['class' => "btn btn-no-margin", 'target'=>"_blank"])}
    {/if}

    {if is_array($addedPages['packingslip'])}<a href="#choose-packingslip" class="btn btn-choose-packingslip">{$smarty.const.IMAGE_ORDERS_PACKINGSLIP}</a>
          <div id="choose-packingslip" style="display: none">
            <div class="popup-heading">{$smarty.const.IMAGE_ORDERS_PACKINGSLIP}</div>
            <div class="popup-content packingslip-links">
              {Html::a(IMAGE_ORDERS_PACKINGSLIP, \Yii::$app->urlManager->createUrl(['orders/ordersbatch', 'pdf' => 'packingslip', 'action' => 'selected', 'orders_id' => $oInfo->orders_id]), ['class' => "btn", 'target'=>"_blank"])}
              {foreach $addedPages['packingslip'] as $fname}
                {Html::a($fname, \Yii::$app->urlManager->createUrl(['orders/ordersbatch', 'pdf' => 'packingslip', 'page_name' => $fname, 'action' => 'selected', 'orders_id' => $oInfo->orders_id]), ['class' => "btn btn-no-margin", 'target'=>"_blank"])}
              {/foreach}
            </div>
            <div class="noti-btn">
              <div><button class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</button></div>
            </div>
            <script type="text/javascript">
              (function($){
                $(function(){
                  $('.popup-box-wrap .packingslip-links a').on('click', function(){
                    $('.popup-box-wrap').remove()
                  });
                  $('.btn-choose-packingslip').popUp();
                })
              })(jQuery)
            </script>
          </div>
        {else}{Html::a(IMAGE_ORDERS_PACKINGSLIP, \Yii::$app->urlManager->createUrl(['orders/ordersbatch', 'pdf' => 'packingslip', 'action' => 'selected', 'orders_id' => $oInfo->orders_id]), ['class' => "btn", 'target'=>"_blank"])}
        {/if}

    {if \common\helpers\Acl::rule(['ACL_ORDER', 'IMAGE_REASSIGN'])}
        {Html::input('button', '', IMAGE_REASSIGN, ['class' => "btn btn-primary btn-process-order", 'onclick' => "reassignOrder("|cat:$oInfo->orders_id|cat:")" ])}
    {/if}

    {if $ext = \common\helpers\Acl::checkExtensionAllowed('MergeOrders', 'allowed')}
        {$ext::actionOrderactions($oInfo->orders_id)}
    {/if}

    {if $canAnonimize}
        {Html::a(TEXT_ANONIMIZE_ORDER, '', ['class' => 'btn btn-no-margin', 'onclick'=>'return anonimizeOrder();'])}
    {/if}
</div>
{if $canAnonimize}
    <script type="text/javascript">
      function anonimizeOrder() {
        bootbox.confirm({
          message: "{$smarty.const.TEXT_CONFIRM_ANONIMAZE}",
          buttons: {
              confirm: {
                  label: '{$smarty.const.TEXT_BTN_YES|escape:'html'}',
              },
              cancel: {
                  label: '{$smarty.const.TEXT_BTN_NO|escape:'html'}',
              }
          },
          callback: function (result) {
            if (result) {
                $.post("{\Yii::$app->urlManager->createUrl(['orders/anonimize-order', 'orders_id' => $oInfo->orders_id])}", { }, function(data, status){
                  if (status == "success") {
                    resetStatement();
                  } else {
                    alert("Request error.");
                  }
                },"json");
            }
          }
        });
        return false;
      }
    </script>
{/if}



