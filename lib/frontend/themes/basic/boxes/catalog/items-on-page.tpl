{use class="Yii"}
{use class="frontend\design\Info"}
{if \frontend\design\Info::themeSetting('old_listing')}
    <div class="items-on-page">
        <form action="{$sorting_link}" method="get" class="sort-form">
            {$hidden_fields}

            <span class="before">{$smarty.const.SHOW}</span>
            <select class="items-select" name="max_items">
                {foreach $view as $item}
                    <option value="{$item}"{if $view_id == $item} selected{/if}>{$item}</option>
                {/foreach}
            </select>
            <span class="after">{$smarty.const.ITEMS}</span>

        </form>
    </div>
    <script type="text/javascript">
        tl('{Info::themeFile('/js/main.js')}', function(){
            var boxID = $('#box-{$box_id}');

            $('select', boxID).off('change', getProductsList).on('change', getProductsList);
        })
    </script>
{else}
    <div class="items-on-page">
            <span class="before">{$smarty.const.SHOW}</span>
            <select class="items-select" name="max_items">
                {foreach $view as $item}
                    <option value="{$item}"{if $view_id == $item} selected{/if}>{$item}</option>
                {/foreach}
            </select>
            <span class="after">{$smarty.const.ITEMS}</span>
    </div>
{/if}