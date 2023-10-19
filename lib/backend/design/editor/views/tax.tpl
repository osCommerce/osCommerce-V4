{use class="yii\helpers\Html"}
{if $wrap}
<label>{$smarty.const.TABLE_HEADING_TAX}:</label>
<div class="label_value">
{/if}
    {Html::dropDownList('product_info[][tax]['|cat:$uprid|cat:']', $tax_selected, $tax_class_array, ['class'=>'form-select tax', 'onchange'=>$onchange, 'prompt' => TEXT_NONE])}
{if $wrap}
</div>
{/if}