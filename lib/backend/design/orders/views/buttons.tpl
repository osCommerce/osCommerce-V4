{use class = "common\helpers\Html"}

<div class="btn-bar" style="padding: 0; text-align: center;">
    <div class="btn-left">
        <a href="{$addedPages['backUrl']}" class="btn btn-cancel-foot">{$smarty.const.IMAGE_BACK}</a>
    </div>
    {if !($order instanceof \common\classes\TmpOrder)}
    {$manager->render('Unprocessed', ['order' => $order])}
    {if \common\helpers\Acl::rule(['ACL_ORDER', 'TEXT_SEND_CUSTOMER_REQUEST'])}{$manager->render('Request', ['manager' => $manager, 'order' => $order])}{/if}
    {if \common\helpers\Acl::rule(['ACL_ORDER', 'TEXT_INVOICE'])}<a href="{\Yii::$app->urlManager->createUrl(['orders/ordersbatch', 'pdf' => 'invoice', 'action' => 'selected', 'orders_id' => $order->info['orders_id']])}" TARGET="_blank" class="btn btn-primary">{$smarty.const.TEXT_INVOICE}</a>
      {if is_array($addedPages['invoice'])}
        {foreach $addedPages['invoice'] as $fname}
          {Html::a($fname, \Yii::$app->urlManager->createUrl(['orders/ordersbatch', 'pdf' => 'invoice', 'page_name' => $fname, 'action' => 'selected', 'orders_id' => $order->info['orders_id']]), ['class' => "btn btn-no-margin", 'target'=>"_blank"])}
        {/foreach}
      {/if}
    &nbsp;{/if}
    {if \common\helpers\Acl::rule(['ACL_ORDER', 'IMAGE_ORDERS_PACKINGSLIP'])}<a href="{\Yii::$app->urlManager->createUrl(['orders/ordersbatch', 'pdf' => 'packingslip', 'action' => 'selected', 'orders_id' => $order->order_id])}" TARGET="_blank" class="btn btn-primary">{$smarty.const.IMAGE_ORDERS_PACKINGSLIP}</a>
      {if is_array($addedPages['packingslip'])}
        {foreach $addedPages['packingslip'] as $fname}
          {Html::a($fname, \Yii::$app->urlManager->createUrl(['orders/ordersbatch', 'pdf' => 'packingslip', 'page_name' => $fname, 'action' => 'selected', 'orders_id' => $order->info['orders_id']]), ['class' => "btn btn-no-margin", 'target'=>"_blank"])}
        {/foreach}
      {/if}
    {/if}
    {if \common\helpers\Acl::rule(['ACL_ORDER', 'TEXT_PRINT_LABEL'])}{$manager->render('PrintLabel', ['manager' => $manager, 'order' => $order])}{/if}
    {foreach \common\helpers\Hooks::getList('orders/process-order', 'btn-bar-bottom') as $filename}
        {include file=$filename}
    {/foreach}
    {/if}
</div>