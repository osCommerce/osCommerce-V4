{if $settings.designer_mode == 'expert'}
    <div class="tabbable tabbable-custom">
        <ul class="nav nav-tabs nav-tabs-scroll-2">

            <li class="active" data-bs-toggle="tab" data-bs-target="#list"><a>{$smarty.const.TEXT_MAIN}</a></li>
            <li class="label">{$smarty.const.WINDOW_WIDTH}:</li>
            {foreach $settings.media_query as $item}
                <li data-bs-toggle="tab" data-bs-target="#list{$item.id}"><a>{$item.title}</a></li>
            {/foreach}

        </ul>
        <div class="tab-content">
            <div class="tab-pane active menu-list" id="list">

                <div class="setting-row">
                    <label for="">{$smarty.const.TEXT_COLUMNS_IN_ROW}</label>
                    <input type="text" name="setting[0][col_in_row]" class="form-control" value="{$settings[0].col_in_row}"/>
                </div>

            </div>
            {foreach $settings.media_query as $item}
                <div class="tab-pane menu-list" id="list{$item.id}">

                    <div class="setting-row">
                        <label for="">{$smarty.const.TEXT_COLUMNS_IN_ROW}</label>
                        <input type="text" name="visibility[0][{$item.id}][col_in_row]" class="form-control" value="{$visibility[0][{$item.id}].col_in_row}"/>
                    </div>

                </div>
            {/foreach}

        </div>
    </div>
{else}
    <div class="setting-row">
        <label for="">{$smarty.const.TEXT_COLUMNS_IN_ROW}</label>
        <input type="text" name="setting[0][col_in_row]" class="form-control" value="{$settings[0].col_in_row}"/>
    </div>
{/if}