<!--=== Page Header ===-->
<div class="page-header">
  <div class="page-title">
    <h3>{$app->controller->view->headingTitle}</h3>
  </div>
</div>
<!-- /Page Header -->

<div class="">
  <!--===groups list===-->
<div class="tabbable tabbable-custom" style="margin-bottom: 0;">
    <ul class="nav nav-tabs">
        {foreach $types as $id => $type}
          <li class="{if $id==$type_id} active {/if}"><a class="js_link_platform_modules_select" href="{Yii::$app->urlManager->createUrl(['email/templates', 'type_id' => $id])}" data-type_id="{$id}"><span>{$type}</span></a></li>
        {/foreach}
    </ul>
</div>
<div class="order-wrap">
  <div class="row order-box-list">
    <div class="col-md-12">
      <div class="widget-content" id="groups_list_data">
        <form id="filterForm" name="filterForm" onsubmit="return applyFilter();">
          <input type="hidden" name="row" id="row_id" value="{$app->controller->view->filters->row}" />
          <input type="hidden" name="type_id" value="{$type_id}" />
        </form>
        <table class="table table-striped table-bordered table-hover table-responsive table-checkable datatable double-grid"
               checkable_list="0" data_ajax="email/templates-list">
          <thead>
          <tr>
            {foreach $app->controller->view->groupsTable as $tableItem}
              <th{if isset($tableItem['not_important']) && $tableItem['not_important'] == 1} class="hidden-xs"{/if}>{$tableItem['title']}</th>
            {/foreach}
          </tr>
          </thead>
        </table>
      </div>
    </div>
  </div>
  <!--===/groups list===-->

  <script type="text/javascript">
    function previewItem( item_id ) {
      $.post("email/template-preview", {
        'item_id': item_id
      }, function (data, status) {

        if (status == "success") {
          alertMessage('<div style="margin: 10px;">'+data+'</div>');
        } else {
          alert("Request error.");
        }

      }, "html");
      return false;
    }

    function preEditItem( item_id ) {
      $.post("email/templatepreedit", {
        'item_id': item_id
      }, function (data, status) {
        if (status == "success") {
          $('#groups_management_data .scroll_col').html(data);
          deleteScroll();
          heightColumn();
        } else {
          alert("Request error.");
        }
      }, "html");
      return false;
    }

    function switchOffCollapse(id) {
      if ($("#" + id).children('i').hasClass('icon-angle-down')) {
        $("#" + id).click();
      }
    }

    function switchOnCollapse(id) {
      if ($("#" + id).children('i').hasClass('icon-angle-up')) {
        $("#" + id).click();
      }
    }

    function resetStatement() {
      $(".order-wrap").show();
      $("#groups_management").hide();

      switchOnCollapse('groups_list_box_collapse');
      switchOffCollapse('groups_management_collapse');

      $('#groups_management_data .scroll_col').html('');
      $('#groups_management').hide();

      var table = $('.table').DataTable();
      table.draw(false);

      $(window).scrollTop(0);

      return false;
    }

    function applyFilter(){
      return false;
    }
    function setFilterState() {
        var orig = $('#filterForm').serialize();
        var url = window.location.origin + window.location.pathname + '?' + orig.replace(/[^&]+=\.?(?:&|$)/g, '')
        window.history.replaceState({ }, '', url);
    }

    function onClickEvent(obj, table) {
      var dtable = $(table).DataTable();
      var id = dtable.row('.selected').index();
      $("#row_id").val(id);
      setFilterState();

      var event_id = $(obj).find('input.cell_identify').val();

      preEditItem(  event_id );
    }

    function onUnclickEvent(obj, table) {

      var event_id = $(obj).find('input.cell_identify').val();
    }
    $(document).ready(function() {
      $(window).resize(function () {
        setTimeout(function () {
          var height_box = $('.order-wrap').height();
          $('#order_management .widget').css('min-height', height_box);
        }, 800);
      })
      $(window).resize();
    })
    function deleteItemConfirm(item_id) {
        $.post("{Yii::$app->urlManager->createUrl('email/confirmitemdelete')}", {  'item_id': item_id }, function (data, status) {
            if (status == "success") {
                $('#groups_management_data .scroll_col').html(data);
                $("#order_management").show();
                //switchOnCollapse('groups_management_collapse');
            } else {
                alert("Request error.");
            }
        }, "html");
        return false;
    }
    function deleteItem() {
        $.post("{Yii::$app->urlManager->createUrl('email/itemdelete')}", $('#item_delete').serialize(), function (data, status) {
            if (status == "success") {
                resetStatement();
                $('#groups_management_data .scroll_col').html("");
                //switchOffCollapse('groups_management_collapse');
            } else {
                alert("Request error.");
            }
        }, "html");

        return false;
    }
  </script>

  <!--===  groups management ===-->
  <div class="row right_column" id="order_management" style="display: none;">
    <div class="widget box">
      <div class="widget-content fields_style" id="groups_management_data">
        <div class="scroll_col"></div>
      </div>
    </div>
  </div>
  <!--=== groups management ===-->
</div>
</div>
