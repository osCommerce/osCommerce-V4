{use class="\yii\helpers\Html"}
<div class="wl-td">
    <label>{$smarty.const.TEXT_FROM}</label>{Html::dropDownList('start_custom', $start_custom, $years, ['class' =>'form-control', 'prompt' => TEXT_SELECT])}
</div>
<div class="wl-td">
    <label>{$smarty.const.TEXT_TO}</label>{Html::dropDownList('end_custom', $end_custom, $years, ['class' =>'form-control', 'prompt' => TEXT_SELECT])}
</div>
<script>
 var checkSelection = function(){
        //check custom    
        return true;
 }
</script>