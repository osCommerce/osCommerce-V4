<div class="or_box_head">{$catalogPage.descriptionLanguageId.name}</div>
<div class=""><div>{$catalogPage.descriptionLanguageId.description_short}</div></div>
<div class="row_or"><div>&nbsp</div><div>&nbsp</div></div>
<div class="row_or"><div>{$smarty.const.TEXT_DATE_ADDED}</div><div>{Yii::$app->formatter->asDate(date('Y-m-d',$catalogPage.created_at), 'long')}</div></div>
<div class="row_or"><div>{$smarty.const.TEXT_LAST_MODIFIED}</div><div>{Yii::$app->formatter->asDate(date('Y-m-d',$catalogPage.updated_at), 'long')}</div></div>
<div class="row_or"><div>{$smarty.const.TEXT_ACTIVE}</div><div>{if $catalogPage.status} {$smarty.const.TEXT_YES} {else} {$smarty.const.TEXT_NO} {/if}</div></div>
<div class="btn-toolbar btn-toolbar-order">
    <a class="btn btn-edit btn-no-margin" href="{Yii::$app->urlManager->createUrl(['catalog-pages/edit', 'id' => $catalogPage.catalog_pages_id, 'platform_id'=>$catalogPage.platform_id, 'parent_id'=>$catalogPage.parent_id])}">{$smarty.const.IMAGE_EDIT}</a><button class="btn btn-delete" onclick="deleteItemConfirm('{$catalogPage.catalog_pages_id}')">{$smarty.const.IMAGE_DELETE}</button>
</div>
