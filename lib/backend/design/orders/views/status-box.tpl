<div class="widget box box-no-shadow">
    <div class="widget-header widget-header-order-status">
        <h4>{$smarty.const.TEXT_ORDER_STATUS}</h4>
        {$manager->render('Toolbar')}
    </div>
    <div class="widget-content">
        {$manager->render('StatusTable',  ['manager' => $manager, 'order' => $order])}
        {if !($order instanceof \common\classes\TmpOrder)}
        {$manager->render('PaymentActions',  ['manager' => $manager, 'order' => $order])}
        {$manager->render('StatusComments',  ['manager' => $manager, 'order' => $order])}
        {/if}
    </div>
</div>