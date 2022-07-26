{use class="\yii\helpers\Html"}
<div class="wl-td">
<label>{$smarty.const.TITLE_YEAR}:</label>
{Html::dropDownList('year', $year, $years, ['class' =>'form-control range-block'])}
</div>
{if !is_null($year_cmp)}
<div class="wl-td">
<label>{$smarty.const.TEXT_YEAR_CMP}:</label>
{Html::dropDownList('year_cmp', $year_cmp, ['' => $smarty.const.TEXT_SELECT] + $years, ['class' =>'form-control range-block'])}
</div>
{/if}