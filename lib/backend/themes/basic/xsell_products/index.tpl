

<!--=== Page Header ===-->
<div class="page-header">
    <div class="page-title">
        <h3>{$this->view->headingTitle}</h3>
    </div>
</div>
<!-- /Page Header -->


<!--===xsell list===-->
<div class="row" id="xsell_list">
    <div class="col-md-12">
        <div class="widget box">
            <div class="widget-header">
                <h4><i class="icon-reorder"></i> Products</h4>

                <div class="toolbar no-padding">
                    <div class="btn-group">
                        <span id="xsell_list_box_collapse" class="btn btn-xs widget-collapse"><i
                                    class="icon-angle-down"></i></span>
                    </div>
                </div>
            </div>
            <div class="widget-content" id="xsell_list_data">
                <table class="table table-striped table-bordered table-hover table-responsive table-checkable datatable"
                       checkable_list="0,1,2" data_ajax="{$Yii->baseUrl}/xsell_products/list">
                    <thead>
                    <tr>
                        {foreach $this->view->xsellTable as $tableItem}
                            <th{if isset($tableItem['not_important']) && $tableItem['not_important'] == 1} class="hidden-xs"{/if} {if $tableItem['colspan']} colspan="{$tableItem['colspan']}"{/if}>{$tableItem['title']}</th>
                        {/foreach}
                    </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>
<!--===/xsell list===-->

<script type="text/javascript">
    var table;
    var odata;
    var toolbar;
    var cell;
    $(document).ready(function(){
      table = $('#xsell_management_data table').clone();
      toolbar = $('.btn-toolbar').clone();
    })

    function editItem(item_id, sort) {
        $('#xsell_management_data').show();
        $("#xsell_management_data").html('');
        $("#xsell_management_data").html($(table).clone());
        $("#xsell_management").show();
        $("#xsell_management_data .xselllist").dataTable({
          "bProcessing": true,
          "bServerSide": true,
          "ajax":'xsell_products/list',
          "fnServerData": function ( sSource, aoData, fnCallback, oSettings ){
              aoData.push({
                "name":"add_related_product_ID",
                "value": item_id
              });
              aoData.push({
                "name":"sort",
                "value": sort
              });
              var _new = {};
              $.map(aoData, function(e, i){
                if(typeof(e) == 'object'){
                  _new[e.name] = e.value;
                }
              })
             aoData = _new;
             oSettings.jqXHR = $.ajax( {
                    "dataType": 'json',
                    "type": "POST",
                    "url": 'xsell_products/list',
                    "data": aoData,
                    "success": function(data){
                      fnCallback(data);
                      //console.log(data);
                      $('#xsell_management_title').html(data.product);
                      if (data.cell != ''){
                        $(cell).html(data.cell);
                      }
                      if (data.form != '' && !$('#xsell_management_data .xselllist').parent().is('form')){
                        $('#xsell_management_data .xselllist').wrap(data.form);
                        $('#xsell_management_data form').append($(toolbar).clone());
                        $('#xsell_management_data .action').attr('data-action', data.action);
                        $('#xsell_management_data form').append('<input type="hidden" name="add_related_product_ID">');
                        $('input[name=add_related_product_ID]').val(data.add_related_product_ID);
                      }
                    }
                  } );          
          }
        });
        return false;
    }

    function saveItem(obj) {
      var action = $(obj).attr("data-action");
      var item_id = $('input[name=add_related_product_ID]').val();
        $.post("{$Yii->baseUrl}/xsell_products/"+action, $('form[name=update_cross]').serialize(), function (data, status) {
            if (status == "success") {
                if (data.errorMessageType != ''){
                  $('#message .alert').removeClass('alert-').addClass('alert-'+data.errorMessageType);
                  $('#message span').html(data.errorMessage);
                  $('#message').show();                  
                }
                editItem(item_id, '');
            } else {
                alert("Request error.");
            }
        }, "json");

        return false;
    }

    function sortItem(item_id) {
        $('#message').hide();
        editItem(item_id, 1);
        $(window).scrollTop(document.body.clientHeight);
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
        $("#xsell_management").hide();

        switchOnCollapse('xsell_list_box_collapse');
        switchOffCollapse('xsell_management_collapse');

        $('#xsell_management_data').html('');
        $('#xsell_management').hide();

        var table = $('.table').DataTable();
        table.draw(false);
        
        $('#message').hide(); 
        
        $(window).scrollTop(0);
        

        return false;
    }

    function onClickEvent(obj, table) {
        $('#message').hide(); 
        cell = $(obj).find('input.cell_identify').parents('tr').find('td:nth-child(n+4):first');

        var event_id = $(obj).find('input.cell_identify').val();

        editItem(  event_id );
        $(window).scrollTop(document.body.clientHeight);
    }

    function onUnclickEvent(obj, table) {

      //  var event_id = $(obj).find('input.cell_identify').val();
    }

</script>

<!--===  xsell management ===-->
<div style="display:none" id="message">
                            <div class="alert alert- fade in">
                                <i data-dismiss="alert" class="icon-remove close"></i>
                                <span></span>
                            </div>
</div>
<div class="row" id="xsell_management" style="display: none;">
    <div class="col-md-12">
        <div class="widget box">
            <div class="widget-header">
                <h4><i class="icon-reorder"></i><span id="xsell_management_title">Cross-Sell Products</span>
                </h4>

                <div class="toolbar no-padding">
                    <div class="btn-group">
                        <span id="xsell_management_collapse" class="btn btn-xs widget-collapse"><i class="icon-angle-down"></i></span>
                    </div>
                </div>
            </div>
            <div class="widget-content fields_style" id="xsell_management_data">
                    <table class="table table-striped table-bordered table-hover table-responsive table-checkable xselllist" checkable_list="0,1,3">
                    <thead>
                      <tr>
                        {foreach $this->view->xsellListTable as $tableItem}
                            <th{if isset($tableItem['not_important']) && $tableItem['not_important'] == 1} class="hidden-xs"{/if}>{$tableItem['title']}</th>
                        {/foreach}
                    </tr>
                    </thead>
                </table>
                                                                <p class="btn-toolbar">
                                                                    <input type="button" class="btn btn-primary action" value="Update" data-action = "save" onClick="return saveItem(this);">
                                                                    <input type="button" class="btn btn-primary" value="Cancel" onClick="return resetStatement();">
                                                                </p>
            </div>
        </div>
    </div>
</div>
<!--=== xsell management ===-->