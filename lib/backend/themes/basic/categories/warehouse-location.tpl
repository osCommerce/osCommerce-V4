{use class="yii\helpers\Html"}
{function name=renderSublocation level=0}
    {if isset($items[$level])}
        <div class="input-row col-md-6">
            {Html::dropDownList('box_location[]', $items[$level].location_id, $items[$level].locationList, ['class'=>'form-control form-control-small', 'onchange' => 'return checkLocationChild(this, '|cat:$warehouse_id|cat:');'])}
        </div>
        <div class="sublocation">
            {call name=renderSublocation items=$items level=$level+1}
        </div>
    {/if}
{/function}

<div class="heading">{$smarty.const.TABLE_HEADING_LOCATION}:</div>
<div class="">
    <div class="input-row col-md-6">
        {Html::dropDownList('box_location[]', $location_id, $locationList, ['class'=>'form-control form-control-small', 'onchange' => 'return checkLocationChild(this, '|cat:$warehouse_id|cat:');'])}
    </div>

    <div class="sublocation">
        {call renderSublocation items=$sublocation level=0}
    </div>
</div>