{use class="\suppliersarea\widgets\ActionButton"}
{use class="yii\helpers\Html"}
<div id="{$b_uprid}">
    <div class="value-box">
    <span>{$value}</span>
        {ActionButton::widget(['template' => '{update}', 'url' => Yii::$app->urlManager->createUrl([$baseUrl|cat:'/products/update', 'uprid' => $uprid ]), 'options' => ['class' => 'edit-discount', 'style'=>"float: right; margin: 0px 0%;", 'data-uprid' => $b_uprid]])}
    </div>
    <div class="edit-box" style="display:none;">        
        {Html::textInput('suppliers_data['|cat:$uprid|cat:'][supplier_discount]', $value, ['class' => 'form-control', 'data-pjax'=>'a5'])}        
        {ActionButton::widget(['template' => '{save}', 'url' => Yii::$app->urlManager->createUrl([$baseUrl|cat:'/products/save-discount', 'uprid' => $uprid ]), 'options' => ['class' => 'save-discount', 'style'=>"float: right; margin: 0px 0%;", 'data-uprid' => $b_uprid]])}        
    </div>
</div>

