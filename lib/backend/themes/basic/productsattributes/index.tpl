<!--=== TODO: fix css ===-->

<!--=== Page Header ===-->
<div class="page-header">
    <div class="page-title">
        <h3>{$app->controller->view->headingTitle}</h3>
    </div>
</div>
<!-- /Page Header -->

<!--===Products Attributes List ===-->
<div class="order-wrap">
<div class="row order-box-list">
    <div class="col-md-12">
            <div class="widget-content">
              <form id="filterForm" name="filterForm" onsubmit="return applyFilter();">
                <input type="hidden" name="row" id="row_id" value="{$row}" />
<input type="hidden" name="global_id" value="{$global_id}" id="global_id">
<input type="hidden" name="global_type_code" value="{$global_type_code}" id="global_type_code">
                </form>
                <table class="table table-striped table-bordered table-hover table-responsive table-checkable table-selectable js-table-sortable datatable"
                       checkable_list="0" data_ajax="productsattributes/list">
                    <thead>
                    <tr>
                        {foreach $app->controller->view->attributesTable as $tableItem}
                            <th{if isset($tableItem['not_important']) && $tableItem['not_important'] == 1} class="hidden-xs"{/if}>{$tableItem['title']}</th>
                        {/foreach}
                    </tr>
                    </thead>

                </table>
            </div>
    </div>
</div>
<!-- /Products Attributes List -->

<script type="text/javascript">

  function setFilterState() {
    orig = $('#filterForm').serialize();
    var url = window.location.origin + window.location.pathname + '?' + orig.replace(/[^&]+=\.?(?:&|$)/g, '')
    window.history.replaceState({ }, '', url);
  }

  function onClickEvent(obj, table) {
    var dtable = $(table).DataTable();
    var id = dtable.row('.selected').index();
    $("#row_id").val(id);
    setFilterState();

        var $identity = $(obj).find('input.cell_identify');
        var event_id = $identity.val();
        var type_code = $(obj).find('input.cell_type').val();

        if (type_code == 'root') {
            //$('#global_id').val(event_id);
            //$('input[type="search"]').val('');
            //$('.table').DataTable().search('').draw();

            $('#global_type_code').val('suboption');
            $('#global_id').val($identity.attr('data-option_id'));
            //$('#global_type_code').val('option');
            $("#attribute_management").hide();
            $('#attribute_management_data .scroll_col').html('');
        } else {
            //return editAttribute(event_id,type_code);
            preEditItem(event_id, type_code);
        }
        return true;
    }

    function _onClickEvent(obj, table) {
        $("#attribute_management").hide();
        $('#attribute_management_data .scroll_col').html('');
        var event_id = $(obj).find('input.cell_identify').val();
        var type_code = $(obj).find('input.cell_type').val();
    }

    function onUnclickEvent(obj, table) {
        $("#attribute_management").hide();

        var event_id = $(obj).find('input.cell_identify').val();
        var type_code = $(obj).find('input.cell_type').val();

        if (type_code == 'option') {

            $('#global_id').val(event_id);
            $('#global_type_code').val('suboption');
            $('input[type="search"]').val('');
            $(table).DataTable().search('').draw(false);
        } else if (type_code == 'root') {
            $('#global_id').val(event_id);
            $('#global_type_code').val('root');
            $('input[type="search"]').val('');
            $(table).DataTable().search('').draw(false);
            //$('.table').DataTable().search('').draw();

            //$('#global_type_code').val('option');
            //$("#attribute_management").hide();
            //$('#attribute_management_data .scroll_col').html('');
        }else if( type_code=='suboption' ) {
            //editAttribute(event_id, type_code);
            $("#attribute_management").show();
            return false;
        }
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
      setFilterState();
        $("#attribute_management").hide();
        $("#attribute_info").hide();
        switchOnCollapse('attr_list_collapse');
        var table = $('.table').DataTable();
        table.draw(false);
        $(window).scrollTop(0);
        return false;
    }

    function preEditItem(products_options_id, type_code) {

        if ((type_code + '') == 'undefined') {
            type_code = $('#global_type_code').val();
        }

        if (type_code == 'suboption') {
            var global_id = $('#global_id').val();
        } else {
            var global_id = null;
        }

        $.post("productsattributes/itempreedit", {
            'item_id': products_options_id,
            "type_code": type_code,
            "global_id": global_id
        }, function (data, status) {
            if (status == "success") {
                $('#attribute_management_data .scroll_col').html(data);
                $("#attribute_management").show();
                switchOffCollapse('attr_list_collapse');
              /*  switchOnCollapse('attribute_management_collapse');*/
            } else {
                alert("Request error.");
            }
        }, "html");
        return false;
    }

    function editAttribute(products_options_id, type_code) {

        if ( !type_code ) {
            type_code = $('#global_type_code').val();
        }

        if (type_code == 'suboption') {
            var global_id = $('#global_id').val();
        } else {
            var global_id = null
        }
        window.location.href = "{Yii::$app->urlManager->createUrl('productsattributes/attributeedit')}?products_options_id="+products_options_id+"&type_code="+type_code+"&global_id="+global_id;
        return false;
        $.post("productsattributes/attributeedit", {
            "products_options_id": products_options_id,
            "type_code": type_code,
            "global_id": global_id
        }, function (data, status) {
            if (status == "success") {
                $('#attribute_management_data .scroll_col').html(data);
                $("#attribute_management").show();
               /* switchOffCollapse('attr_list_collapse');*/

            } else {
                alert("Request error.");
            }
        }, "html");
        return false;
    }

    function confirmDeleteOption(option_id) {

        var cell_type = $('.table').find('td').find('.cell_type').val();

        $("#admin_management").hide();
        $.post("productsattributes/confirmadeleteoption", {
            'products_options_id': option_id,
            "cell_type": cell_type
        }, function (data, status) {
            if (status == "success") {
                $('#attribute_management_data .scroll_col').html(data);
                $("#attribute_management").show();
                /*switchOffCollapse('attr_list_collapse');*/
            } else {
                alert("Request error.");
                //$("#attribute_management").hide();
            }
        }, "html");
        return false;
    }

    function deleteOption() {
        $("#attribute_management").hide();
        $.post("productsattributes/optiondelete", $('#option_delete').serialize(), function (data, status) {
            if (status == "success") {
                resetStatement()
            } else {
                alert("Request error.");
            }
        }, "html");
        return false;
    }

    function saveAttribute() {

        //$("#attribute_management").hide();

        $.post("productsattributes/attributesubmit", $('#save_attribute_form').serialize(), function (data, status) {
            if (status == "success") {
                $('#attribute_management_data .scroll_col').html(data);
                $("#attribute_management").show();

                $('.table').DataTable().search('').draw(false);
                //$(".cell_identify[value='"+admin_id+"']").click();


            } else {
                alert("Request error.");
                //$("#attribute_management").hide();
            }
        }, "html");
        //$('#attribute_management_data').html('');
        return false;
    }

    $(document).ready(function(){
      $( ".js-table-sortable.datatable tbody" ).sortable({
        axis: 'y',
        update: function( event, ui ) {
          $(this).find('[role="row"]').each(function() {
            if ( this.id ) return;
            var cell_ident = $(this).find('.cell_identify');
            var cell_type = $(this).find('.cell_type');
            if ( cell_ident.length>0 && cell_type.length>0 ) {
              this.id = cell_type.val()+'_'+cell_ident.val();
            }
          });
          var post_data = [];
          $(this).find('[role="row"]').each(function() {
            var spl = this.id.indexOf('_');
            if ( spl===-1 ) return;
            post_data.push({ name:this.id.substring(0, spl)+'[]', value:this.id.substring(spl+1) });
          });
          var $dropped = $(ui.item);
          post_data.push({ name:'sort_'+$dropped.find('.cell_type').val(), value:$dropped.find('.cell_identify').val() });

          $.post("{Yii::$app->urlManager->createUrl('productsattributes/sort-order')}", post_data, function(data, status){
            if (status == "success") {
              resetStatement();
            } else {
              alert("Request error.");
            }
          },"html");
        },
        handle: ".handle"
      }).disableSelection();
      /*$('.table').on('xhr.dt', function ( e, settings, json, xhr ) {
       console.log(json);
       } );*/
    });

</script>

<div class="right_column">
<!--===  info ===-->
<div id="attribute_info" style="display: none;">
        <div class="widget box">
            <div class="widget-content" id="attribute_info_data">
                <div class="scroll_col">
                account info
                </div>
            </div>
        </div>
</div>
<!--===  info ===-->

<!--===  attribute management ===-->
<div id="attribute_management" style="display: none;">
        <div class="widget box">
            <div class="widget-content" id="attribute_management_data">
                <div class="scroll_col">
                account info
                </div>
            </div>
        </div>
</div>
</div>
</div>
<!--===  attribute management ===-->