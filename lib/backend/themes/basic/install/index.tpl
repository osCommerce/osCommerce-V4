{if {$messages|default:array()|@count} > 0}
{foreach $messages as $message}
  <div class="alert alert-warning fade in">
    <i data-dismiss="alert" class="icon-remove close"></i>
    <span id="message_plce">{$message}</span>
  </div>
{/foreach}
{/if}
{if $success}
  <div class="alert alert-success fade in">
    <i data-dismiss="alert" class="icon-remove close"></i>
    <span id="message_plce">{$success}</span>
  </div>
{/if}
<div class="tabbable tabbable-custom tabbable-ep">
    <ul class="nav nav-tabs">
        {foreach $directories as $directory}
            <li class="{if $directory['id']==$selectedRootDirectoryId} active {/if}"><a class="js_link_folder_select" href="{$directory['link']}" data-directory_id="{$directory['id']}"{if $directory['id']==$selectedRootDirectoryId} onclick="return false;"{/if}><span>{$directory['text']}</span></a></li>
        {/foreach}
    </ul>
    <div class="tab-content tab-content1">
        <div class="tab-pane topTabPane tabbable-custom active">
            {if $selectedRootDirectoryId == 'selection'}{include "selection.tpl"}{/if}
            {if $selectedRootDirectoryId == 'library'}{include "library.tpl"}{/if}
            {if $selectedRootDirectoryId == 'modules'}{include "modules.tpl"}{/if}
            {if $selectedRootDirectoryId == 'settings'}{include "settings.tpl"}{/if}
            {if $selectedRootDirectoryId == 'updates'}{include "updates.tpl"}{/if}
        </div>
    </div>
</div>
<script>
$(document).ready(function () {
    $('.create_item_popup').popUp();
});
</script>