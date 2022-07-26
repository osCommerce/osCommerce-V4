{assign var="objImportExport" value=$app->controller->view->import_export}
<div id="productIEholder">
    <div class="tabbable tabbable-custom">
        <ul class="nav nav-tabs">
            {foreach $objImportExport->tabList() as $id=>$tab}
            <li class="{if $id==0} active{/if} {$data['cssClass']}" id="{$id}__li"><a href="#ieEP_{$id}" data-toggle="tab"><span>{$tab['title']}</span></a></li>
            {/foreach}
        </ul>
        <div class="tab-content">
        {foreach $objImportExport->tabList() as $id=>$tab}
             <div class="tab-pane topTabPane tabbable-custom {if $id==0} active{/if}" id="ieEP_{$id}">{$tab['content']}</div>
        {/foreach}
        </div>
    </div>
</div>
