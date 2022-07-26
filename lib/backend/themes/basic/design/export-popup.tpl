<style type="text/css">
    .icon-muted:before {
        content: "\f0c8";
    }
    ul.fancytree-container {
        border: none;
    }
</style>
<div class="">
    <div class="">{$smarty.const.SELECT_ITEMS_TO_EXPORT}</div>
    <div class="tree"></div>
</div>
<script type="text/javascript">
    entryData.exportItems = [
        {if $menus}
        { "title": "<b>{$smarty.const.TEXT_MENU}</b>", "expanded": true, "folder": true, "children": [
            {foreach $menus as $menu}
            { "title": "{$menu.name}", "name": "{$menu.name}", "type": "menus"},
            {/foreach}
        ]},
        {/if}
        {if $banners}
        { "title": "<b>{$smarty.const.TABLE_HEADING_BANNERS}</b>", "expanded": true, "folder": true, "children": [
            {foreach $banners as $banner}
            { "title": "{$banner.group}", "name": "{$banner.group}", "type": "banners"},
            {/foreach}
        ]},
        {/if}
        {if $infoPages && false}
        { "title": "<b>{$smarty.const.TEXT_INFO_PAGES}</b>", "expanded": true, "folder": true, "children": [
            {foreach $infoPages as $page}
            { "title": "{$page.name}", "name": "{$page.name}", "type": "infoPages"},
            {/foreach}
        ]},
        {/if}
    ];
</script>