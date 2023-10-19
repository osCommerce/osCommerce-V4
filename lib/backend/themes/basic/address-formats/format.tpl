{use class="yii\helpers\Html"}
<div class="widget box box-no-shadow format-box" style="margin-bottom: 10px;" data-format-id="{$format->address_format_id}">
    <div class="widget-header">
        <h4>
            <span>{$format->address_format_title}</span>
            {Html::hiddenInput('formats_titles['|cat:$format->address_format_id|cat:']', $format->address_format_title)}
            <div class="btn-group">
                <i class="icon-pencil"></i>
            </div>
        </h4>
        <div class="btn-group">
            <span class="btn-remove" title="{$smarty.const.TEXT_REMOVE}"><i class="icon-trash"></i></span>
        </div>

        <div class="toolbar no-padding">
            <div class="btn-group">
                <span class="btn btn-xs widget-collapse"><i class="icon-angle-down"></i></span>
            </div>
        </div>
    </div>
    <div class="widget-content after address-format-rows" data-format-id="{$format->address_format_id}">
        <div class="address-holder">
            {if is_array($format->address_format)}
                {foreach $format->address_format as $key => $row}
                    <div class="rows" data-row="{$key}">
                        {if is_array($row)}
                            <div class="row address-row">
                                {foreach $row as $keyrow => $item}
                                    <div class="item">{$item}{Html::hiddenInput('formats['|cat:$format->address_format_id|cat:']['|cat:$key|cat:'][]', $item)}</div>
                                {/foreach}
                            </div>
                        {/if}
                    </div>
                {/foreach}
            {/if}
        </div>
        <button class="btn btn-default add-row" >{$smarty.const.TEXT_ADD_NEW_ROW}</button>
    </div>
</div>