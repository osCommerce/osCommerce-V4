<div class="popup-heading">{$details.name}</div>
<div class="popup-content">

    <table class="table">
        <tr>
            <td>Admin:</td>
            <td>{$details.admin}</td>
        </tr>
        <tr>
            <td>Date / time:</td>
            <td>{$details.date_added}</td>
        </tr>
        {if $details.widget_name}
            <tr>
                <td>Widget name:</td>
                <td>{$details.widget_name}</td>
            </tr>
        {/if}
        {if $details.page_name}
            <tr>
                <td>Root block name:</td>
                <td>{$details.page_name}</td>
            </tr>
        {/if}
        {if $details.designer_mode}
            <tr>
                <td>{$smarty.const.EDIT_MODE}:</td>
                <td>{$details.designer_mode}</td>
            </tr>
        {/if}
    </table>

    {if $details.widgetSettings}
        <h4>Widget settings:</h4>
        <table class="table">
            <tr>
                <th>Setting name</th>
                <th>old value</th>
                <th>new value</th>
                <th>affiliation</th>
            </tr>
            {foreach $details.widgetSettings as $setting}
                {if $setting.old.setting_value != $setting.new.setting_value}
                    <tr>
                        <td>{$setting.new.setting_name}</td>
                        <td>{$setting.old.setting_value}</td>
                        <td>{$setting.new.setting_value}</td>
                        <td>
                            {if $setting.new.visibility == '1'}
                                :hover
                            {elseif $setting.new.visibility == '2'}
                                .active
                            {elseif $setting.new.visibility == '3'}
                                :before
                            {elseif $setting.new.visibility == '4'}
                                :after
                            {else}
                                {$setting.new.visibility}
                            {/if}
                        </td>
                    </tr>
                {/if}
            {/foreach}
        </table>
    {/if}
    {if $details.css.delete}
        <h4>Deleted styles:</h4>
        <pre>{$details.css.delete}</pre>
    {/if}
    {if $details.css.changed}
        <h4>Changed styles:</h4>
        <pre>{$details.css.changed}</pre>
    {/if}
    {if $details.css.new}
        <h4>New styles:</h4>
        <pre>{$details.css.new}</pre>
    {/if}

    {if $details.extensionWidgets}
        {$no = false}
        {$notInstalled = false}
        {foreach $details.extensionWidgets as $widget}
            {if $widget.status == 'no'}
                {$no = true}
            {elseif $widget.status == 'not-installed'}
                {$notInstalled = true}
            {/if}
        {/foreach}
        {if $no}
            <div class=""><b>{$smarty.const.EXTENSIONS_YOU_DONT_HAVE}</b></div>
            {foreach $details.extensionWidgets as $widget}
                {if $widget.status == 'no'}
                    <div class="">{$widget.name}</div>
                {/if}
            {/foreach}
        {/if}
        {if $notInstalled}
            <div class=""><b>{$smarty.const.WIDGETS_NOT_INSTALLED_EXTENSIONS}</b></div>
            {foreach $details.extensionWidgets as $widget}
                {if $widget.status == 'not-installed'}
                    <div class="">{$widget.name}</div>
                {/if}
            {/foreach}
        {/if}
    {/if}


</div>
<div class="popup-buttons" style="overflow: hidden">
    <span class="btn btn-cancel">Close</span>
</div>