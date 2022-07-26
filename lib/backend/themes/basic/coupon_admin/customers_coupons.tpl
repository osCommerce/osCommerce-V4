{use class="yii\helpers\Url"}
{use class="common\helpers\Html"}
<style>
.white {
 background-color: #fff!important;
}
</style>

<div class="widget box">
            <div class="widget-content">
             <div class="alert fade in hide ">
                  <i data-dismiss="alert" class="icon-remove close"></i>
                  <span id="message_place"></span>
              </div>
              <form name="customers_coupoms" method="post" action="{Url::to('coupon_admincustomerscodes/list')}" >
                {Html::input('hidden', 'cid', $cid)}
                <table class="table table-striped table-bordered table-hover table-responsive datatable" id="ajxtable">
                    <thead>
                    <tr>
                        <th>{$smarty.const.COUPON_CUSTOMERS_COUPONS_ONLY_FOR_CUSTOMER}</th>
                        <th>{$smarty.const.COUPON_CUSTOMERS_COUPON_CODE}</th>
                        <th>{$smarty.const.COUPON_CUSTOMERS_UPDATED}</th>                        
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>                
                </form>
        </div>       
</div>
      
<div class="widget box customer-coupon" style="display: none;">                    
   <div class="widget" id="customerscoupons_management" style="display: none;">
        <div class="widget-head">
            <h4 class="heading">Edit or ADD</h4>
        </div>
    </div>   
</div>
<INPUT TYPE="BUTTON" onClick="return closePopup();" VALUE="{$smarty.const.COUPON_CUSTOMERS_IMAGE_CANCEL}" class="btn btn-cancle" style="float:right;">
                
<script>
var hTable;
$(document).ready(function() {    
    hTable = $('#ajxtable').DataTable( {
        "columns": [
        { "data": "only_for_customer" },
        { "data": "coupon_code" },
        { "data": "date_added" }
        ],
        "columnDefs": [
        {
        "targets": [0],
        "visible": true,
        "searchable": true
        },
        {
        "targets": [1],
        "visible": true,
        "searchable": false
        },
        {
        "targets": [2],
        "visible": true,
        "searchable": false
        }, 
        ],
        "ajax": "{$ajaxListUrl}",
        "responsive": true,
        "processing": true,
        "serverSide": true,
    } );
    $(hTable.context[0].aanFeatures.f)
    .append('<button accesskey="A" class="btn btn-default" style="float:left;" onclick="addCustomersCoupon();return false;">{$smarty.const.COUPON_CUSTOMERS_ADD_MORE}</button>');
    {if $couponUsed == 0}
    $(hTable.context[0].aanFeatures.f)
    .append('<button class="btn btn-default" style="float:left;margin-left:5px" onclick="deleteAll();return false;">{$smarty.const.COUPON_CUSTOMERS_DELETE_ALL}</button>');
    {/if}
    $('.input-group-addon', hTable.context[0].aanFeatures.f).remove();
    $(hTable.context[0].aanFeatures.f).css('width', '100%').parent().css('width', '100%');
} );

function editCustomersCoupon(id, cid) {    
        {*if $couponUsed > 0}
        return false;
        {/if*}
        var parameters = new Object();
        parameters.u_id = id;
        parameters.cid = cid;
        
        $("#customerscoupons_management").hide();
        $.post("{$ajaxEditUrl}", parameters, function(data, status) {
            if (status == "success") {
                $('#customerscoupons_management').html(data);
                $('.popup-box-wrap').css('top','250px');
                $('.customer-coupon').show();
                $('#customerscoupons_management').show();
        
            } else {              
                alert('Request error.');
            }
        }, 'html');
        return false;
}
function addCustomersCoupon() {
            
        var parameters = new Object();
        parameters.u_id = 0;
        
        //$("#customerscoupons_management").hide();
        $.post("{$ajaxEditUrl}", parameters, function(data, status) {
            if (status == "success") {                
                $('#customerscoupons_management').html('').html(data);
                $('.popup-box-wrap').css('top','250px');
                $('.customer-coupon').show();
                $('#customerscoupons_management').show();
                $('#only_for_customer').focus();
        
            } else {              
                alert('Request error.');
            }
        }, 'html');
        return false;
}

function saveCustomersCode(id){
    
    var parameters = new Object();
    parameters.id = id;
    parameters.cid = $('input[name=cid]').val();
    parameters.only_for_customer =$("form[name=edit_customercode]").find('input[name=only_for_customer]').val();
    parameters.coupon_code = $("form[name=edit_customercode]").find('input[name=coupon_code]').val();
        
    if(parameters.only_for_customer == ''){
        alert('{$smarty.const.COUPON_REQUIRED_FIELDS_EMPTY|escape}');
        return false;
    }

    $.post("{$ajaxSaveUrl}", parameters, function(data) {
        //console.info(data);
        if (data.message == "ok") {
            alert('{$smarty.const.COUPON_CUSTOMERS_SUCCESSFULLY_SAVED}');
            //console.info(hTable);
            hTable.ajax.reload();
            $('.customer-coupon').hide();            
            $('#customerscoupons_management').hide();
            
        } else {
            alert(data.message);
        }
    }, 'json');
    return false;
}

function deleteCustomersCode(id) {
    {if $couponUsed > 0}
    return false;
    {/if}
    var parameters = new Object();
    parameters.id = id;
    parameters.cid = $('input[name=cid]').val();
    
    bootbox.dialog({
            message: "{$smarty.const.COUPON_CUSTOMERS_ARE_YOU_SURE}",
            title: "{$smarty.const.COUPON_CUSTOMERS_CONFIRMATION}",
            buttons: {
                    success: {
                        label: "Yes",
                        className: "btn-delete",
                        callback: function() {
                            $.post("{$ajaxDeleteUrl}", parameters, function(data) {
                                if (data.message == "ok") {                                    
                                    
                                    hTable.ajax.reload();
                                    $('.customer-coupon').hide();
                                    $('.customer-coupon').hide();
                                    $('#customerscoupons_management').hide();
                                } else {
                                    alert(data.message);
                                }
                            },"json");
                        }
                    },                   
                    main: {
                        label: "Cancel",
                        className: "btn-cancel",
                        callback: function() {

                        }
                    }
            }
    });
    return false;
}

function deleteAll()
{
    var parameters = new Object();    
    parameters.cid = $('input[name=cid]').val();
    
    bootbox.dialog({
            message: "{$smarty.const.COUPON_CUSTOMERS_ARE_YOU_SURE_ALL}",
            title: "{$smarty.const.COUPON_CUSTOMERS_CONFIRMATION}",
            buttons: {
                    success: {
                        label: "Yes",
                        className: "btn-delete",
                        callback: function() {
                            $.post("{$ajaxDeleteAllUrl}", parameters, function(data) {
                                if (data.message == "ok") {
                                    
                                    alert('{$smarty.const.COUPON_CUSTOMERS_SUCCESSFULLY_DELETED}');
                                    hTable.ajax.reload();
                                    $('.customer-coupon').hide();                                  
                                    $('#customerscoupons_management').hide();
                                    closePopup();
                                    $('input[name=coupon_csv_loaded]').val('');
                                } else {
                                    alert(data.message);
                                }
                            },"json");
                        }
                    },                   
                    main: {
                        label: "Cancel",
                        className: "btn-cancel",
                        callback: function() {

                        }
                    }
            }
    });
    return false;
}

function closePopup()
{
    $('.popup-box:last').trigger('popup.close');
    $('.popup-box-wrap:last').remove();
    return false;
}

</script>