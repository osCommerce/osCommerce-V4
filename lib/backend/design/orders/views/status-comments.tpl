{use class="yii\helpers\Html"}
<div class="widget box box-wrapp-blue filter-wrapp">
    <div class="widget-header upd-sc-title">
        <h4>{$smarty.const.TABLE_HEADING_COMMENTS_STATUS}</h4>
    </div>
    <div class="widget-content usc-box usc-box2">
        <ul class="comments-var-box">
            <li style="width: 40%;">{$manager->render('StatusList',  ['manager' => $manager, 'order' => $order])}</li>
            <li style="width: 40%;">{$manager->render('OrderComments',  ['manager' => $manager, 'order' => $order])}</li>
            <li style="width: 19%;">{$manager->render('InvoiceComments',  ['manager' => $manager, 'order' => $order])}</li>
        </ul>
        <div style="text-align:center;">
        {Html::submitInput(IMAGE_UPDATE, ['class' => 'btn btn-confirm'])}
        </div>
    </div>
</div>