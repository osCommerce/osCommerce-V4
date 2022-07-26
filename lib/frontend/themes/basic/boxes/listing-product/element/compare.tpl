<label class="checkbox">
    <input type="checkbox" name="compare[]" value="{$product.id}">
    <span></span>
    {$smarty.const.TEXT_SELECT_TO_COMPARE}
</label>
<a href="{\Yii::$app->urlManager->createUrl('catalog/compare')}" class="view compare_button" style="display: none">({$smarty.const.VIEW})</a>