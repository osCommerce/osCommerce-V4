

<!--=== Page Header ===-->
<div class="page-header">
    <div class="page-title">
        <h3>{$this->view->headingTitle}</h3>
    </div>
</div>
<!-- /Page Header -->


<!--===define_mainpage===-->
<div class="row" id="define_mainpage">
    <div class="col-md-12">
        <div class="widget box">
            <div class="widget-header">
                <h4><i class="icon-reorder"></i> {$mainpage}</h4>

                <div class="toolbar no-padding">
                    <div class="btn-group">
                        <span id="xsell_list_box_collapse" class="btn btn-xs widget-collapse"><i
                                    class="icon-angle-down"></i></span>
                    </div>
                </div>
            </div>
            <div class="widget-content" id="define_mainpage_data">
                {$lng}
                {$mpcontent}
            </div>
        </div>
    </div>
</div>
<!--===/define_mainpage===-->

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

    function save() {
        $.post("{$Yii->baseUrl}/define_mainpage/save", $('form[name=update_cross]').serialize(), function (data, status) {
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

</script>
