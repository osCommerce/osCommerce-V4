{use class="yii\helpers\Html"}
<div class="or_box_head">{$info->title}</div>
{if $status == '1'}
    <div class="btn-toolbar btn-toolbar-order">
        <a class="btn btn-edit btn-primary btn-no-margin" href="{$editLink}">{$smarty.const.IMAGE_EDIT}</a><button class="btn btn-delete" onClick="return {$removeBtn}">{$smarty.const.TEXT_REMOVE}</button>
        {if (!isset($info->module->isExtension))}
            <a class="btn btn-default btn-no-margin" href="{Yii::$app->urlManager->createUrl(['modules/export','platform_id' => $selected_platform_id, 'set' => $set, 'module' => $info->module->code])}">{$smarty.const.TEXT_EXPORT_SETTINGS}</a>
            <a class="btn btn-default btn-no-margin btn-import" href="javascript:void(0);">{$smarty.const.TEXT_IMPORT_SETTINGS}</a>
            <a class="btn btn-edit btn-default btn-no-margin" href="{$translateLink}">{$smarty.const.IMAGE_BUTTON_TRANSLATE}</a>
        {/if}
    </div>
    <div class="module_row">
        <div>{$description}</div>
        <div>{$keys}</div>
    </div>
{else}
    <div class="btn-toolbar btn-toolbar-order">
        <input type="button" class="btn btn-primary btn-process-order" value="{$smarty.const.IMAGE_INSTALL}" onClick="return {$installBtn}">
    </div>
    <div class="module_row">
        <div>{$description}</div>
    </div>
{/if}
{*{if $set == 'extensions'}*}
    <div>
        <b>Ver:</b> <small>{$version}</small>
    </div>

    {if (\common\helpers\Acl::checkExtensionInstalled($info->module->code))}
        {if (tep_not_null($info->adminMenu[0]['title']))}
            <div><b>{$smarty.const.TEXT_MENU}:</b></div>
            <div class="mb-2">{$info->tree}</div>
            {if (\common\helpers\Acl::rule(['BOX_HEADING_ADMINISTRATOR', 'BOX_ADMINISTRATOR_BOXES'])) && $countDisabledMenuItems > 0}
                <div style="margin-top: 20px;"><a target="_blank" class="btn btn-secondary btn-edit btn-block" href="{Yii::$app->urlManager->createUrl(['adminfiles'])}"> {$smarty.const.TEXT_CHANGE_ACL}</a></div>
            {/if}
        {/if}
    {/if}

{*{/if}*}