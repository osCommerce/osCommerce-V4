<!--=== Page Header ===-->
<div class="page-header">
    <div class="page-title">
        <h3>{$app->controller->view->headingTitle}</h3>
    </div>
</div>
<!-- /Page Header -->

<!--===banners list===-->
<div class="widget box box-wrapp-blue filter-wrapp">
  <div class="widget-header filter-title">
    <h4>{$smarty.const.TEXT_FILTER}</h4>
    <div class="toolbar no-padding">
      <div class="btn-group">
        <span class="btn btn-xs widget-collapse"><i class="icon-angle-down"></i></span>
      </div>
    </div>
  </div>
  <div class="widget-content fillBan">
	<form id="filterForm" name="filterForm" onsubmit="return applyFilter();">
            <div class="banner-filter">

                {if $isMultiPlatforms}
                    <div class="platform-filter">
                        <div class="platform-filter-holder">
                            <label>{$smarty.const.TEXT_COMMON_PLATFORM_FILTER}</label>

                            <div class="f_row"><div class="f_td f_td_radio ftd_block"><label class="radio_label"><input type="checkbox" name="platform[]" class="js_platform_checkboxes" value=""> {$smarty.const.TEXT_COMMON_PLATFORM_FILTER_ALL}</label></div></div>
                            {foreach $platforms as $platform}
                                <div class="f_row"><div class="f_td f_td_radio ftd_block"><label class="radio_label"><input type="checkbox" name="platform[]" class="js_platform_checkboxes" value="{$platform['id']}" {if in_array($platform['id'], $app->controller->view->filters->platform)} data-checked="true" checked="checked"{else} data-checked="false" {/if}> {$platform['text']}</label></div></div>
                            {/foreach}
                        </div>
                    </div>
                {/if}

                <div class="banner-filter-right">
                    <div class="m-b-4">
                        {$groups}
                    </div>

                    <div class="align-right">
                        <a href="javascript:void(0)" onclick="return resetFilter();" class="btn">{$smarty.const.TEXT_RESET}</a>
                        <button class="btn btn-primary">{$smarty.const.TEXT_SEARCH}</button>
                    </div>
                </div>

            </div>

	</form>
	</div>
</div>
<div class="order-wrap">
<input type="hidden" id="row_id">
<div class="row container-box-list">
    <div class="col-md-12">
        <div class="widget-content widget_relative">	
            <table class="table table-striped table-checkable table-hover table-responsive table-bordered datatable double-grid table-banner_manager" data_ajax="banner_manager/list" checkable_list="{if $isMultiPlatforms}0{else}0,1,2{/if}">
                <thead>
                    <tr>
                        {foreach $app->controller->view->bannerTable as $tableItem}
                            <th{if isset($tableItem['not_important']) && $tableItem['not_important'] == 2} class="checkbox-column"{/if}{if isset($tableItem['not_important']) && $tableItem['not_important'] == 1} class="hidden-xs"{/if}>{$tableItem['title']}</th>
                            {/foreach}
                    </tr>
                </thead>

            </table>
        </div>
    </div>
</div>

<!--===/banners list===-->
<script>

var _start = false;
/*$(document).ready(function(){
	$('.create_item').on('click', function(e){
		var ev = e||window.event;
		ev.preventDefault();
		editBanner(0);
	})
});*/

function viewModule(id){
	$.get('banner_manager/view', {
		bID:id,
	}, 
	function(data, status){
            if (status == "success") {
                $('#banners_management_data .scroll_col').html(data);
                $("#banners_management").show();
               // deleteScroll();
                heightColumn();
            } else {
                alert("Request error.");
            }	
	});
}

function deleteItemConfirm(id){
	$.get('banner_manager/deleteconfirm', {
		bID:id,
	}, 
	function(data, status){
            if (status == "success") {
                $('#banners_management_data .scroll_col').html(data);
                $("#banners_management").show();
               // deleteScroll();
                heightColumn();
            } else {
                alert("Request error.");
            }	
	});
}
function resetStatementStatus() {
    var table = $('.table').DataTable();
    table.draw(false);
    return false;
}
function switchStatement(id, status) {
    $.post("banner_manager/switch-status", { 'id' : id, 'status' : status }, function(data, status){
        if (status == "success") {
            resetStatement();
        } else {
            alert("Request error.");
        }
    },"html");
}
function onClickEvent (obj, table){
$('#row_id').val(table.find(obj).index());
 $(".check_on_off").bootstrapSwitch(
                  {
									onSwitchChange: function (element, arguments) {
											switchStatement(element.target.value, arguments);
											return true;  
									},
									onText: "{$smarty.const.SW_ON}",
  offText: "{$smarty.const.SW_OFF}",
                    handleWidth: '20px',
                    labelWidth: '24px'
                  }
        );
  var event_id = $(obj).find('input.cell_identify').val();
  viewModule(event_id);
 $('.popupN').popUp({		
      box: "<div class='popup-box-wrap'><div class='around-pop-up'></div><div class='popup-box'><div class='popup-heading cat-head'>{$smarty.const.TEXT_CHANGE_BANNER_TYPE}</div><div class='pop-up-content'><div class='preloader'></div></div></div></div>"
	});
}

function onUnclickEvent(obj, table){
}
$(document).ready(function(){
		/*$('.sfilter').on('change', function(){
		$('#filterForm').serialize();
		/*$.post("banner_manager/list", { 'bagr' : $(this).val() }, function(data, status){
        if (status == "success") {
				//window.history.pushState('','','banner_manager?banner_group='+$(this).val());
						resetStatement();
        } else {
            alert("Request error.");
        }
    },"html");*/
	//	})	
})
function setFilterState() {
    orig = $('#filterForm').serialize();
    var url = window.location.origin + window.location.pathname + '?' + orig.replace(/[^&]+=\.?(?:&|$)/g, '').replace(/\[/g, '%5B').replace(/\]/g, '%5D');
    window.history.replaceState({ }, '', url);
}
function resetStatement() {
		setFilterState();
    $(".order-wrap").show();
    $("#edit_management").hide();

    //switchOnCollapse('groups_list_box_collapse');
    //switchOffCollapse('groups_management_collapse');

    $('#banner_management_data .scroll_col').html('');
    $('#banner_management').hide();

    var table = $('.table').DataTable();
    table.draw(false);

    $(window).scrollTop(0);

    return false;
}
function resetFilter() {
    $('select[name="filter_by"]').val('');
    //$('.js_platform_checkboxes').prop("checked", false);
		$("#row_id").val(0);
    resetStatement();
    return false;  
}
function applyFilter() {
    var $platforms = $('.js_platform_checkboxes');
    if ( $platforms.length>0 ) {
      var http_method = false;
      $platforms.filter('[data-checked]').each(function(){
        if ( this.checked != ($(this).attr('data-checked')=='true') ) {
          http_method = true;
        }
      });
      if ( http_method ) return true;
    }
    resetStatement();
    return false;    
}  
function closePopup() {
		$('.popup-box:last').trigger('popup.close');
		$('.popup-box-wrap:last').remove();
		return false;
}
function saveBannertype() {
    $.post("{$app->urlManager->createUrl('banner_manager/savetype')}", $('#save_banner_type').serialize(), function (data, status) {
        if (status == "success") {
            closePopup();
        } else {
            alert("Request error.");
        }
    }, "html");
		
    return false;
}		
$(document).ready(function(){
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
})
</script>


<!--===  banners management ===-->
<div class="row right_column" id="banners_management" style="display: none;">
        <div class="widget box">
            <div class="widget-content fields_style" id="banners_management_data">
                <div class="scroll_col"></div>
            </div>
        </div>
</div>
<!--=== banners management ===-->
</div>

<!--===  edit management ===-->
				<div class="row" id="edit_management" style="display: none;">
					<div class="col-md-12">
							<div class="fields_style" id="edit_management_data">
                                                            Action
							</div>
					</div>
</div>

