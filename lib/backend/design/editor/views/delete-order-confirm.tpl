<script>    
    $(document).ready(function(){        
        $('.pop-up-close:last').trigger('click');
        bootbox.dialog({
            message: "{$smarty.const.TEXT_INFO_DELETE_INTRO}"+'<div class="restock"><label class="restock"><input type="checkbox" class="uniform" name="restock" id="restock" value="1" {$restock_disabled}  {$restock_selected}> {$smarty.const.TEXT_INFO_RESTOCK_PRODUCT_QUANTITY}</label></div>',
            title: "{$smarty.const.TEXT_INFO_HEADING_DELETE_ORDER}",
            buttons: {
                success: {
                    label: "{$smarty.const.TEXT_BTN_YES}",
                    className: "btn",
                    callback: function() {                        
                        $.post("{$url}", {
                            'action':'delete_order',
                            'restock':$('[name=restock]:checkbox').prop('checked'),
                        }, function(data, status){                        
                            if (status == "success") {                                
                                window.location.href= data.redirect;
                            } else {
                                alert("Request error.");
                            }
                        },"json");
                    }
                },
            }
        });
        
    })
</script>
