{function name=renderAclTree level=0}
<ul>
{foreach $items as $item}
<li class="dd-item dd3-item" data-id="{$item.id}">
    <input type="checkbox" name="persmissions[]" value="{$item.id}" onchange="recalcAcl();" {if $item.selected == 1} checked{/if}><span>{$item.text}</span>
{if count($item.child) > 0}
{call name=renderAclTree items=$item.child level=$level+1}
{/if}
</li>
{/foreach}
</ul>
{/function}
{call renderAclTree items=$aclTree}