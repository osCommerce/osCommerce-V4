{assign var="objImportExport" value=$app->controller->view->import_export}
<div id="productIEholder">
    <div class="tabbable tabbable-custom">
        <ul class="nav nav-tabs">
            {foreach $objImportExport->tabList() as $id=>$tab}
            <li class="{if $id==0} active{/if} {$data['cssClass']}" id="{$id}__li" data-bs-toggle="tab" data-bs-target="#ieEP_{$id}"><a><span title="{$tab['title']|escape:'html'}">{$tab['directory_name']}</span></a></li>
            {/foreach}
        </ul>
        <div class="tab-content">
        {foreach $objImportExport->tabList() as $id=>$tab}
             <div class="tab-pane topTabPane tabbable-custom {if $id==0} active{/if}" id="ieEP_{$id}">{$tab['content']}</div>
        {/foreach}
        </div>
    </div>
</div>
