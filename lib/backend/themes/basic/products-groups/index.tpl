<!--=== Page Header ===-->
<div class="page-header">
    <div class="page-title">
        <h3>{$app->controller->view->headingTitle}</h3>
    </div>
</div>
<!-- /Page Header -->
<div class="order-wrap">
    <input type="hidden" id="row_id">
    <!--=== Page Content ===-->
    <div class="row order-box-list">
        <div class="col-md-12">
            <div class="widget-content">

                <div class="alert fade in" style="display:none;">
                    <i data-dismiss="alert" class="icon-remove close"></i>
                    <span id="message_plce"></span>
                </div>       
                {if {$messages|@count} > 0}
                    {foreach $messages as $message}
                        <div class="alert fade in {$message['messageType']}">
                            <i data-dismiss="alert" class="icon-remove close"></i>
                            <span id="message_plce">{$message['message']}</span>
                        </div>               
                    {/foreach}
                {/if}
                <table class="table table-striped table-selectable table-checkable table-hover table-responsive table-bordered datatable dataTable table-properties" data_ajax="{$app->urlManager->createUrl('products-groups/list')}">
                    <thead>
                        <tr>
                            {foreach $app->controller->view->productsGroupTable as $tableItem}
                                <th{if isset($tableItem['not_important']) && $tableItem['not_important'] == 1} class="hidden-xs"{/if}>{$tableItem['title']}</th>
                            {/foreach}
                        </tr>
                    </thead>
                </table>            

            </div>
        </div>
    </div>

    <!--===Actions ===-->
    <div class="row right_column" id="products_groups_management">
        <div class="widget box">
            <div class="widget-content fields_style" id="products_groups_management_data">
                <div class="scroll_col"></div>
            </div>
        </div>
    </div>
    <!--===Actions ===-->
    <!-- /Page Content -->
</div>

<script type="text/javascript">
var global = '{$eID}';

function switchOffCollapse(id) {
    if ($("#"+id).children('i').hasClass('icon-angle-down')) {
        $("#"+id).click();
    }
}

function switchOnCollapse(id) {
    if ($("#"+id).children('i').hasClass('icon-angle-up')) {
        $("#"+id).click();
    }
}

function resetStatement(item_id) {
    if (item_id > 0) global = item_id;

    $("#products_groups_management").hide();
    switchOnCollapse('products_groups_list_collapse');
    var table = $('.table').DataTable();
    table.draw(false);
    $(window).scrollTop(0);
    return false;
}

var first = true;
function onClickEvent(obj, table) {
    $('#row_id').val(table.find(obj).index());
    $("#products_groups_management").hide();
    $('#products_groups_management_data .scroll_col').html('');
    var products_groups_id = $(obj).find('input.cell_identify').val();
    if (global > 0) products_groups_id = global;

    $.post("products-groups/statusactions", { 'products_groups_id' : products_groups_id }, function(data, status) {
        if (status == "success") {
            $('#products_groups_management_data .scroll_col').html(data);
            $("#products_groups_management").show();
        } else {
            alert("Request error.");
        }
    },"html");

    $('.table tr').removeClass('selected');
    $('.table').find('input.cell_identify[value=' + products_groups_id + ']').parents('tr').addClass('selected');
    global = '';
    url = window.location.href;
    if (url.indexOf('eID=') > 0) {
      url = url.replace(/eID=\d+/g, 'eID=' + products_groups_id);
    } else {
      url += '?eID=' + products_groups_id;
    }
    if (first) {
      first = false;
    } else {
      window.history.replaceState({}, '', url);
    }
}

function onUnclickEvent(obj, table) {
    $("#products_groups_management").hide();
    var event_id = $(obj).find('input.cell_identify').val();
    var type_code = $(obj).find('input.cell_type').val();
    $(table).DataTable().draw(false);
}

function productsGroupEdit(id) {
    $("#products_groups_management").hide();
    $.get("products-groups/edit", { 'products_groups_id' : id }, function(data, status) {
        if (status == "success") {
            $('#products_groups_management_data .scroll_col').html(data);
            $("#products_groups_management").show();
            switchOffCollapse('products_groups_list_collapse');
        } else {
            alert("Request error.");
        }
    },"html");
    return false;
}

function productsGroupSave(id) {
    $.post("products-groups/save?products_groups_id="+id, $('form[name=products_group]').serialize(), function(data, status) {
        if (status == "success") {
            //$('#products_groups_management_data').html(data);
            //$("#products_groups_management").show();
            $('.alert #message_plce').html('');
            $('.alert').show().removeClass('alert-error alert-success alert-warning').addClass(data['messageType']).find('#message_plce').append(data['message']);
            resetStatement(id);
            switchOffCollapse('products_groups_list_collapse');
        } else {
            alert("Request error.");
        }
    },"json");
    return false;    
}

function productsGroupDeleteConfirm(id) {
    $.post("{$app->urlManager->createUrl('products-groups/confirmdelete')}", { 'products_groups_id': id }, function (data, status) {
        if (status == "success") {
            $('#products_groups_management_data .scroll_col').html(data);
        } else {
            alert("Request error.");
        }
    }, "html");
    return false;
}

function productsGroupDelete() {
    if (confirm('Are you sure?')) {
        $.post("{$app->urlManager->createUrl('products-groups/delete')}", $('#item_delete').serialize(), function (data, status) {
            if (status == "success") {
                if (data == 'reset') {
                    resetStatement();
                } else {
                    $('#products_groups_management_data .scroll_col').html(data);
                    $("#products_groups_management").show();
                }
                switchOnCollapse('products_groups_list_collapse');
            } else {
                alert("Request error.");
            }
        }, "html");
    }
    return false;
}

$.fn.image_uploads = function(options){
  var option = jQuery.extend({
    overflow: false,
    box_class: false
  },options);

  return this.each(function() {
    var _this = $(this);
    if (_this.data('value')) {
      _this.html('\
    <div class="upload-file-wrap">\
      <div class="upload-file-template">Drop files here or<br><span class="btn">Upload</span></div>\
      <div class="upload-file dz-clickable dz-started"><div class="dz-details dz-processing dz-success dz-image-preview"><img data-dz-thumbnail src="{$smarty.const.DIR_WS_CATALOG_IMAGES}' + _this.data('value') + '" /><div class="dz-filename"><span data-dz-name="">' + _this.data('value') + '</span></div><div class="upload-remove"></div></div></div>\
      <div class="upload-hidden"><input type="hidden" name="' + _this.data('name') + '"/></div>\
    </div>');
      $('.upload-remove', _this).click(function(){
        $('.upload-file', _this).html('');
        _this.removeAttr('data-value');
        $('input[name="' + _this.data('name') + '"]').val('del');
      })
    } else {
      _this.html('\
    <div class="upload-file-wrap">\
      <div class="upload-file-template">Drop files here or<br><span class="btn">Upload</span></div>\
      <div class="upload-file"></div>\
      <div class="upload-hidden"><input type="hidden" name="' + _this.data('name') + '"/></div>\
    </div>');
    }

    $('.upload-file', _this).dropzone({
      url: "{Yii::$app->urlManager->createUrl('upload')}",
      sending:  function(e, data) {
        $('.upload-hidden input[type="hidden"]', _this).val(e.name);
        $('.upload-remove', _this).on('click', function(){
          $('.dz-details', _this).remove()
          $('.upload-hidden input[type="hidden"]', _this).val('del');
        })
      },
      previewTemplate: '<div class="dz-details"><img data-dz-thumbnail /><div class="dz-filename"><span data-dz-name=""></span></div><div class="upload-remove"></div></div>',
      dataType: 'json',
      drop: function(){
        $('.upload-file', _this).html('');
      }
    });
  })
};
</script>
