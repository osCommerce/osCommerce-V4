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
{if !empty($manualUrl)}
    <div>
        <a href="{$manualUrl}" id="manual" target="_blank">{$smarty.const.TEXT_MANUAL}</a>
    </div>
{/if}
    <div>
        <b>Ver:</b> <small>{$version}</small>
    </div>
    {if $menu = \common\helpers\MenuHelper::getExtensionHtmlMenu($info->module->code, true, 'extension-menu-item mt-1')}
        <div class="extensions-menu-title"><b>{$smarty.const.TEXT_MENU}:</b></div>
        {$menu}
    {/if}

{if \common\helpers\System::isDevelopment() && $info->module->getType() == 'extension'}
    <div style="margin-top: 20px;">
{*        {use class="\yii\helpers\Inflector"}*}
{*        {$translateLink = "extensions/"|cat:Inflector::camel2id($info->module->code)}*}
        <a class="btn btn-secondary btn-block btn-tr-refresh" href="{Yii::$app->urlManager->createUrl(['extensions', 'module' => $info->module->code, 'action' => 'actionRefreshTranslation'])}"><i class="icon-refresh">&nbsp;&nbsp;</i>Refresh Translations</a><br>
        <script>
            $(document).ready(function() {
                $('.btn-tr-refresh').popUp({
                    box: "<div class='popup-box-wrap'><div class='around-pop-up'></div><div class='popup-box popupCredithistory'><div class='popup-heading'>Refresh Translations</div><div class='pop-up-close'></div><div class='pop-up-content'><div class='preloader'></div></div></div></div>"
                });
            });
        </script>
{*        <a target="_blank" class="btn btn-secondary btn-block" href="{Yii::$app->urlManager->createUrl(['texts', 'by' => 'translation_entity', 'search' => $translateLink])}"><i class="icon-edit">&nbsp;&nbsp;</i>Edit Translations</a>*}
    </div>
{/if}
{*{/if}*}