{include 'menu.tpl'}

{use class="backend\assets\DesignAsset"}
{DesignAsset::register($this)|void}

<div class="style-edit-page">
<!--=== Page Header ===-->
<div class="page-header">
  <div class="page-title">
    <h3>{$app->controller->view->headingTitle}</h3>
  </div>
</div>
<!-- /Page Header -->
<div class="texts_block">
  <!--=== Page Content ===-->
  <div class="row order-box-list">
    <div class="col-md-12">
      <div class="widget-content">
        <div class="alert fade in" style="display:none;">
          <i data-dismiss="alert" class="icon-remove close"></i>
          <span id="message_plce"></span>
        </div>
        <div class="ord_status_filter_row filter_texts">
          <form id="filterForm" name="filterForm" onsubmit="return applyFilter();">
            <input type="hidden" name="row" id="row_id" value="{$app->controller->view->row|default:null}" />
            <input type="hidden" name="theme_name" value="{$theme_name}" />
          </form>
        </div>
        {if {$messages|default:array()|@count} > 0}
          {foreach $messages as $message}
            <div class="alert fade in {$message['messageType']}">
              <i data-dismiss="alert" class="icon-remove close"></i>
              <span id="message_plce">{$message['message']}</span>
            </div>
          {/foreach}
        {/if}
        <table class="table table-bordered table-hover datatable table-ordering table-no-search"  order_list="0" order_by="desc" data_ajax="design/backups-list?theme_name={$theme_name}">
          <thead>
          <tr>
            <th>{$smarty.const.TEXT_DATE_TIME}</th>
            <th>{$smarty.const.TEXT_COMMENTS}</th>
          </tr>
          </thead>

        </table>


      </div>


      <div class="btn-bar btn-bar-edp-page after">
        <div class="btn-right" style="padding-right: 20px">
          <span class="btn btn-import">{$smarty.const.TEXT_IMPORT}</span>
          <span class="btn btn-export">{$smarty.const.TEXT_EXPORT}</span>
        </div>
      </div>

    </div>
  </div>

  <!--===Actions ===-->
  <div class="row right_column" id="text_management">
    <div class="widget box">
      <div class="widget-content fields_style" id="text_management_data">
        <div class="scroll_col"></div>
      </div>
    </div>

  </div>
  <!--===Actions ===-->
  <!-- /Page Content -->

</div>
</div>
<script type="text/javascript">
  function onClickEvent(obj, table) {
    var dtable = $(table).DataTable();
    var id = dtable.row('.selected').index();
    $("#row_id").val(id);
    setFilterState();

    $("#text_management").hide();
    $('#text_management_data .scroll_col').html('');
    var backup_id = $(obj).find('input.backup_id').val();
    $.post("{Yii::$app->urlManager->createUrl('design/backups-actions')}", { 'backup_id' : backup_id }, function(data, status){
      if (status == "success") {
        $('#text_management_data .scroll_col').html(data);
        $("#text_management").show();
      } else {
        alert("Request error.");
      }
    },"html");
  }

  function onUnclickEvent(obj, table) {
  }

  function setFilterState() {
    orig = $('#filterForm').serialize();
    var url = window.location.origin + window.location.pathname + '?' + orig.replace(/[^&]+=\.?(?:&|$)/g, '')
    window.history.replaceState({ }, '', url);
  }

  function resetStatement() {
    setFilterState();
    var table = $('.table').DataTable();
    table.draw(false);
    //$(window).scrollTop(0);
    return false;
  }

  function applyFilter() {
    resetStatement();
    return false;
  }

  function translateDelete(backup_id){
    $.popUpConfirm('{$smarty.const.TEXT_DELETE_BACKUP}', function(){
      $.post("{Yii::$app->urlManager->createUrl('design/backup-delete')}", { 'backup_id' : backup_id }, function(data, status){
        if (status == "success") {
          resetStatement();
        } else {
          alert("Request error.");
        }
      },"html");
    });
    return false;
  }

  function backupRestore(backup_id){
    $.popUpConfirm('{$smarty.const.TEXT_RESTORE_DESIGN}', function(){
        $('#container > #content > .container').addClass('hided-box').append('<div class="hided-box-holder"><div class="preloader"></div></div>')
      $.post("{Yii::$app->urlManager->createUrl('design/backup-restore')}", { 'backup_id' : backup_id }, function(data, status){
          $('#container > #content > .container').removeClass('hided-box');
          $('.hided-box-holder').remove();
        if (status == "success") {
          resetStatement();
        } else {
          alert("Request error.");
        }
      },"html");
    });
    return false;
  }

</script>

<link href="{$app->request->baseUrl}/plugins/fancytree/skin-bootstrap/ui.fancytree.min.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="{$app->request->baseUrl}/plugins/fancytree/jquery.fancytree-all.min.js"></script>
<script type="text/javascript" src="{$app->view->theme->baseUrl}/js/backups.js"></script>
<script>
  $(function(){
      backups.init()
  })
</script>