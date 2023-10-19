{use class = "common\helpers\Date"}
{if $platform}
    <div class="or_box_head">{$platform->platform_name}</div>
    <div class="row_or"><div>{$smarty.const.TEXT_DATE_ADDED}</div><div>{Date::date_short($platform->date_added)}</div></div>
    {if tep_not_null($platform->last_modified)}
        <div class="row_or"><div>{$smarty.const.TEXT_LAST_MODIFIED}</div><div>{Date::date_short($platform->last_modified)}</div></div>
    {/if}
    
    {if $statement}
        <div class="btn-toolbar btn-toolbar-order">
            <a href="{Yii::$app->urlManager->createUrl(['platforms/edit', 'id' => $platform->platform_id])}" class="btn btn-edit btn-primary btn-process-order ">{$smarty.const.IMAGE_EDIT}</a>            
                {if !($platform->is_default)}
                    <button onclick="return deleteItemConfirm({$platform->platform_id})" class="btn btn-delete btn-no-margin btn-process-order ">{$smarty.const.IMAGE_DELETE}</button>                
                {/if}
                {if !$platform->is_virtual}
                    {$multiplatform}            
                    <a href="{Yii::$app->urlManager->createUrl(['platforms/configuration', 'platform_id' => $platform->platform_id])}" class="btn btn-edit btn-primary btn-process-order ">{$smarty.const.BOX_HEADING_CONFIGURATION}</a>
                {/if}
            <button onclick="return copyItemConfirm({$platform->platform_id})" class="btn btn-copy btn-primary btn-process-order ">{$smarty.const.IMAGE_COPY}</button>
            {if $theme_edit_link}
                <a href="{$theme_edit_link}" class="btn btn-theme btn-primary btn-process-order ">{$smarty.const.BUTTON_CHOOSE_PLATFORM_THEME}</a>
            {/if}
            {if $platform_localization_link}
                <a href="{$platform_localization_link}" class="btn btn-edit btn-primary btn-process-order ">{$smarty.const.BUTTON_SETUP_PLATFORM_LOCALIZATION}</a>
            {/if}
            {if $platform_working_timetable_link}
                <a href="{$platform_working_timetable_link}" class="btn btn-edit btn-primary btn-process-order ">{$smarty.const.BUTTON_SETUP_PLATFORM_WORKING_TIMETABLE}</a>
            {/if}
            {if $watermark_edit_link}
                <a href="{$watermark_edit_link}" class="btn btn-edit btn-primary btn-process-order ">{$smarty.const.BUTTON_SETUP_PLATFORM_WATERMARK}</a>
            {/if}
            {if $platform_soap_server_link}
                <a href="{$platform_soap_server_link}" class="btn btn-edit btn-primary btn-process-order ">Configure platform SOAP server</a>
            {/if}
            {if $platform_rest_server_link}
                <a href="{$platform_rest_server_link}" class="btn btn-edit btn-primary btn-process-order ">Configure platform rest server</a>
            {/if}
        </div>
    {/if}
{/if}
