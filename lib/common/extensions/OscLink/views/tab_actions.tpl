{use class="yii\helpers\Html"}
<div class="widget box">
<div class="widget-content">
    <table id="ActionAllTable" class="table table-bordered table-responsive table-execute">
        <thead>
            <tr>
                <th>{$smarty.const.EXTENSION_OSCLINK_TEXT_DESCRIPTION}</th>
                <th>{$smarty.const.EXTENSION_OSCLINK_TEXT_ACTION}</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{$smarty.const.EXTENSION_OSCLINK_TEXT_DESCRIPTION_IMPORT_ALL}</td>
                <td width="20%">
                    <a class="btn btn-primary btn-execute">{$smarty.const.EXTENSION_OSCLINK_TEXT_IMPORT_ALL_BUTTON}</a>
                    <input type="hidden" name="feed" value="ALL"/>
                </td>
            </tr>
        </tbody>
    </table>
    <p>{$smarty.const.EXTENSION_OSCLINK_TEXT_IMPORT_BY_PARTS}</p>
    <table id="ActionsTable" class="table table-bordered table-responsive table-execute">
        <thead>
            <tr>
                <th>{$smarty.const.EXTENSION_OSCLINK_TEXT_GROUP}</th>
                <th>{$smarty.const.EXTENSION_OSCLINK_TEXT_ENTITY}</th>
                <th>{$smarty.const.EXTENSION_OSCLINK_TEXT_DESCRIPTION}</th>
                <th>{$smarty.const.EXTENSION_OSCLINK_TEXT_ACTION}</th>
            </tr>
        </thead>
        <tbody>
            {foreach $app->controller->view->actionsArray as $actionItem}
                <tr>
                    {if !empty($actionItem['group_name'])}
                        <td rowspan="{$actionItem['group_count']}"><center>{$actionItem['group_name']}</center></td>
                    {/if}
                    <td width="15%">{$actionItem['entity']}</td>
                    <td>{$actionItem['description']}</td>
                    <td width="20%">
                        <a class="btn btn-primary btn-execute">{$actionItem['action']}</a>
                        <input type="hidden" name="feed" value="{$actionItem['feed']}"/>
                    </td>
                </tr>
            {/foreach}
        </tbody>
    </table>
    <p>*{$smarty.const.EXTENSION_OSCLINK_TEXT_DESCRIPTION_COMMON}</p>
</div>
</div>
