{use class="yii\helpers\Html"}
<div class="input-row col-md-6">
    {Html::dropDownList('box_location[]', '', $locationList, ['class'=>'form-control form-control-small', 'onchange' => 'return checkLocationChild(this, '|cat:$warehouse_id|cat:');'])}
</div>
<div class="sublocation">
</div>
