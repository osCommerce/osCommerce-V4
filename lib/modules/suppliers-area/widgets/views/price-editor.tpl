{use class="\suppliersarea\widgets\ActionButton"}
{use class="yii\helpers\Html"}
<div id="{$b_uprid}">
    <div class="value-box">
    <span>{$value}</span>
        {ActionButton::widget(['template' => '{update}', 'url' => Yii::$app->urlManager->createUrl([$baseUrl|cat:'/products/update', 'uprid' => $uprid ]), 'options' => ['class' => 'edit-price', 'style'=>"float: right; margin: 0px 0%;", 'data-uprid' => $b_uprid]])}
    </div>
    <div class="edit-box" style="display:none;">        
        {Html::textInput('suppliers_data['|cat:$uprid|cat:'][suppliers_price]', $price, ['class' => 'form-control', 'data-pjax'=>'a5'])}
        {Html::dropDownList('suppliers_data['|cat:$uprid|cat:'][currencies_id]', $sCurrency, $cLlist, ['class' => 'form-control'])}
        {ActionButton::widget(['template' => '{save}', 'url' => Yii::$app->urlManager->createUrl([$baseUrl|cat:'/products/save-price', 'uprid' => $uprid ]), 'options' => ['class' => 'save-price', 'style'=>"float: right; margin: 0px 0%;", 'data-uprid' => $b_uprid]])}        
    </div>
</div>

