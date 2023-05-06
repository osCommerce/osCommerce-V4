<ul class="tabNavigation" id="menuSwitcher">
    <li><a href="{$app->urlManager->createUrl("index")}" class="basic">{$smarty.const.TEXT_EVERYDAY_ACTIVITIES}</a></li>
    <li><a href="#" class="advanced active">{$smarty.const.TEXT_FULL_MENU}</a></li>
</ul>
{function name=renderMenu level=0}
<ul {if $level==0}id="nav"{else}class="sub-menu"{/if}>
    {foreach $items as $item}
        <li class="{if isset($context->selectedMenu[$level]) && $context->selectedMenu[$level] == $item['acl']}current{/if}{if isset($item['dis_module']) && $item['dis_module']} dis_module{/if}">
        {if $item['box_type'] == 1}
            <a href="javascript:void(0);">
                <i class="icon-{$item['filename']}"></i>
                <span>{$item['title']}</span>
            </a>
            {call name=renderMenu items=$item.child level=$level+1}
        {else}
            <a href="{if isset($item['disabled']) && $item['disabled']}javascript:void(0);{else}{$app->urlManager->createUrl($item['path'])}{/if}">
                <i class="icon-{$item['filename']}"></i>
                {$item['title']}
            </a>
        {/if}
        </li>
    {/foreach}
</ul>
{/function}
{call renderMenu items=$currentMenu level=0}
{if $autoHideMenu}
<script type="text/javascript">
if ($('#container').hasClass('sidebar-closed') == false) {
    $('#container').toggleClass('sidebar-closed');
}
</script>
{/if}