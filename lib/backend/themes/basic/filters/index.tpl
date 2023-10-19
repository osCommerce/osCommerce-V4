{use class="yii\helpers\Html"}

{if $filter_selected == 'orders'}
{\backend\assets\OrderAsset::register($this)|void}
{\backend\assets\MultiSelectAsset::register($this)|void}
{/if}

<div class="tabbable tabbable-custom" style="margin-bottom: 0;">
    <ul class="nav nav-tabs">
        {foreach $filters as $filter}
          <li class="{if $filter == $filter_selected} active {/if}"><a class="js_link_platform_modules_select" href="{Yii::$app->urlManager->createUrl(['filters/', 'type' => $filter])}""><span>{ucfirst($filter)}</span></a></li>
        {/foreach}
    </ul>
</div>
<div class="widget box box-wrapp-blue filter-wrapp">
    <div class="widget-header filter-title">
        <h4>{$smarty.const.TEXT_FILTER}</h4>
        <div class="toolbar no-padding">
          <div class="btn-group">
            <span class="btn btn-xs widget-collapse"><i class="icon-angle-down"></i></span>
          </div>
        </div>
    </div>
    <div class="widget-content">
        <form id="filterForm" name="filterForm" onsubmit="return applyFilter();">
            <input type="hidden" name="type" value="{$filter_selected}" />
            {if $filter_selected == 'orders'}
            {include file="../filters/orders.tpl"}
            {/if}
            {if $filter_selected == 'customers'}
            {include file="../filters/customers.tpl"}
            {/if}
            <div class="filters_btn">
                <a href="javascript:void(0)" onclick="return resetFilter();" class="btn">{$smarty.const.TEXT_RESET}</a>&nbsp;&nbsp;&nbsp;<button type="submit" class="btn btn-confirm">{$smarty.const.TEXT_SAVE}</button>
            </div>
        </form>
    </div>
</div>
<script type="text/javascript">
function resetFilter() {
    $.post("{$app->urlManager->createUrl('filters/reset')}", $('#filterForm').serialize(), function(data, status){
        if (status == "success") {
            alert(data.message);
            window.location.reload();
        } else {
            alert("Request error.");
        }
    },"json");
    return false;
}
function applyFilter() {
    $.post("{$app->urlManager->createUrl('filters/save')}", $('#filterForm').serialize(), function(data, status){
        if (status == "success") {
            alert(data.message);
            window.location.reload();
        } else {
            alert("Request error.");
        }
    },"json");
    return false;
}
{if $filter_selected == 'orders'}
function doCheckOrderStatus() {
    let element = $('div.modal-body select[name="change_status"]');
    $('div.modal-body #evaluation_state_force_holder').hide();
    $('div.modal-body #evaluation_state_restock_holder').hide();
    $('div.modal-body #evaluation_state_reset_cancel_holder').hide();
    $('div.modal-body #evaluation_state_force').prop('checked', true);
    $('div.modal-body #evaluation_state_restock').prop('checked', false);
    $('div.modal-body #evaluation_state_reset_cancel').prop('checked', false);
    if (element.length > 0) {
        console.log($(element).val());
        let evaluation_state_id = $(element).find('option[value="' + $(element).val() + '"]').attr('evaluation_state_id');
        console.log(evaluation_state_id);
        if (evaluation_state_id == '{\common\helpers\Order::OES_DISPATCHED}'
            || evaluation_state_id == '{\common\helpers\Order::OES_DELIVERED}'
        ) {
            $('div.modal-body #evaluation_state_force_holder').show();
        } else if (evaluation_state_id == '{\common\helpers\Order::OES_CANCELLED}') {
            $('div.modal-body #evaluation_state_restock_holder').show();
        } else if (evaluation_state_id == '{\common\helpers\Order::OES_PENDING}') {
            $('div.modal-body #evaluation_state_reset_cancel_holder').show();
        }
        return true;
    }
    return false;
}
$(document).ready(function() {
    doCheckOrderStatus();

    $(window).resize(function(){ 
        setTimeout(function(){ 
            var height_box = $('.order-box-list').height() + 2;
            $('#order_management .widget.box').css('min-height', height_box);
        }, 800);        
    })
    $(window).resize();
    
    
    $('.w-tdc.act_row input[type="text"]').prop('disabled', false);
    $('.w-tdc.act_row select').prop('disabled', false);
    
    $('input[name="date"]').click(function() { 
        if($(this).is(':checked')){ 
            $(this).parents().siblings('div.w-tdc').removeClass('act_row');
            $(this).parents('.w-tdc').addClass('act_row');
            $('.w-tdc input[type="text"]').prop('disabled', true);
            $('.w-tdc select').prop('disabled', true);
            $('.w-tdc.act_row input[type="text"]').prop('disabled', false);
            $('.w-tdc.act_row select').prop('disabled', false);
        }
    });

    $('#fcById').off('click').click( function () {
        if ($(this).is(':checked')) {
          $(this).parent().addClass('active_options');
          $("#fcLike").prop("checked", false);
          $("#fcLike").parent().removeClass('active_options');
          $("#fcCode").prop("disabled", true);
          $("#fcId").prop("disabled", false);
        } else {
          $("#fcCode").prop("disabled", false);
          $("#fcId").prop("disabled", true);
        }
      }
    );
    $('#fcLike').off('click').click( function () {
        if ($(this).is(':checked')) {
          $(this).parent().addClass('active_options');
          $("#fcById").prop("checked", false);
          $("#fcById").parent().removeClass('active_options');
          $("#fcCode").prop("disabled", false);
          $("#fcId").prop("disabled", true);
        } else {
          $("#fcCode").prop("disabled", true);
          $("#fcId").prop("disabled", false);
        }
      }
    );


    $('#fpFrom').off('click').click( function () {
        if ($(this).is(':checked')) {
          $(this).parent().addClass('active_options');
          $("#fpClass").prop("disabled", false);
          $("#fpFromSumm").prop("disabled", false);
        } else {
          $(this).parent().removeClass('active_options');
          $("#fpFromSumm").prop("disabled", true);
          if (!$("#fpTo").is(':checked')) {
            $("#fpClass").prop("disabled", true);
          }
        }
      }
    );
    $('#fpTo').off('click').click( function () {
        if ($(this).is(':checked')) {
          $(this).parent().addClass('active_options');
          $("#fpClass").prop("disabled", false);
          $("#fpToSumm").prop("disabled", false);
        } else {
          $(this).parent().removeClass('active_options');
          $("#fpToSumm").prop("disabled", true);
          if (!$("#fpFrom").is(':checked')) {
            $("#fpClass").prop("disabled", true);
          }
        }
      }
    );

    $('body').on('click', 'th.checkbox-column .uniform', function() { 
        if($(this).is(':checked')){
            $('tr.checkbox-column .uniform').prop('checked', true).uniform('update');
            $('.order-box-list .btn-wr').removeClass('disable-btn');
        }else{
            $('.order-box-list .btn-wr').addClass('disable-btn');
        }
    });
    
    $('select.select2-offscreen').change(function(){ 
        setTimeout(function(){ 
            var height_box = $('.order-box-list').height() + 2;
            $('#order_management .widget.box').css('min-height', height_box);
        }, 800); 
    });

    var $platforms = $('.js_platform_checkboxes');
    var check_platform_checkboxes = function(){
        var checked_all = true;
        $platforms.not('[value=""]').each(function () {
            if (!this.checked) checked_all = false;
        });
        $platforms.filter('[value=""]').each(function() {
            this.checked = checked_all
        });
    };
    check_platform_checkboxes();
    $platforms.on('click',function(){
        var self = this;
        if (this.value=='') {
            $platforms.each(function(){
                this.checked = self.checked;
            });
        }else{
            var checked_all = this.checked;
            if ( checked_all ) {
                $platforms.not('[value=""]').each(function () {
                    if (!this.checked) checked_all = false;
                });
            }
            $platforms.filter('[value=""]').each(function() {
                this.checked = checked_all
            });
        }
    });
    {if $departments}
    var $departments = $('.js_department_checkboxes');
    var check_department_checkboxes = function(){
        var checked_all = true;
        $departments.not('[value=""]').each(function () {
            if (!this.checked) checked_all = false;
        });
        $departments.filter('[value=""]').each(function() {
            this.checked = checked_all
        });
    };
    check_department_checkboxes();
    $departments.on('click',function(){
        var self = this;
        if (this.value=='') {
            $departments.each(function(){
                this.checked = self.checked;
            });
        }else{
            var checked_all = this.checked;
            if ( checked_all ) {
                $departments.not('[value=""]').each(function () {
                    if (!this.checked) checked_all = false;
                });
            }
            $departments.filter('[value=""]').each(function() {
                this.checked = checked_all
            });
        }
    });
    {/if}
});
{if \common\helpers\Acl::checkExtensionAllowed('OrderMarkers', 'allowed')}
function sendOrderFlag(id, flag_state) {
    var selected_ids = [];
    selected_ids[0] = id;
    if (typeof flag_state == "undefined") flag_state = 0;
    sendOrdersFlag(selected_ids, flag_state);
}
function flagSelectedOrders() {
    if (getTableSelectedCount() > 0) {
        var selected_ids = getTableSelectedIds();
        sendOrdersFlag(selected_ids, 0);
    }
    return false;
}
function sendOrdersFlag(selected_ids, flag_state) {
    bootbox.dialog({
        message: '{foreach $app->controller->view->flags as $flag}<label class="{$flag['class']}" style="{$flag['style']}">{\yii\helpers\Html::radio('o_flag', false, ['value' => $flag['id']])|escape:'javascript'}<span>{$flag['text']}</span></label><br>{/foreach}',
        title: "{$smarty.const.TEXT_SET_FLAG}",
        buttons: {
                success: {
                        label: "{$smarty.const.IMAGE_SAVE}",
                        className: "btn",
                        callback: function() {
                            $.post("{$app->urlManager->createUrl(['extensions/', 'module' => 'OrderMarkers', 'action' => 'adminActionSetFlag'])}", { 'selected_ids' : selected_ids, 'o_flag' : $('input:checked[name="o_flag"]').val() }, function(data, status){
                                if (status == "success") {
                                    resetStatement();
                                } else {
                                    alert("Request error.");
                                }
                            },"html");
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
    setTimeout(function(){
        $('input[name="o_flag"][value="'+flag_state+'"]').prop('checked', 'checked');
    }, 200);
}
function sendOrderMarker(id, marker_state) {
    var selected_ids = [];
    selected_ids[0] = id;
    sendOrdersMarker(selected_ids, marker_state);
}
function markerSelectedOrders() {
    if (getTableSelectedCount() > 0) {
        var selected_ids = getTableSelectedIds();
        sendOrdersMarker(selected_ids, 0);
    }
    return false;
}
function sendOrdersMarker(selected_ids, marker_state) {

        bootbox.dialog({
                message: '{foreach $app->controller->view->markers as $marker}<label class="{$marker['class']}" style="{$marker['style']}">{\yii\helpers\Html::radio('o_marker', false, ['value' => $marker['id']])|escape:'javascript'}<span>{$marker['text']}</span></label><br>{/foreach}',
                title: "{$smarty.const.TEXT_SET_MARKER}",
                buttons: {
                        success: {
                                label: "{$smarty.const.IMAGE_SAVE}",
                                className: "btn",
                                callback: function() {
                                    $.post("{$app->urlManager->createUrl(['extensions/', 'module' => 'OrderMarkers', 'action' => 'adminActionSetMarker'])}", { 'selected_ids' : selected_ids, 'o_marker' : $('input:checked[name="o_marker"]').val() }, function(data, status){
                                        if (status == "success") {
                                            resetStatement();
                                        } else {
                                            alert("Request error.");
                                        }
                                    },"html");
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
    setTimeout(function(){
        $('input[name="o_marker"][value="'+marker_state+'"]').prop('checked', 'checked');
    }, 200);
}
{/if}

$(document).ready(function(){
	//===== Date Pickers  =====//
	$( ".datepicker" ).datepicker({
		changeMonth: true,
                changeYear: true,
		showOtherMonths:true,
		autoSize: false,
		dateFormat: '{$smarty.const.DATE_FORMAT_DATEPICKER}',
                onSelect: function (e) { 
                    if ($(this).val().length > 0) { 
                      $(this).siblings('span').addClass('active_options');
                    }else{ 
                      $(this).siblings('span').removeClass('active_options');
                    }
                  }
        });
        $("select[data-role=multiselect]").multipleSelect({
            multiple: true,
            filter: true,
        });
		
		$('[data-role=multiselect-radio]').multipleSelect({
            multiple: false,
            filter: true,
            single: true,
            onClick : function(option){
                applyFilter();
            }
        });
 
        $('#selectCountry').autocomplete({
            source: "orders/countries",
            minLength: 0,
            autoFocus: true,
            delay: 0,
            appendTo: '.f_td_country',
            open: function (e, ui) {
              if ($(this).val().length > 0) {
                var acData = $(this).data('ui-autocomplete');
                acData.menu.element.find('a').each(function () {
                  var me = $(this);
                  var keywords = acData.term.split(' ').join('|');
                  me.html(me.text().replace(new RegExp("(" + keywords + ")", "gi"), '<b>$1</b>'));
                });
                $(this).siblings('label').addClass('active_options');
              }else{ 
                  $(this).siblings('label').removeClass('active_options');
              }
            },
            select: function(event, ui) {
                if ($(this).val().length > 0) { 
                    $(this).siblings('label').addClass('active_options');
                }else{ 
                    $(this).siblings('label').removeClass('active_options');
                }
                $('input[name="delivery_state"]').prop('disabled', true);
                if(ui.item.value != null){ 
                    $('input[name="delivery_state"]').prop('disabled', false);
                }
            }
        }).focus(function () {
          $(this).autocomplete("search");
          if ($(this).val().length > 0) { 
                    $(this).siblings('label').addClass('active_options');
                }else{ 
                    $(this).siblings('label').removeClass('active_options');
                }
        });
        
        $('#selectState').autocomplete({
            // source: "orders/state?country=" + $('#selectCountry').val(),
            source: function(request, response) {
                $.ajax({
                    url: "orders/state",
                    dataType: "json",
                    data: {
                        term : request.term,
                        country : $("#selectCountry").val()
                    },
                    success: function(data) {
                        response(data);
                    }
                });
            },
            minLength: 0,
            autoFocus: true,
            delay: 0,
            appendTo: '.f_td_state',
            open: function (e, ui) {
              if ($(this).val().length > 0) {
                var acData = $(this).data('ui-autocomplete');
                acData.menu.element.find('a').each(function () {
                  var me = $(this);
                  var keywords = acData.term.split(' ').join('|');
                  me.html(me.text().replace(new RegExp("(" + keywords + ")", "gi"), '<b>$1</b>'));
                });
                $(this).siblings('label').addClass('active_options');
              }else{ 
                  $(this).siblings('label').removeClass('active_options');
              }
            },
            select: function(event, ui) {
                if ($(this).val().length > 0) { 
                    $(this).siblings('label').addClass('active_options');
                }else{ 
                    $(this).siblings('label').removeClass('active_options');
                }
            }
        }).focus(function () {
          $(this).autocomplete("search");
          if ($(this).val().length > 0) { 
                $(this).siblings('label').addClass('active_options');
            }else{ 
                $(this).siblings('label').removeClass('active_options');
            }
        });  
});

{/if}
{if $filter_selected == 'customers'}
$('.fields_style select').change(function(){
    $(this).focusout();
});
$(document).ready(function() {
    
    //===== Date Pickers  =====//
    $( ".datepicker" ).datepicker({
            changeMonth: true,
            changeYear: true,
            showOtherMonths:true,
            autoSize: false,
            dateFormat: '{$smarty.const.DATE_FORMAT_DATEPICKER}'
    });
    
  $(window).resize(function () {
    setTimeout(function () {
      var height_box = $('.order-box-list').height() + 2;
      $('#order_management .widget.box').css('min-height', height_box);
    }, 800);
  })
  $(window).resize();
  
  $('.f_row.act_row input[type="text"]').prop('disabled', false);
    $('.f_row.act_row select').prop('disabled', false);
    
    $('input[name="date"]').click(function() { 
        if($(this).is(':checked')){ 
            $(this).parents().siblings('div.f_row').removeClass('act_row');
            $(this).parents('.f_row').addClass('act_row');
            $('.f_row input[type="text"]').prop('disabled', true);
            $('.f_row select').prop('disabled', true);
            $('.f_row.act_row input[type="text"]').prop('disabled', false);
            $('.f_row.act_row select').prop('disabled', false);
        }
    });  
  
  $('th.checkbox-column .uniform').click(function() { 
        if($(this).is(':checked')){
            $('.order-box-list .btn-wr').removeClass('disable-btn');
        }else{
            $('.order-box-list .btn-wr').addClass('disable-btn');
        }
    });

    $('#selectCountry').autocomplete({
        source: "customers/countries",
        minLength: 0,
        autoFocus: true,
        delay: 0,
        appendTo: '.f_td_country',
        open: function (e, ui) {
          if ($(this).val().length > 0) {
            var acData = $(this).data('ui-autocomplete');
            acData.menu.element.find('a').each(function () {
              var me = $(this);
              var keywords = acData.term.split(' ').join('|');
              me.html(me.text().replace(new RegExp("(" + keywords + ")", "gi"), '<b>$1</b>'));
            });
          }
        },
        select: function(event, ui) {
            $('input[name="state"]').prop('disabled', true);
            if(ui.item.value != null){ 
                $('input[name="state"]').prop('disabled', false);
            }
            $('input[name="city"]').prop('disabled', true);
            if(ui.item.value != null){ 
                $('input[name="city"]').prop('disabled', false);
            }
        }
    }).focus(function () {
      $(this).autocomplete("search");
    });
    
    $('#selectState').autocomplete({
        source: function(request, response) {
            $.ajax({
                url: "customers/state",
                dataType: "json",
                data: {
                    term : request.term,
                    country : $("#selectCountry").val()
                },
                success: function(data) {
                    response(data);
                }
            });
        },
        minLength: 0,
        autoFocus: true,
        delay: 0,
        appendTo: '.f_td_state',
        open: function (e, ui) {
          if ($(this).val().length > 0) {
            var acData = $(this).data('ui-autocomplete');
            acData.menu.element.find('a').each(function () {
              var me = $(this);
              var keywords = acData.term.split(' ').join('|');
              me.html(me.text().replace(new RegExp("(" + keywords + ")", "gi"), '<b>$1</b>'));
            });
          }
        },
        select: function(event, ui) {
            /*$('input[name="city"]').prop('disabled', true);
            if(ui.item.value != null){ 
                $('input[name="city"]').prop('disabled', false);
            }*/
        }
    }).focus(function () {
      $(this).autocomplete("search");
    });
    
    $('#selectCity').autocomplete({
        source: function(request, response) {
            $.ajax({
                url: "customers/city",
                dataType: "json",
                data: {
                    term : request.term,
                    country : $("#selectCountry").val(),
                    state :  $("#selectState").val(),
                },
                success: function(data) {
                    response(data);
                }
            });
        },
        minLength: 0,
        autoFocus: true,
        delay: 0,
        appendTo: '.f_td_city',
        open: function (e, ui) {
          if ($(this).val().length > 0) {
            var acData = $(this).data('ui-autocomplete');
            acData.menu.element.find('a').each(function () {
              var me = $(this);
              var keywords = acData.term.split(' ').join('|');
              me.html(me.text().replace(new RegExp("(" + keywords + ")", "gi"), '<b>$1</b>'));
            });
          }
        }
    }).focus(function () {
      $(this).autocomplete("search");
    });
    
{if $app->controller->view->filters->showGroup}
    $('#selectGroup').autocomplete({
        source: "customers/group",
        minLength: 0,
        autoFocus: true,
        delay: 0,
        appendTo: '.f_td_group',
        open: function (e, ui) {
          if ($(this).val().length > 0) {
            var acData = $(this).data('ui-autocomplete');
            acData.menu.element.find('a').each(function () {
              var me = $(this);
              var keywords = acData.term.split(' ').join('|');
              me.html(me.text().replace(new RegExp("(" + keywords + ")", "gi"), '<b>$1</b>'));
            });
          }
        }
    }).focus(function () {
      $(this).autocomplete("search");
    });
{/if}
    
    
    $('#selectCompany').autocomplete({
        source: "customers/company",
        minLength: 0,
        autoFocus: true,
        delay: 0,
        appendTo: '.f_td_company',
        open: function (e, ui) {
          if ($(this).val().length > 0) {
            var acData = $(this).data('ui-autocomplete');
            acData.menu.element.find('a').each(function () {
              var me = $(this);
              var keywords = acData.term.split(' ').join('|');
              me.html(me.text().replace(new RegExp("(" + keywords + ")", "gi"), '<b>$1</b>'));
            });
          }
        }
    }).focus(function () {
      $(this).autocomplete("search");
    });

    var $platforms = $('.js_platform_checkboxes');
    var check_platform_checkboxes = function(){
        var checked_all = true;
        $platforms.not('[value=""]').each(function () {
            if (!this.checked) checked_all = false;
        });
        $platforms.filter('[value=""]').each(function() {
            this.checked = checked_all
        });
    };
    check_platform_checkboxes();
    $platforms.on('click',function(){
        var self = this;
        if (this.value=='') {
            $platforms.each(function(){
                this.checked = self.checked;
            });
        }else{
            var checked_all = this.checked;
            if ( checked_all ) {
                $platforms.not('[value=""]').each(function () {
                    if (!this.checked) checked_all = false;
                });
            }
            $platforms.filter('[value=""]').each(function() {
                this.checked = checked_all
            });
        }
    });
    {if $departments}
    var $departments = $('.js_department_checkboxes');
    var check_department_checkboxes = function(){
        var checked_all = true;
        $departments.not('[value=""]').each(function () {
            if (!this.checked) checked_all = false;
        });
        $departments.filter('[value=""]').each(function() {
            this.checked = checked_all
        });
    };
    check_department_checkboxes();
    $departments.on('click',function(){
        var self = this;
        if (this.value=='') {
            $departments.each(function(){
                this.checked = self.checked;
            });
        }else{
            var checked_all = this.checked;
            if ( checked_all ) {
                $departments.not('[value=""]').each(function () {
                    if (!this.checked) checked_all = false;
                });
            }
            $departments.filter('[value=""]').each(function() {
                this.checked = checked_all
            });
        }
    });
    {/if}

});
{/if}
</script>
{if $filter_selected == 'orders'}
    
{if $ext = \common\helpers\Extensions::isAllowed('ShippingCarrierPick')}
    {$ext::orderIndexJs()}
{/if}

{/if}