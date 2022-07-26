{function name=renderAclTreeFull level=0}
<ol class="dd-list">
{foreach $items as $item}
<li class="dd-item" data-id="{$item.id}">
    <div class="dd-nodrag">
        <label class="checkbox"><input type="checkbox" name="persmissions[]" value="{$item.id}" onchange="recalcAcl(this);" {if $item.selected == 1} checked{/if}><span>{$item.text}</span></label>
    </div>
{if count($item.child) > 0}
{call name=renderAclTreeFull items=$item.child level=$level+1}
{/if}
</li>
{/foreach}
</ol>
{/function}
{call renderAclTreeFull items=$aclTree}