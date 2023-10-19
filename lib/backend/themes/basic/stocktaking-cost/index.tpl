{use class="\common\helpers\Html"}
{use class="\yii\helpers\Url"}
{\backend\assets\MultiSelectAsset::register($this)|void}

<!--=== Page Header ===-->
<div class="page-header">
  <div class="page-title">
    <h3>{$app->controller->view->headingTitle}</h3>
  </div> 
</div>
<!-- /Page Header -->
<!--<div>{$excluded}</div>-->
<style>
  tr.orange{
    background: #f9f9f9;
	border-bottom:3px solid #ddd;
  }
  .headd {
	padding:10px 0 0 10px;
  }
  .order-wrap a {
	line-height: 1.428571429;
	color: #424242;
  }
  .cedit-block.cedit-block-2 .cr-ord-cust:before {
    content: '\f1b3';
  }
  .cedit-block.cedit-block-3 .cr-ord-cust:before, .cedit-block.cedit-block-4 .cr-ord-cust:before {
    content: '\f0d6';
  }
.cedit-top {
  margin:10px;
}
.cedit-block {
    float: left;
    width: 33.3333%;
    padding: 0 1%;
    border-left: 1px solid #d9d9d9;
}
</style>
<div class="order-wrap">    
  <input type="hidden" id="row_id">
  <!--=== Page Content ===-->
 
  <div class="widget box box-wrapp-blue widget-closed filter-wrapp">
        <div class="widget-header filter-title">
            <h4 class="s_filter_title">{$smarty.const.TEXT_FILTER}</h4>
            <div class="toolbar no-padding">
              <div class="btn-group">
                <span class="btn btn-xs widget-collapse"><i class="icon-angle-up"></i></span>
              </div>
            </div>
        </div>
        <div class="widget-content filter_values">
        {Html::beginForm('stocktaking-cost', 'get', ['id'=>'filterForm', 'name'=>'stocktaking-filter'])}
        <div class="wrap_filters after wrap_filters_4">
            <div class="item_filter item_filter_1">
                <div class="tl_filters_title"></div>

                <div class="wl-td">
                    <label>{$smarty.const.TEXT_STATUS}</label>
                    {Html::dropDownList('status', $app->controller->selected_status, $app->controller->status, ['id' => 'statusId'])}
                </div>

                <div class="wl-td">
                    <label>{$smarty.const.TEXT_COMMON_PLATFORM_TAB}</label>
                    {Html::dropDownList('platforms[]', $app->controller->selected_platforms, $platforms, ['class' => 'form-control', 'multiple' => 'multiple', 'data-role' => 'multiselect'])}
                </div>
                {*
                <div class="wl-td">
                    <label>{$smarty.const.TABLE_HEADING_CATEGORY}</label>
                    {Html::dropDownList('categories', $app->controller->selected_categories, $categories, ['class' => 'form-control', 'multiple' => 'multiple', 'data-role' => 'multiselect'])}
                </div>
                *}
            </div>

            <div class="item_filter item_filter_2">
              <div class="tl_filters_title">{$smarty.const.OPTIONS_SHOW_COLUMNS}</div>
              <div class="filter_checkboxes_column">
                <div class="wl-td">
                    <label>
                    {Html::checkbox('showcolumns[]', in_array('cat', $app->controller->showcolumns), ['value'=>'cat'])}
                    {$smarty.const.OPTIONS_SHOW_CATEGORIES}
                    </label>
                </div>
                <div class="wl-td">
                    <label>
                    {Html::checkbox('showcolumns[]', in_array('sp', $app->controller->showcolumns), ['value'=>'sp'])}
                    {$smarty.const.OPTIONS_SHOW_SALE_PRICE}
                    </label>
                </div>
                <div class="wl-td">
                    <label>
                    {Html::checkbox('showcolumns[]', in_array('pp', $app->controller->showcolumns), ['value'=>'pp'])}
                    {$smarty.const.OPTIONS_SHOW_PURCHASE_PRICE}
                    </label>
                </div>
              </div>
            </div>

            <div class="item_filter item_filter_3">
              <div class="tl_filters_title">{$smarty.const.OPTIONS_GROUP_BY}</div>
              <div class="filter_checkboxes_column">
                <div class="wl-td">
                    <label>
                    {Html::checkbox('groupby[]', in_array('cat', $app->controller->groupby), ['value'=>'cat'])}
                    {$smarty.const.OPTIONS_SHOW_CATEGORIES}
                    </label>
                </div>
                <div class="wl-td">
                    <label>
                    {Html::checkbox('groupby[]', in_array('prod', $app->controller->groupby), ['value'=>'prod'])}
                    {$smarty.const.TABLE_HEADING_PRODUCTS}
                    </label>
                </div>
              </div>
            </div>

            <div style="float: right;bottom: 10px;position: absolute;right: 10px;">
            <a href="javascript:void(0)" onclick="return resetFilter();" class="btn">{$smarty.const.TEXT_RESET}</a>&nbsp;&nbsp;&nbsp;<button type="submit" class="btn btn-primary">{$smarty.const.TEXT_SEARCH}</button>
            </div>
        </div>
        {Html::endForm()}
        </div>
    </div>


  <div class="row">
    <div class="col-md-12">
      <div class="widget-content">
        <div class="alert fade in" style="display:none;">
          <i data-dismiss="alert" class="icon-remove close"></i>
          <span id="message_plce"></span>
        </div>
        <div class="headd" style="width:100%;">
			<div class="cedit-top after">
				   
			</div>
        </div>
		<div class="sh-scloll">Table scrolled</div>
		<div class="col-md-12">
		
        <table class="table table-striped table-bordered table-hover table-responsive table-checkable table-selectable js-table-sortable datatable table-no-search" verticalHeight="true" checkable_list="0" data_ajax="stocktaking-cost/list">
          <thead class="widget-fixed">
          <tr>
            {foreach $app->controller->view->CostTable as $tableItem}
              <th{if isset($tableItem['not_important']) && $tableItem['not_important'] == 1} class="hidden-xs"{/if} width="{$tableItem['width']}">{$tableItem['title']}</th>
            {/foreach}
          </tr>
          </thead>

        </table>
		</div>


        <p class="btn-wr">        	
            <a style="display:none;" href="javascript:void(0);" ></a>
            <button class="btn btn-primary" onClick="_export(this);">{$smarty.const.TEXT_EXPORT}</button>
        </p>
        
      </div>

    </div>
  </div>
  <script type="text/javascript">
    function setFilterState() {
        orig = $('#filterForm').serialize();
        var url = window.location.origin + window.location.pathname + '?' + orig.replace(/[^&]+=\.?(?:&|$)/g, '')
        window.history.replaceState({ }, '', url);
    }
    function resetFilter(){
        window.location.href="{Yii::$app->urlManager->createUrl('stocktaking-cost')}";
    }
    function resetStatement() {
    }
    function onClickEvent(obj, table) {
    }

    function onUnclickEvent(obj, table) {
    }

    function applyFilter() {
          resetStatement();
          return false;
    }

   var fTable;
   
   function maluem(rows){
        var row;
        $.each(rows, function (i, e){
            row = fTable.fnGetNodes(e);
            //console.log(e);return;
            $(row).addClass('orange');
        });
   }
   
   function onDraw(json, table){
    fTable = table;
    if (json.head){
        $('.cedit-top').html('');
		s=1;
        $.each(json.head.list, function (i, e){
		    s++;
            $('.cedit-top').append('<div class="cedit-block cedit-block-'+s+'"><div class="cr-ord-cust"><span>'+i+'</span><div>'+e+'</div></div></div>');
        });
        if (json.head.row){
            if (fTable.fnSettings().fnDisplayEnd() > 0){
                maluem(json.head.row);
            } else {
                var tm = setInterval(function(){
                    if (fTable.fnSettings().fnDisplayEnd() > 0){
                         maluem(json.head.row);
                         var row = fTable.fnGetNodes(json.head.last);
                         $(row).addClass('orange');
                         clearInterval(tm);
                    }
                }, 1000);
            }
      
        }
    }
    
    return;
   }
   
   function _export(obj){
        $.get('stocktaking-cost/export', 
                $('#filterForm').serialize(),
                function(data, status, e){
                        var filename = 'export.csv';
                        var disposition = e.getResponseHeader('Content-Disposition');
                        if (disposition && disposition.indexOf('attachment') !== -1) {
                            var filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
                            var matches = filenameRegex.exec(disposition);
                            if (matches != null && matches[1]) {
                                filename = matches[1].replace(/['"]/g, '');
                            }
                        }
                        var reader = new FileReader();
                        reader.onload = function(e) {
                          $('<a></a>').attr({ "href": e.target.result, "download": filename }).get(0).click();
                        }                
                        reader.readAsDataURL(new Blob([data], { type: 'application/vnd.ms-excel' }));
                });
                return false;
    }
    /*$('#filterForm').submit(function(e){
        setFilterState();
        return false;
    })*/
  </script>
  <!-- /Page Content -->
</div>