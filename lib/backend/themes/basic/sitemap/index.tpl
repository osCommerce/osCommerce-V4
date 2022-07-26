
<!--=== Page Header ===-->
<div class="page-header">
  <div class="page-title">
    <h3>{$app->controller->view->headingTitle}</h3>
  </div>
</div>
<!-- /Page Header -->
  <!--=== Page Content ===-->
  <div class="xmlsitemap-box-list">
        <div class="widget box">
            <div class="widget-content">
      <table class="table table-striped table-bordered table-hover table-responsive table-checkable datatable table-sitemap" checkable_list="0" data_ajax="sitemap/list">
        <thead>
        <tr>
          {foreach $app->controller->view->SiteMapTable as $tableItem}
            <th{if isset($tableItem['not_important']) && $tableItem['not_important'] == 1} class="hidden-xs"{/if}>{$tableItem['title']}</th>
          {/foreach}
        </tr>
        </thead>
      </table>
        </div>
        </div>
  </div>
<script type="text/javascript">
  function onClickEvent(){

  }
  function onUnclickEvent() {

  }
</script>