{use class="frontend\design\Block"}
<table class="table table-striped table-hover table-responsive table-bordered table-adding-column">
    <tr>
        {for $count = 1 to $cols}
            <th>{Block::widget(['name' => $name|cat:'_heading-'|cat:$count, 'params' => ['type' => $type, 'params' => $params]])}</th>
        {/for}
        <td rowspan="2" class="add-col update-cols" data-name="{$name}" data-cols="{$cols + 1}" title="{$smarty.const.ADD_COLUMN}">&nbsp;</td>
    </tr>
    <tr>
        {for $count = 1 to $cols}
            <td>{Block::widget(['name' => $name|cat:'-'|cat:$count, 'params' => ['type' => $type, 'params' => $params]])}</td>
        {/for}
    </tr>
    <tr>
        {for $count = 1 to $cols}
            <td class="remove-col-cell">
                {if $count == $cols && $count > 2}
                    <span class="remove-col update-cols" data-name="{$name}" data-cols="{$cols - 1}" title="{$smarty.const.REMOVE_COLUMN}"></span>
                {/if}
            </td>
        {/for}
        <td class="remove-col-cell">
        </td>
    </tr>
</table>

<style type="text/css">
    .add-col {
        text-align: center;
        vertical-align: middle;
        cursor: pointer;
        font-size: 34px;
    }
    .add-col:before {
        content: '+';
    }
    .remove-col {
        cursor: pointer;
        padding: 5px;
        display: inline-block;
        font-size: 20px;
    }
    .remove-col:before {
        content: '\f014';
        font-family: FontAwesome;
        color: #f00;
    }
    .remove-col-cell {
        text-align: center;
    }
    .remove-col-cell {
        border: none !important;
    }
</style>