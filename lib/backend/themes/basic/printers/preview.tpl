<div class="or_box_head">{$service->service}</div>
<div class="btn-toolbar btn-toolbar-order">
    {if $service}
    {\yii\helpers\Html::a(IMAGE_EDIT, \yii\helpers\Url::to(['printers/service', 'id' => $service->id]), ['class' => 'btn btn-no-margin'])}
    <button class="btn btn-delete btn-no-margin" onclick="serviceDelete({$service->id})">{$smarty.const.IMAGE_DELETE}</button>
    {/if}
</div>