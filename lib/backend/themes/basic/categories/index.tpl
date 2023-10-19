{\backend\assets\Categories::register($this)|void}
{\backend\design\SelectProducts::widget([ 'onlyIncludeJs' => true ])}
<!--=== Page Header ===-->
{$directOutput=false}
{include file="./cat_main_box.tpl"}
<div class="page-header">
        <div class="page-title">
                <h3>{$app->controller->view->headingTitle}</h3>
        </div>
</div>
<!-- /Page Header -->

<!--=== Page Content ===-->
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
              <form id="filterForm" name="filterForm" class="catalog-filters" onsubmit="return applyFilter();">
                {if $departments}
                <div class="f_row f_row_pl_cus f_row_pl platform-filter">
                    <div class="f_td">
                        <label>{$smarty.const.TEXT_COMMON_DEPARTMENTS_FILTER}</label>
                    </div>
                    <div class="f_td f_td_radio ftd_block">
                        <div><label class="radio_label"><input type="checkbox" name="departments[]" class="js_department_checkboxes" value=""> {$smarty.const.TEXT_COMMON_PLATFORM_FILTER_ALL}</label></div>
                        {foreach $departments as $department}
                            <div><label class="radio_label"><input type="checkbox" name="departments[]" class="js_department_checkboxes" value="{$department['id']}" {if in_array($department['id'], $app->controller->view->filters->departments)} data-checked="true" checked="checked"{else} data-checked="false" {/if}> {$department['text']}</label></div>
                        {/foreach}
                    </div>
                </div>
                {/if}
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

            <div class="filter_categories {if $isMultiPlatforms}filter_categories_1{/if}">
              <div class="filter_block after">
                  <div class="filter_left">
                        <div class="filter_row row_with_label">
                            <label>{$smarty.const.TEXT_SEARCH_BY}</label>
                            <select class="form-control" name="by">
                                {foreach $app->controller->view->filters->by as $Item}
                                    <option {$Item['selected']} value="{$Item['value']}">{$Item['name']}</option>
                                {/foreach}
                            </select>
                      </div>
                   </div>
                <div class="filter_right">
                    <div class="filter_row filter_disable">
                    <input type="text" name="search" value="{$app->controller->view->filters->search}" class="form-control" />
                  </div>
                </div>
                <div class="filter_left">                  
                  <div class="filter_row row_with_label">
                    <label>{$smarty.const.TEXT_BRAND}</label>
                    <div class="f_td f_td_group brands">
                        <input type="text" value="{$app->controller->view->filters->brand}" name="brand" id="selectBrand" class="form-control" placeholder="{$smarty.const.TEXT_CHOOSE_BRAND}">
                    </div>
                  </div>
                  <div class="filter_row status_row row_with_label">
                    <label>{$smarty.const.TEXT_STATUS}</label>
                    <select name="status" class="form-control">
                        {foreach $app->controller->view->filters->status as $Item}
                            <option {$Item['selected']} value="{$Item['value']}">{$Item['name']}</option>
                        {/foreach}
                    </select>
                  </div>
                  <div class="filter_row stock_row row_with_label">
                    <label>{$smarty.const.TEXT_STOCK}</label>
                    <select name="stock" class="form-control">
                        {foreach $app->controller->view->filters->stock as $Item}
                            <option {$Item['selected']} value="{$Item['value']}">{$Item['name']}</option>
                        {/foreach}
                    </select>
                  </div>
                  <div class="filter_attr">
                    <input type="checkbox" class="uniform" name="prod_attr" value="1" {if $app->controller->view->filters->prod_attr == 1}checked{/if}>
                    <label>{$smarty.const.TEXT_PRODUCTS_ATTR}</label>
                  </div>
                </div>
                <div class="filter_right">
                  
                  <div class="filter_row supllier_filter">
                    <label>{$smarty.const.TEXT_SUPPLIER}</label>
                    <div class="f_td f_td_group suppliers">
                        <input type="text" value="{$app->controller->view->filters->supplier}" name="supplier" id="selectSupplier" class="form-control" placeholder="{$smarty.const.TEXT_CHOOSE_SUPPLIER}">
                    </div>
                  </div>
                  <div class="filter_row supllier_filter">
                    <label>{$smarty.const.TEXT_PRODUCT_SOURCE}:</label>
                    <div class="f_td f_td_group">
                        <input type="text" value="{$app->controller->view->filters->source}" name="source" id="selectSupplierSource" class="form-control js-sources" placeholder="{$smarty.const.TEXT_CHOOSE_SUPPLIER_SOURCE}">
                    </div>
                  </div>
                  <div class="price_row after">
                    <div class="price_title">{$smarty.const.TEXT_PRODUCTS_PRICE_INFO}</div>
                    <div class="price_desc">
                      <span>{$smarty.const.TEXT_FROM}</span>
                      <input type="text" name="price_from" value="{$app->controller->view->filters->price_from}" class="form-control">
                      <span>{$smarty.const.TEXT_TO}</span>
                      <input type="text" name="price_to" value="{$app->controller->view->filters->price_to}" class="form-control">
                    </div>
                  </div>
                  <div class="weight_row">
                    <label class="weight_title">{$smarty.const.TEXT_WEIGHT}:</label>
                    <div class="weight_desc">
                      <div class="weight_field_text">
                        <span>{$smarty.const.TEXT_FROM}</span>
                        <input type="text" name="weight_from" value="{$app->controller->view->filters->weight_from}" class="form-control">
                        <span>{$smarty.const.TEXT_TO}</span>
                        <input type="text" name="weight_to" value="{$app->controller->view->filters->weight_to}" class="form-control">
                      </div>
                      <div class="weight_field">
                        <input type="radio" name="weight_value" value="kg" {if $app->controller->view->filters->weight_kg}checked{/if}>
                        <span>Kg</span>
                        <input type="radio" name="weight_value" value="lbs" {if $app->controller->view->filters->weight_lbs}checked{/if}>
                        <span>Lbs</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="filter_check_pad">
              <div class="filter_checkboxes">
                <div>
                  <input type="checkbox" class="uniform" name="low_stock" value="1" {if $app->controller->view->filters->low_stock == 1}checked{/if}>
                  <span>{$smarty.const.TEXT_LOW_STOCK}</span>
                </div>
                <div>
                  <input type="checkbox" class="uniform" name="featured" value="1" {if $app->controller->view->filters->featured == 1}checked{/if}>
                  <span>{$smarty.const.TEXT_FEATURED}</span>
                </div>
                <div>
                  <input type="checkbox" class="uniform" name="gift" value="1" {if $app->controller->view->filters->gift == 1}checked{/if}>
                  <span>{$smarty.const.TEXT_GIFT_WRAP}</span>
                </div>
                <div>
                  <input type="checkbox" class="uniform" name="virtual" value="1" {if $app->controller->view->filters->virtual == 1}checked{/if}>
                  <span>{$smarty.const.TEXT_VIRTUAL_PRODUCT}</span>
                </div>
                <div>
                  <input type="checkbox" class="uniform" name="all_bundles" value="1" {if $app->controller->view->filters->all_bundles == 1}checked{/if}>
                  <span>{$smarty.const.TEXT_ALL_BUNDLES}</span>
                </div>
                <div>
                  <input type="checkbox" class="uniform" name="type_listing" value="1" {if $app->controller->view->filters->type_listing == 1}checked{/if}>
                  <span>{$smarty.const.TEXT_DASHBOARD_LISTING}</span>
                </div>
                <div>
                  <input type="checkbox" class="uniform" name="type_not_listing" value="1" {if $app->controller->view->filters->type_not_listing == 1}checked{/if}>
                  <span>{$smarty.const.TEXT_DASHBOARD_MASTER}</span>
                </div>
                <div>
                  <input type="checkbox" class="uniform" name="sub_children" value="1" {if $app->controller->view->filters->sub_children == 1}checked{/if}>
                  <span>{$smarty.const.TEXT_DASHBOARD_CHILD}</span>
                </div>
                <div>
                  <input type="checkbox" class="uniform" name="sale" value="1" {if $app->controller->view->filters->sale == 1}checked{/if}>
                  <span>{$smarty.const.TEXT_SALE}</span>
                </div>
                  <div>
                      <input type="checkbox" class="uniform" name="wo_images" value="1" {if $app->controller->view->filters->wo_images == 1}checked{/if}>
                      <span>{$smarty.const.TEXT_FILTER_WITHOUT_IMAGES}</span>
                  </div>
              </div>
              </div>
              <div class="filters_buttons">
                <a href="javascript:void(0)" onclick="return resetFilter();" class="btn">{$smarty.const.TEXT_RESET}</a>
                <button class="btn btn-primary">{$smarty.const.TEXT_SEARCH}</button>
              </div>
            </div>
            {if $es = \common\helpers\Acl::checkExtensionAllowed('EventSystem', 'allowed')}
                {$es::event()->exec('getEventInformation')}
            {/if}
                  <input type="hidden" name="listing_type" id="listing_type" value="{$app->controller->view->filters->listing_type}" />
                  <input type="hidden" name="category_id" id="global_id" value="{$app->controller->view->filters->category_id}" />
                  <input type="hidden" name="brand_id" id="brand_id" value="{$app->controller->view->filters->brand_id}" />
                  <input type="hidden" name="row" id="row_id" value="{$app->controller->view->filters->row}" />
            </form>
          </div>
        </div>
        <div class="category_list">
          <div class="cat_left_column">
            <!-- Tabs-->
            <div class="tabbable tabbable-custom scroll_col">
                    <ul class="nav nav-tabs">
                        <li{if $app->controller->view->filters->listing_type == 'category'} class="active"{/if} data-bs-toggle="tab" data-bs-target="#tab_1_1">
                            <a onclick="changeListingCategory()"><i class="icon-folder-open"></i><span>{$smarty.const.TEXT_CATEGORIES}</span></a>
                        </li>

                        <li{if $app->controller->view->filters->listing_type == 'brand'} class="active"{/if} data-bs-toggle="tab" data-bs-target="#tab_1_2">
                            <a onclick="changeListingBrand()"><i class="icon-tag"></i><span>{$smarty.const.TEXT_BRANDS}</span></a>
                        </li>
                    </ul>
                    <div class="tab-content">
                            <div class="tab-pane{if $app->controller->view->filters->listing_type == 'category'} active{/if}" id="tab_1_1">
                              <div class="top_cat">
      <div class="cat_search_by">
        <div class="input-group input-group-order"><span class="input-group-addon dt-ic-search"><i class="icon-search"></i></span><input type="search" id="categorysearch" class="form-control" placeholder="{$smarty.const.ENTRY_SEARCH_CATEGORIES}"></div>
      </div>
     <div class="sorting_collapse after">
        <div class="switch_collapse" id="cat_main_box_switch_collapse">
          <a href="#" class="expand_all{if ! $collapsed } switch_active{/if}">{$smarty.const.ENTRY_EXPAND_ALL}</a>
          <a href="#" class="collapse_all{if $collapsed } switch_active{/if}">{$smarty.const.ENTRY_COLLAPSE_ALL}</a>
        </div>
       {*<div class="sw_sort_by_cat">
         <select name="sort_by_cat" class="form-control">
           <option value="sort by">Sort by</option>
           <option value="sort by">Sort by</option>
           <option value="sort by">Sort by</option>
         </select>
       </div>*}
     </div>
   </div>
                              <div class="dd cat_main_box">
                                  
{call renderCategoriesTree items=$app->controller->view->categoriesTree collapsed=$collapsed level=0}

                              </div>
                              <div class="cat_buttons after">
                                {if \common\helpers\Acl::rule(['TEXT_CATEGORIES', 'IMAGE_EDIT'])}<a class="btn btn-add-category btn-primary js_create_new_category" href="{Yii::$app->urlManager->createUrl('categories/categoryedit')}">{$smarty.const.TEXT_CREATE_NEW_CATEGORY}</a>{/if}
                                {if \common\helpers\Acl::rule(['TABLE_HEADING_PRODUCTS', 'IMAGE_EDIT'])}<a class="btn btn-add-product btn-primary js_create_new_product"  href="{Yii::$app->urlManager->createUrl('categories/productedit')}">{$smarty.const.TEXT_CREATE_NEW_PRODUCT}</a>{/if}
                              </div>
                            </div>
                            <div class="tab-pane{if $app->controller->view->filters->listing_type == 'brand'} active{/if}" id="tab_1_2">
                              <div class="top_brands after">
                                <div class="brand_search_by">
                                  <div class="input-group input-group-order"><span class="input-group-addon dt-ic-search"><i class="icon-search"></i></span><input type="search" id="brandsearch" class="form-control" placeholder="{$smarty.const.TEXT_SEARCH_BRANDS}"></div>
                                </div>
                                <!--<div class="sort_by_brands">
                                  <select class="form-control">
                                    <option value="Sort by">Sort by</option>
                                    <option value="Sort by">Sort by</option>
                                    <option value="Sort by">Sort by</option>
                                  </select>
                                </div>!-->
                              </div>
                              <div class="brand_box">
                                <ul>
                                    <li class="li_block li-block-top"><span class="brand_li"><span id="0" onclick="changeBrand(this)">{$smarty.const.TEXT_ALL}</span></span></li>
                                    <li class="li_block li-block-top"><span class="brand_li"><span id="-1" onclick="changeBrand(this)">{$smarty.const.TEXT_ALL_WITHOUT_BRAND}</span></span></li>
                                     {foreach $app->controller->view->brandsList as $brandItem}
                                         <li id="brands-{$brandItem.id}" class="li_block{if $brandItem.id == $app->controller->view->filters->brand_id} selected{/if}">

                                             <span class="handle"><i class="icon-hand-paper-o"></i></span>

                                             <span class="brand_li">
                                                 <span class="brand_text" id="{$brandItem.id}" onClick="changeBrand(this)">{$brandItem.text}</span>

                                                <span class="function-buttons">
                                                 {if \common\helpers\Acl::rule(['TEXT_LABEL_BRAND', 'IMAGE_EDIT'])}<a href="{Yii::$app->urlManager->createUrl(['categories/brandedit', 'manufacturers_id' => $brandItem.id])}" class="edit_brand"><i class="icon-pencil"></i></a>{/if}

                                                 {if \common\helpers\Acl::rule(['TEXT_LABEL_BRAND', 'IMAGE_DELETE'])}<a class="delete_brand" href="{Yii::$app->urlManager->createUrl(['categories/confirm-manufacturer-delete', 'manufacturers_id' => $brandItem.id])}"><i class="icon-trash"></i></a>{/if}

                                                </span>
                                             </span>

                                         </li>
                                    {/foreach}
                                </ul>
                              </div>
                              {if \common\helpers\Acl::rule(['TEXT_LABEL_BRAND', 'IMAGE_EDIT'])}<div class="cat_brand_buttons">
                                <a href="{Yii::$app->urlManager->createUrl('categories/brandedit')}" class="btn btn-primary"><i class="icon-tag"></i>{$smarty.const.TEXT_CREATE_NEW_BRANDS}</a>
                              </div>{/if}
                            </div>
                    </div>
            </div>
          </div>
            <!--END TABS-->
          <div class="cat_center">
                                                                                
    <div class="order-wrap">                            <!--=== List ===-->
				<div class="row order-box-list">
					<div class="col-md-12">
							<div class="widget-content">

                                <div id="list_bread_crumb"></div>
								<table id="categoriesTable" class="table table-checkable table-bordered table-hover table-responsive table-selectable datatable tab-status sortable-grid catelogue-grid" checkable_list="" data_ajax="categories/list">
									<thead>
										<tr>
                        {foreach $app->controller->view->catalogTable as $tableItem}
                            <th{if isset($tableItem['not_important']) && $tableItem['not_important'] == 1} class="hidden-xs"{/if}{if isset($tableItem['not_important']) && $tableItem['not_important'] == 2} class="checkbox-column"{/if}>{$tableItem['title']}</th>
                        {/foreach}
										</tr>
									</thead>
									
								</table>
                <div class="count_category">
                  <span>{$smarty.const.TEXT_CATEGORIES} <strong id="categories_counter">0</strong></span>
                  <span>{$smarty.const.TEXT_PRODUCTS} <strong id="products_counter">0</strong></span>
                </div>
							</div>
					</div>
				</div>
				
                                <!-- / List -->
                                             
<script type="text/javascript">
  /*
function switchOffCollapse(id) {
    if ($("#"+id).children('i').hasClass('icon-angle-down')) {
        $("#"+id).click();
    }
    CKEDITOR.replaceAll('ckeditor');
}

function switchOnCollapse(id) {
    if ($("#"+id).children('i').hasClass('icon-angle-up')) {
        $("#"+id).click();
    }
}
*/
function setFilterState() {
    orig = $('#filterForm').serialize();
    var url = window.location.origin + window.location.pathname + '?' + orig.replace(/[^&]+=\.?(?:&|$)/g, '').replace(/\[/g, '%5B').replace(/\]/g, '%5D');
    window.history.replaceState({ }, '', url);
}

function resetStatement() {
    setFilterState();
    //$("#catalog_management").hide();
    //switchOnCollapse('catalog_list_collapse');
    var table = $('.table').DataTable();
    table.draw(false);
    $('#categoriesTable .js-cat-batch-master').each(function () {
        this.checked = false;
        if ( typeof $.uniform !=='undefined' ) $.uniform.update();
        $('.js-batch-buttons').addClass('disable-btn');
        $('.right_column .scroll_col').show();
        $('.batchCol').hide();
    });
    //$(window).scrollTop(0);
    return false;
}
function getTableSelectedCount() {
    return $('#categoriesTable .js-cat-batch:checked').not('js-cat-batch-master').length;
}

function afterClickBatchSelection() {
    
    if(getTableSelectedCount() > 0){
        $( ".datatable tbody tr" ).addClass('ui-state-disabled');
        /*$('#categoriesTable .js-cat-batch:checked').not('js-cat-batch-master').each(function () {
            if ( !this.checked ) return;
            $(this).parent('span').parent('div').parent('td').parent('tr').removeClass('ui-state-disabled');
        });*/
    }else{
        $( ".datatable tbody tr" ).removeClass('ui-state-disabled');
    }
}
var files = {};
var form;

function editCategory(category_id) {
    $("#catalog_management").hide();// hide right column
    $.post("categories/categoryedit?categories_id="+category_id, {}, function(data, status){
      if (status == "success") {
        $('#catalog_management_data .scroll_col').html(data);
        $("#catalog_management").show();
        //switchOffCollapse('catalog_list_collapse');
        files = {};
        addFileListeners($(':file'), files);
      } else {
        alert("Request error.");
      }
    },"html");
    return false;
}
                           
function checkCategoryForm() {
    $("#catalog_management").hide();
    cke_preload();
    var category_id = $( "input[name='categories_id']" ).val();
    form = collectData('new_category', files);
    var xhr = window.ActiveXObject ? new ActiveXObject("Microsoft.XMLHTTP") : new XMLHttpRequest();
            xhr.onreadystatechange = function() {
              if (xhr.readyState == 4) {
                if(xhr.status == 200) {
                  //switchOnCollapse('catalog_list_collapse');
                  var table = $('.table').DataTable();
                  table.draw(false);
                  setTimeout('$(".cell_identify[value=\''+category_id+'\']").click();', 500);
                } else {
                  alert("Request error.");
                }
              } 
            };  
    xhr.open("POST", 'categories/categorysubmit', true);
    xhr.send(form);
    return false;
}
                                    
function checkProductForm() {
    $("#catalog_management").hide();
    cke_preload();
    var products_id = $( "input[name='products_id']" ).val();
    form = collectData('products_edit', files);
    var xhr = window.ActiveXObject ? new ActiveXObject("Microsoft.XMLHTTP") : new XMLHttpRequest();
            xhr.onreadystatechange = function() {
              if (xhr.readyState == 4) {
                if(xhr.status == 200) {
                  //switchOnCollapse('catalog_list_collapse');
                  var table = $('.table').DataTable();
                  table.draw(false);
                  $('#messageStack').html(xhr.responseText);
                  setTimeout('$(".cell_identify[value=\''+products_id+'\']").click();', 500);
                } else {
                  alert("Request error.");
                }
              } 
            };  
    xhr.open("POST", 'categories/productsubmit', true);
    xhr.send(form);
    return false;
}
                                    

function editProduct(products_id) {
    $("#catalog_management").hide();
    $.post("categories/productedit", { 'products_id' : products_id }, function(data, status){
        if (status == "success") {
            $('#catalog_management_data .scroll_col').html(data);
            $("#catalog_management").show();
            //switchOffCollapse('catalog_list_collapse');
            files = {};
            addFileListeners($(':file'), files);
        } else {
            alert("Request error.");
        }
    },"html");
    return false;
}

function deleteProduct() {
    $("#catalog_management").hide();
    $.post("categories/productdelete", $('#products_edit').serialize(), function(data, status){
        if (status == "success") {
            resetStatement()
        } else {
            alert("Request error.");
        }
    },"html");
    return false;
}

function confirmDeleteProduct(products_id) {
$("#catalog_management").hide();
$.post("categories/confirmproductdelete", { 'products_id' : products_id }, function(data, status){
    if (status == "success") {
        $('#catalog_management_data .scroll_col').html(data);
        $("#catalog_management").show();
        //switchOffCollapse('catalog_list_collapse');
    } else {
        alert("Request error.");
    }
},"html");
    return false;
}

function confirmMoveProduct(products_id) {
    var categories_id = $('#global_id').val();
    $("#catalog_management").hide();
    $.post("{Yii::$app->urlManager->createUrl('categories/confirm-product-move')}", { 'products_id' : products_id, 'categories_id' : categories_id }, function(data, status){
        if (status == "success") {
            $('#catalog_management_data .scroll_col').html(data);
            $("#catalog_management").show();
        } else {
            alert("Request error.");
        }
    },"html");
    return false;
}

function moveProduct() {
    $("#catalog_management").hide();
    $.post("{Yii::$app->urlManager->createUrl('categories/product-move')}", $('#products_move').serialize(), function(data, status){
        if (status == "success") {
            resetStatement();
        } else {
            alert("Request error.");
        }
    },"html");
    return false;
}

function confirmCopyProduct(products_id) {
    var categories_id = $('#global_id').val();
    $("#catalog_management").hide();
    $.post("{Yii::$app->urlManager->createUrl('categories/confirm-product-copy')}", { 'products_id' : products_id, 'categories_id' : categories_id }, function(data, status){
        if (status == "success") {
            $('#catalog_management_data .scroll_col').html(data);
            $("#catalog_management").show();
        } else {
            alert("Request error.");
        }
    },"html");
    return false;
}

function copyProduct() {
    $.post("{Yii::$app->urlManager->createUrl('categories/product-copy')}", $('#products_copy').serialize(), function(data, status){
        if (status == "success") {
            resetStatement();
        } else {
            alert("Request error.");
        }
    },"html");
    return false;
}

function confirmCopyProductAttr(products_id) {
    $("#catalog_management").hide();
    $.post("{Yii::$app->urlManager->createUrl('categories/confirm-product-attr-copy')}", { 'products_id' : products_id }, function(data, status){
        if (status == "success") {
            $('#catalog_management_data .scroll_col').html(data);
            $("#catalog_management").show();
            if ( typeof $.uniform !=='undefined' ) {
                $('.uniform').uniform();
            }

            var wrapper = $('#products_attr_copy');
            var products_name = $('input[name="products_name"]', wrapper);
            var products_id = $('#copy_to_products_id', wrapper);

            products_name.autocomplete({
                source: function( request, response ) {
                    $.get('index/search-suggest', {
                        keywords: request.term,
                        no_click: true,
                        suggest: 1,
                        json: true
                    }, function(data) {
                        response(data);
                    }, 'json');
                },
                create: function(){
                    $(this).data( 'ui-autocomplete' )._renderItem = function( ul, item ) {
                        var cls = "";
                        if (item.hasOwnProperty('status') && item.status == 0) {
                            cls = "dis_module";
                        }
                        return $( "<li class='" + cls + "'></li>" )
                            .data( "item.autocomplete", item )
                            .append( "<a><span>" + item.label + "</span></a>")
                            .appendTo( ul );
                        };
                },
                minLength: 2,
                autoFocus: true,
                delay: 300,
                appendTo: '#products_attr_copy .search-product',
                select: function(event, ui) {
                            if (ui.item.id > 0){
                                products_name.val(ui.item.label);
                                products_id.val(ui.item.id);
                            } else {
                                products_name.val('');
                                products_id.val('');
                            }
                        },
            });
                
            
        } else {
            alert("Request error.");
        }
    },"html");
    return false;
}

function copyProductAttr() {
    $.post("{Yii::$app->urlManager->createUrl('categories/product-attr-copy')}", $('#products_attr_copy').serialize(), function(data, status){
        if (status == "success") {
            if (data.ok) {
                resetStatement();
            } else if (data.message != '') {
                alert(data.message);
            }
        } else {
            alert("Request error.");
        }
    },"json");
    return false;
}

function confirmSyncNow(products_id, local_id,  directory_id) {
    bootbox.confirm( "{$smarty.const.TEXT_CONFIRM_NS_SYNC|escape:'javascript'}", function(result){
        if (result){
            $.post("{Yii::$app->urlManager->createUrl('categories/ns-sync')}", 'r_id=' + products_id + '&l_id=' + local_id + '&d_id=' + directory_id, function(data){
              if ( data && data.status && data.status=='OK' ) {
                if (data.inqueue == 1) {
                  //alert("In queue");
                  $('#exchangeExportResult').html('In queue');
                } else {
                  $('#exchangeExportResult').html('Complete');
                  if ( data.messages && data.messages.length ) {
                      $('#exchangeExportResult').html('');
                      for(var i=0; i<data.messages.length;i++){
                          $('#exchangeExportResult').append('<div>'+data.messages[i]+'</div>');
                      }
                  }
                  resetStatement();
                }
              } else {
                  $('#exchangeExportResult').html('Request error');
                  //alert("Request error.");
              }
            },'json');
            bootbox.dialog({
                message: '<div id="exchangeExportResult">Sync in progress...</div>',
                title: "Force Sync",
                buttons: {
                    done:{
                        label: "{$smarty.const.TEXT_BTN_OK}",
                        className: "btn-cancel"
                    }
                }
            });
        }
    } );
}

function linkNS(products_id, local_id, directory_id) {
  bootbox.confirm( '{$smarty.const.TEXT_ENTER_REMOTE_ID|escape:'javascript'}<br><input name="ns_id" id="ns_id" value="' + products_id + '" class="form-control">', function(result){
    if (result){
      $.post("{Yii::$app->urlManager->createUrl('categories/ns-sync-update-id')}", 'r_id=' + products_id + '&l_id=' + local_id + '&n_id=' + $('#ns_id').val() + '&d_id=' + directory_id, function(data){
        if ( data && data.status && data.status=='OK' ) {
            resetStatement();
        } else {
            alert("Request error.");
        }
      },'json');
    }
  });
}

function switchStatement(type ,id, status) {
    $.post("categories/switch-status", { 'type' : type, 'id' : id, 'status' : status }, function(data, status){
        if (status == "success") {
            resetStatement();
        } else {
            alert("Request error.");
        }
    },"html");
}
function onClickEvent(obj, table, event) {
    if ( $(event.target).is(':input') ) return false;
    var dtable = $(table).DataTable();
    var id = dtable.row('.selected').index();
    $("#row_id").val(id);
    setFilterState();
    $(".check_on_off").bootstrapSwitch(
        {
            onSwitchChange: function (element, arguments) {
                switchStatement(element.target.name, element.target.value, arguments);
                return true;  
            },
            onText: "{$smarty.const.SW_ON|escape:'javascript'}",
            offText: "{$smarty.const.SW_OFF|escape:'javascript'}",
            handleWidth: '20px',
            labelWidth: '24px'
        }
    );
    $(".check_on_off_check").change(
        function (element, arguments) {
            ///console.log(element.target.name, element.target.value, arguments);
            if (this.checked) {
                var arguments = true;
            }else{
                var arguments = false;
            }
            switchStatement(element.target.name, element.target.value, arguments);
            return true;  
    });
    $("#catalog_management").hide();
    $('#catalog_management_data .scroll_col').html('');
    var event_id = $(obj).find('input.cell_identify').val();
    var type_code = $(obj).find('input.cell_type').val();
    let get = { };
    const url = new URL(window.location.href);
    url.searchParams.forEach((val, key) => { get[key] = val})
    if (type_code == 'category') {
        $("#catalog_management_title").text('Category Management');
        $.post("categories/categoryactions", { 'categories_id' : event_id, get }, function(data, status){
            if (status == "success") {
                $('#catalog_management_data .scroll_col').html(data);
                $("#catalog_management").show();
            } else {
                alert("Request error.");
            }
        },"html");
    } else if (type_code == 'product') {
        $("#catalog_management_title").text('Product Management');
        $.post("categories/productactions", { 'products_id' : event_id, 'categories_id':$('#global_id').val(), get }, function(data, status){
            if (status == "success") {
                $('#catalog_management_data .scroll_col').html(data);
                $("#catalog_management").show();
                $('#catalog_management_data .scroll_col a.actionPopup').popUp();
            } else {
                alert("Request error.");
            }
        },"html");
    } else if (type_code == 'parent') {
        event_id = $('#global_id').val();
        $("#catalog_management_title").text('Category Management');
        $.post("categories/categoryactions", { 'categories_id' : event_id, get }, function(data, status){
            if (status == "success") {
                $('#catalog_management_data .scroll_col').html(data);
                $("#catalog_management").show();
            } else {
                alert("Request error.");
            }
        },"html");
    }
}

function onUnclickEvent(obj, table, event) {
    if ( $(event.target).is(':input') ) return false;
    $("#catalog_management").hide();
    var event_id = $(obj).find('input.cell_identify').val();
    var type_code = $(obj).find('input.cell_type').val();
    /*$(table).dataTable({
        destroy: true,
        "ajax": "categories/list/parent/"+event_id
    });*/
    if (type_code == 'category' || type_code == 'parent') {
        $('#global_id').val(event_id);
        changeCategory($('span#'+event_id))
        //$(table).DataTable().draw(false);
    }

}

function resetFilter() {
    $('select[name="by"]').val('');
    $('input[name="search"]').val('');
    $('input[name="brand"]').val('');
    $('input[name="supplier"]').val('');
    $('select[name="stock"]').val('');
    //$('.js_platform_checkboxes').prop("checked", false);
    //$('.js_department_checkboxes').prop("checked", false);
    $('input[name="price_from"]').val('');
    $('input[name="price_to"]').val('');
    $('input[name="weight_from"]').val('');
    $('input[name="weight_to"]').val('');
    $('input[name="prod_attr"]').prop("checked", false);
    $('input[name="low_stock"]').prop("checked", false);
    $('input[name="featured"]').prop("checked", false);
    $('input[name="gift"]').prop("checked", false);
    $('input[name="virtual"]').prop("checked", false);
    $('input[name="all_bundles"]').prop("checked", false);
    $('input[name="type_listing"]').prop("checked", false);
    $('input[name="type_not_listing"]').prop("checked", false);
    $('input[name="sub_children"]').prop("checked", false);
    $('input[name="sale"]').prop("checked", false);
    $("#row_id").val(0);
    resetStatement();
  {* ???? why reset filter reset active cat 
  $("div.dd3-content.selected").removeClass('selected');
    $(".categories_ul li.dd-item[data-id='"+$('#global_id').val()+"'] div.dd3-content").addClass('selected');*}
    $("div.brand_box li.selected").removeClass('selected');
    $("div.brand_box li[id='brands-"+$('#brand_id').val()+"']").addClass('selected');
    return false;  
}

function closePopup() {
    $('.popup-box:last').trigger('popup.close');
    $('.popup-box-wrap:last').remove();
}
function applyFilter() {
    $("div.dd3-content.selected").removeClass('selected');
    $("div.brand_box li.selected").removeClass('selected');
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
{if $departments}
    var $departments = $('.js_department_checkboxes');
    if ( $departments.length>0 ) {
      var http_method = false;
      $departments.filter('[data-checked]').each(function(){
        if ( this.checked != ($(this).attr('data-checked')=='true') ) {
          http_method = true;
        }
      });
      if ( http_method ) return true;
    }
{/if}
    resetStatement();
    return false;    
}                                    
function changeCategory(obj) {
    var event_id = $(obj).attr('id');
    if ( typeof event_id === 'undefined' ) {
        event_id = $(obj).data('id');
    }
    $('#global_id').val(event_id);
    $("div.dd3-content.selected").removeClass('selected');
    $("#cat-main-box-cat-" + event_id).addClass('selected');
    
    var table = $('.table').DataTable();
    table.page( 'first' );// .draw( 'page' );
    
    resetFilter();
    //resetStatement();
    return false;
}
function changeListingCategory() {
    $("#listing_type").val('category');
    resetFilter();
}
function changeBrand(obj) {
    var event_id = $(obj).attr('id');
    $('#brand_id').val(event_id);
    $("li.li_block.selected").removeClass('selected');
    $(obj).parent('span').parent('li').addClass('selected');
    resetStatement();
    return false;
}
function changeListingBrand() {
    $("#listing_type").val('brand');
    resetFilter();
}

function confirmDeleteCategory(categories_id) {
    $("#catalog_management").hide();
    $.post("categories/confirmcategorydelete", { 'categories_id' : categories_id }, function(data, status){
        if (status == "success") {
            $('#catalog_management_data .scroll_col').html(data);
            $("#catalog_management").show();
            //switchOffCollapse('catalog_list_collapse');
        } else {
            alert("Request error.");
        }
    },"html");
    return false;
}

function deleteCategory() {
  $("#catalog_management").hide();
  $.post("{Yii::$app->urlManager->createUrl('categories/categorydelete')}", $('#categories_edit').serialize(), function(data, status){
    if (status == "success") {
        closePopup();
        initCategoryTree(data);
        $("#categorysearch").val('');
        $('#global_id').val(0);
        $('#row_id').val('');
        resetStatement();
    } else {
        alert("Request error.");
    }
  },"html");

  return false;
}

function deleteManufacturer() {
    $.post("{Yii::$app->urlManager->createUrl('categories/manufacturer-delete')}", $('#manufacturer_delete').serialize(), function(data, status){
            if (status == "success") {
                closePopup();
                $( ".brand_box" ).html(data);
                $('.edit_brand').popUp({
                    box: "<div class='popup-box-wrap'><div class='around-pop-up'></div><div class='popup-box popupEditCat'><div class='pop-up-close'></div><div class='popup-heading cat-head'>Editing brand <span class='js-popup-brand-name'></span></div><div class='pop-up-content'><div class='preloader'></div></div></div></div>"
                });
                $('.delete_brand').popUp({
                    box: "<div class='popup-box-wrap'><div class='around-pop-up'></div><div class='popup-box popupBrandDelete'><div class='pop-up-close'></div><div class='popup-heading cat-head'>Delete brand</div><div class='pop-up-content'><div class='preloader'></div></div></div></div>"
                });
                resetStatement();
            } else {
                alert("Request error.");
            }
        },"html");
    return false;
}
function confirmMoveCategory(categories_id) {
    $("#catalog_management").hide();
    $.post("{Yii::$app->urlManager->createUrl('categories/confirm-category-move')}", { 'categories_id' : categories_id }, function(data, status){
        if (status == "success") {
            $('#catalog_management_data .scroll_col').html(data);
            $("#catalog_management").show();
        } else {
            alert("Request error.");
        }
    },"html");
    return false;
}

function confirmSortGroupped(categories_id, hasChildren) {
  var timeout = 3000;
    var buttons = {
          cancel: {
              label: "{$smarty.const.TEXT_BTN_NO|escape:'javascript'}",
          },
          success: {
              label: "{$smarty.const.TEXT_BTN_OK|escape:'javascript'}",
              className: 'btn-success',
              callback: function (data) {
                $.post("{Yii::$app->urlManager->createUrl('categories/sort-products')}", { 'categories_id': categories_id}, function(data){
                    if ( data && data.status && data.status=='OK' ) {
                      var sgDialog = bootbox.alert({
                        message: "{$smarty.const.TEXT_MESSEAGE_SUCCESS|escape:'javascript'}",
                        animate: false,
                      });
                      applyFilter();
                      setTimeout(function () {
                        sgDialog.modal('hide');
                      }, timeout);

                    }
                },'json');
              }
          }
      };

    if (hasChildren) {
      buttons.recursively = {
              label: "{$smarty.const.TEXT_RECURSIVELY|escape:'javascript'}",
              className: 'btn-success',
              callback: function (data) {
                $.post("{Yii::$app->urlManager->createUrl('categories/sort-products')}", { 'categories_id': categories_id, 'recursively':1}, function(data){
                    if ( data && data.status && data.status=='OK' ) {
                      var sgDialog = bootbox.alert({
                        message: "{$smarty.const.TEXT_MESSEAGE_SUCCESS|escape:'javascript'}",
                        animate: false,
                      });
                      applyFilter();
                      setTimeout(function () {
                        sgDialog.modal('hide');
                      }, timeout);
                    }
                },'json');
              }
          };
    }


    bootbox.dialog({
      message: "{$smarty.const.TEXT_CONFIRM_SORT_GROUPPED|escape:'javascript'}",
      animate: false,
      buttons: buttons
    });

    return false;
}

function moveCategory() {
  $("#catalog_management").hide();
  $.post("{Yii::$app->urlManager->createUrl('categories/category-move')}", $('#categories_move').serialize(), function(data, status){
      if (status == "success") {
        initCategoryTree(data);
        $("#categorysearch").val('');
        resetStatement();
      } else {
          alert("Request error.");
      }
  },"html");

  return false;
}

function initCategoryTree(data) {
  $('.cat_main_box').hide();
  $('.cat_main_box').html(data);
  $('.edit_cat').popUp({
      box: "<div class='popup-box-wrap'><div class='around-pop-up'></div><div class='popup-box popupEditCat'><div class='pop-up-close'></div><div class='popup-heading cat-head'>Editing category <span class='js-popup-category-name'></span></div><div class='pop-up-content'><div class='preloader'></div></div></div></div>"
  });
  $('.delete_cat').popUp({
      box: "<div class='popup-box-wrap'><div class='around-pop-up'></div><div class='popup-box popupBrandDelete'><div class='pop-up-close'></div><div class='popup-heading cat-head'>Delete category</div><div class='pop-up-content'><div class='preloader'></div></div></div></div>"
  });
  $('.cat_main_box .tl-wrap-li-left-cat').each(function(){
    var head = $('.collapse_span', this);
    head.off('click').on('click', function(){
      _id = $(this).attr('data-id-suffix');
      var content = $('ol#ol-sub-cat-'+_id);
      var c_data = localStorage.getItem('closed_data');
      if (!c_data) c_data = '';
      if ($(this).hasClass('c_up')){
        $(this).removeClass('c_up');
        content.slideDown();
          localStorage.setItem('closed_data', c_data.replace(_id + '|', ''))
      } else {
        $(this).addClass('c_up');
        content.slideUp();
          localStorage.setItem('closed_data', c_data + _id + '|')
      }
    });
  });

  $('.cat_main_box').show();
  
  if ($('#categorysearch').val() !== '') {
    $('#cat_main_box_switch_collapse .collapse_all').removeClass('switch_active');
    $('#cat_main_box_switch_collapse .expand_all').addClass('switch_active');
  } else {
  {if $collapsed }
    $('#cat_main_box_switch_collapse .expand_all').removeClass('switch_active');
    $('#cat_main_box_switch_collapse .collapse_all').addClass('switch_active');
  {/if}
  }
}

function getSelectedCatalogCount() {
    var selected_count = 0;
    $('#categoriesTable .js-cat-batch').not('.js-cat-batch-master').each(function () {
        if ( !this.checked ) return;
        selected_count++;
    });
    return selected_count;
}
function getSelectedCatalogItems() {
    var selected = [];
    $('#categoriesTable .js-cat-batch').not('.js-cat-batch-master').each(function () {
        if ( !this.checked ) return;
        selected.push({
            name: this.name,
            value: $(this).val()
        });
    });
    return selected;
}

function deleteSelectedCatalog(){
    bootbox.confirm( "{$smarty.const.TEXT_CONFIRM_DELETE_SELECTED_ITEMS|escape:'javascript'}", function(result){
        if (result){
            var post_data = getSelectedCatalogItems();
            post_data.push({
                name: 'categories_id',
                value: $('#global_id').val()
            });
            $.post("{Yii::$app->urlManager->createUrl('categories/delete-batch')}", post_data, function(data){
                if ( data && data.status && data.status=='ok' ) {
                    applyFilter();
                }
            },'json');
        }
    } );

    return false;
}

function switchCatalogState(state){
    var post_data = getSelectedCatalogItems();
    post_data.push({
        name: 'state', value: state
    });
    $.post("{Yii::$app->urlManager->createUrl('categories/switch-status-batch')}", post_data, function(data){
        if ( data && data.status && data.status=='ok' ) {
            applyFilter();
        }
    },'json');
    return false;
}

function batchMove() {
    var count_items = getSelectedCatalogCount();
    if (count_items > 0) {
        var selected_categories = [];
        var selected_products = [];
        $('#categoriesTable .js-cat-batch').not('.js-cat-batch-master').each(function () {
            if ( !this.checked ) return;
            var type = $(this).parents('tr').find('input.cell_type').val();
            if (type == 'category') {
                var name = $(this).parents('tr').find('div.cat_name').children('b').text();
                selected_categories.push(name);
            }
            if (type == 'product') {
                var name = $(this).parents('tr').find('span.prodNameC').text();
                selected_products.push(name);
            }
        });
        
        var message = '<div class="">';
        
        message += '{sprintf($smarty.const.TEXT_MOVE, '')} <div class="choose-visibility"><select name="move_to_category_id" class="col-md-12 select2 select2-offscreen">{foreach \common\helpers\Categories::get_category_tree() as $category}<option value="{$category.id}">{$category.text|escape:'javascript'}</option>{/foreach}</select></div>{*tep_draw_pull_down_menu('move_to_category_id', \common\helpers\Categories::get_category_tree(), 0)*}';
        
        if (selected_categories.length > 0) {
            message += '<h3>Selected categories:</h3>';
            for (var i = 0, len = selected_categories.length; i < len; i++) {
                message += '<b>' + selected_categories[i] + '</b><br>';
            }
        }
        if (selected_products.length > 0) {
            message += '<h3>Selected products:</h3>';
            for (var i = 0, len = selected_products.length; i < len; i++) {
                message += '<b>' + selected_products[i] + '</b><br>';
            }
            message += '<br><label class="control-label">{$smarty.const.TEXT_CHOISE_METHOD} </label><div class=""><label class="radio"><input type="radio" class="uniform" name="copy_to" value="move" checked>{$smarty.const.TEXT_INFO_HEADING_MOVE_PRODUCT}</label><label class="radio"><input type="radio" class="uniform" name="copy_to" value="link">{$smarty.const.TEXT_COPY_AS_LINK}</label><label class="radio"><input type="radio" class="uniform" name="copy_to" value="dublicate">{$smarty.const.TEXT_COPY_AS_DUPLICATE}</label></div><label class="control-label">{$smarty.const.TEXT_COPY_ATTRIBUTES} ({$smarty.const.TEXT_COPY_ATTRIBUTES_ONLY})</label><div class=""><label class="radio"><input type="radio" class="uniform" name="copy_attributes" value="yes" checked>{$smarty.const.TEXT_YES}</label><label class="radio"><input type="radio" class="uniform" name="copy_attributes" value="no">{$smarty.const.TEXT_NO}</label></div>';
        }
        message += '</div>';
        message += '<script>$(".select2").select2();<\/script>';

        var post_data = getSelectedCatalogItems();
        
        bootbox.dialog({
                message: message,

                title: "{$smarty.const.TEXT_MOVE_OR_COPY_BACH|escape}",
                buttons: {
                        success: {
                                label: "{$smarty.const.TEXT_YES|escape:'javascript'}",
                                className: "btn btn-primary",
                                callback: function() {
                                    var copy_to = $('input[name="copy_to"]:checked').val();
                                    var copy_attributes = $('input[name="copy_attributes"]:checked').val();
                                    var current_category_id = $('#global_id').val();
                                    var categories_id = $('select[name="move_to_category_id"]').val();
                                    post_data.push({
                                        name: 'type', value: 'mixed'
                                    });
                                    post_data.push({
                                        name: 'categories_id', value: categories_id
                                    });
                                    post_data.push({
                                        name: 'copy_to', value: copy_to
                                    });
                                    post_data.push({
                                        name: 'copy_attributes', value: copy_attributes
                                    });
                                    post_data.push({
                                        name: 'current_category_id', value: current_category_id
                                    });

                                    $.post("{Yii::$app->urlManager->createUrl('categories/copy-move')}", post_data, function(data, status){
                                        if (status == "success") {
                                            initCategoryTree(data);
                                            $("#categorysearch").val('');
                                            resetStatement();
                                        } else {
                                            alert("Request error.");
                                        }
                                    },"html");
                                }
                        },
                        cancel: {
                                label: "{$smarty.const.IMAGE_CANCEL|escape:'javascript'}",
                                className: "btn-cancel",
                                callback: function() {
                                        //console.log("Primary button");
                                }
                        }
                }
        });
        
        
    }
}

$(document).ready(function() {
    $('#categoriesTable').on('click','.js-cat-batch',function(event){ 
        if ( $(this).hasClass('js-cat-batch-master') ) {
            var master = this;
            $('#categoriesTable .js-cat-batch').each(function(){
                this.checked = master.checked;
                if ( this.checked ){
                    $('.js-batch-buttons').removeClass('disable-btn');
                    $('.right_column .scroll_col').hide();
                    $('.batchCol').show();
                }else {
                    $('.js-batch-buttons').addClass('disable-btn');
                    $('.right_column .scroll_col').show();
                    $('.batchCol').hide();
                }
            });
            if ( typeof $.uniform !=='undefined' ) $.uniform.update();
        }else{
            var any_checked = false;
            var all_checked = -1;
            $('#categoriesTable .js-cat-batch').not('.js-cat-batch-master').each(function(){
                if (this.checked) any_checked = true;
                if (all_checked==-1) {
                    all_checked = this.checked?1:0;
                }else{
                    if (!this.checked) all_checked = 0;
                }
            });
            if ( all_checked==1 ) {
                $('#categoriesTable .js-cat-batch-master').each(function(){
                    this.checked = true;
                });
            }else{
                $('#categoriesTable .js-cat-batch-master').each(function(){
                    this.checked = false;
                });
            }
            if ( any_checked ){
                $('.js-batch-buttons').removeClass('disable-btn');
                $('.right_column .scroll_col').hide();
                $('.batchCol').show();
            }else {
                $('.js-batch-buttons').addClass('disable-btn');
                $('.right_column .scroll_col').show();
                $('.batchCol').hide();
            }
            if ( typeof $.uniform !=='undefined' ) $.uniform.update();
        }
    });
    $('#selectBrand').autocomplete({
        source: "{Yii::$app->urlManager->createUrl('categories/brands')}",
        minLength: 0,
        autoFocus: true,
        delay: 0,
        appendTo: '.f_td_group.brands',
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
    
    $('#selectSupplier').autocomplete({
        source: "{Yii::$app->urlManager->createUrl('categories/suppliers')}",
        minLength: 0,
        autoFocus: true,
        delay: 0,
        appendTo: '.f_td_group.suppliers',
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

    /*$( ".cat_main_box" ).nestable();
    $( ".cat_main_box" ).on('change', function() {
        console.log($(this).nestable('serialize'));
        /!*var data = window.JSON.stringify($(this).nestable('serialize'));
        $.post("{Yii::$app->urlManager->createUrl('categories/sort-order')}", { 'categories' : data }, function(data, status){
            if (status == "success") {
                resetStatement();
            } else {
                alert("Request error.");
            }
        },"html");*!/
    });*/
    $( ".categories_ul ol" ).sortable({
        connectWith: ".categories_ul ol",
        handle: ".handle",
        update: function(){
            const categories = JSON.stringify(categoriesTree($('.cat_main_box > ol > li')));
            $.post("{Yii::$app->urlManager->createUrl('categories/sort-order')}", { categories }, function(data, status){
                if (status == "success") {
                    resetStatement();
                } else {
                    alert("Request error.");
                }
            },"html");
        }
    });

    function categoriesTree($list){
        const tree = [];
        $list.each(function () {
            const children = categoriesTree($('> ol > li', this))
            if (children.length) {
                tree.push({ id: $(this).data('id'), children})
            }else {
                tree.push({ id: $(this).data('id')})
            }
        })
        return tree;
    }

    $( ".datatable tbody" ).sortable({
        stop: function( event, ui ) {
            var elem = document.elementFromPoint(event.clientX, event.clientY);
            var obj = $(elem).parents("li.dd-item");
            if (obj[0] != undefined) {
//                console.log(ui.item[0]);//orig
//                console.log( obj[0] );//target
                
//                var title = $(obj[0]).children('div.dd3-content').children('span.cat_li').children('span.cat_text').text();
//                var categories_id = $(obj[0]).attr('data-id');
                //console.log( categories_id );
                
                var cell_identify = $(ui.item[0]).find('.cell_identify').val();
//                console.log( cell_identify[0] );
                var cell_type = $(ui.item[0]).find('.cell_type').val();
//                console.log( cell_type[0] );
                
                //if (cell_type[0] != undefined) {
                    //var type_code = $(cell_type).val();
                    var count_items = getSelectedCatalogCount();
                    if (count_items > 0) {
                        var title = $(obj[0]).children('div.tl-wrap-li-left-cat').children('div.dd3-content').children('span.cat_li').children('span.cat_text').text();
                        var categories_id = $(obj[0]).attr('data-id');
                        
                        var selected_categories = [];
                        var selected_products = [];
                        $('#categoriesTable .js-cat-batch').not('.js-cat-batch-master').each(function () {
                            if ( !this.checked ) return;
                            var type = $(this).parents('tr').find('input.cell_type').val();
                            if (type == 'category') {
                                var name = $(this).parents('tr').find('div.cat_name').children('b').text();
                                selected_categories.push(name);
                            }
                            if (type == 'product') {
                                var name = $(this).parents('tr').find('span.prodNameC').text();
                                selected_products.push(name);
                            }
                        });
                    
                        var message = '<div class="">';
                        if (selected_categories.length > 0) {
                            message += '<h3>Selected categories:</h3>';
                            for (var i = 0, len = selected_categories.length; i < len; i++) {
                                message += '<b>' + selected_categories[i] + '</b><br>';
                            }
                        }
                        if (selected_products.length > 0) {
                            message += '<h3>Selected products:</h3>';
                            for (var i = 0, len = selected_products.length; i < len; i++) {
                                message += '<b>' + selected_products[i] + '</b><br>';
                            }
                            message += '<br><label class="control-label">{$smarty.const.TEXT_CHOISE_METHOD} </label><div class=""><label class="radio"><input type="radio" class="uniform" name="copy_to" value="move" checked>{$smarty.const.TEXT_INFO_HEADING_MOVE_PRODUCT}</label><label class="radio"><input type="radio" class="uniform" name="copy_to" value="link">{$smarty.const.TEXT_COPY_AS_LINK}</label><label class="radio"><input type="radio" class="uniform" name="copy_to" value="dublicate">{$smarty.const.TEXT_COPY_AS_DUPLICATE}</label></div><label class="control-label">{$smarty.const.TEXT_COPY_ATTRIBUTES} ({$smarty.const.TEXT_COPY_ATTRIBUTES_ONLY})</label><div class=""><label class="radio"><input type="radio" class="uniform" name="copy_attributes" value="yes" checked>{$smarty.const.TEXT_YES}</label><label class="radio"><input type="radio" class="uniform" name="copy_attributes" value="no">{$smarty.const.TEXT_NO}</label></div>';
                        }
                        message += '</div>';
                        
                        var post_data = getSelectedCatalogItems();
                        
                        bootbox.dialog({
                                message: message,

                                title: "{$smarty.const.TEXT_MOVE_OR_COPY_BACH} " + title,
                                buttons: {
                                        success: {
                                                label: "{$smarty.const.TEXT_YES|escape:'javascript'}",
                                                className: "btn btn-primary",
                                                callback: function() {
                                                    var copy_to = $('input[name="copy_to"]:checked').val();
                                                    var copy_attributes = $('input[name="copy_attributes"]:checked').val();
                                                    var current_category_id = $('#global_id').val();
                                                    
                                                    post_data.push({
                                                        name: 'type', value: 'mixed'
                                                    });
                                                    /*post_data.push({
                                                        name: 'products_id', value: cell_identify
                                                    });*/
                                                    post_data.push({
                                                        name: 'categories_id', value: categories_id
                                                    });
                                                    post_data.push({
                                                        name: 'copy_to', value: copy_to
                                                    });
                                                    post_data.push({
                                                        name: 'copy_attributes', value: copy_attributes
                                                    });
                                                    post_data.push({
                                                        name: 'current_category_id', value: current_category_id
                                                    });
                                                    
                                                    $.post("{Yii::$app->urlManager->createUrl('categories/copy-move')}", post_data, function(data, status){
                                                        if (status == "success") {
                                                            initCategoryTree(data);
                                                            $("#categorysearch").val('');
                                                            resetStatement();
                                                        } else {
                                                            alert("Request error.");
                                                        }
                                                    },"html");
                                                }
                                        },
                                        cancel: {
                                                label: "{$smarty.const.IMAGE_CANCEL|escape:'javascript'}",
                                                className: "btn-cancel",
                                                callback: function() {
                                                        //console.log("Primary button");
                                                }
                                        }
                                }
                        });
                        
                    } else if (cell_type == 'product') {
                        var title = $(obj[0]).children('div.tl-wrap-li-left-cat').children('div.dd3-content').children('span.cat_li').children('span.cat_text').text();
                        var categories_id = $(obj[0]).attr('data-id');
                            bootbox.dialog({
                                message: '<div class=""><label class="control-label">{$smarty.const.TEXT_CHOISE_METHOD} </label><div class=""><label class="radio"><input type="radio" class="uniform" name="copy_to" value="move" checked>{$smarty.const.TEXT_INFO_HEADING_MOVE_PRODUCT}</label><label class="radio"><input type="radio" class="uniform" name="copy_to" value="link">{$smarty.const.TEXT_COPY_AS_LINK}</label><label class="radio"><input type="radio" class="uniform" name="copy_to" value="dublicate">{$smarty.const.TEXT_COPY_AS_DUPLICATE}</label></div><label class="control-label">{$smarty.const.TEXT_COPY_ATTRIBUTES} ({$smarty.const.TEXT_COPY_ATTRIBUTES_ONLY})</label><div class=""><label class="radio"><input type="radio" class="uniform" name="copy_attributes" value="yes" checked>{$smarty.const.TEXT_YES}</label><label class="radio"><input type="radio" class="uniform" name="copy_attributes" value="no">{$smarty.const.TEXT_NO}</label></div></div>',

                                title: "{$smarty.const.TEXT_MOVE_OR_COPY_PRODUCT_TO} " + title,
                                buttons: {
                                        success: {
                                                label: "{$smarty.const.TEXT_YES}",
                                                className: "btn btn-primary",
                                                callback: function() {
                                                    var copy_to = $('input[name="copy_to"]:checked').val();
                                                    var copy_attributes = $('input[name="copy_attributes"]:checked').val();
                                                    var current_category_id = $('#global_id').val();
                                                    $.post("{Yii::$app->urlManager->createUrl('categories/copy-move')}", { 'type' : cell_type, 'products_id' : cell_identify, 'categories_id' : categories_id, 'copy_to' : copy_to, 'copy_attributes' : copy_attributes, 'current_category_id' : current_category_id }, function(data, status){
                                                        if (status == "success") {
                                                            resetStatement();
                                                        } else {
                                                            alert("Request error.");
                                                        }
                                                    },"html");
                                                }
                                        },
                                        cancel: {
                                                label: "{$smarty.const.IMAGE_CANCEL|escape:'javascript'}",
                                                className: "btn-cancel",
                                                callback: function() {
                                                        //console.log("Primary button");
                                                }
                                        }
                                }
                        });
                    } else if (cell_type == 'category') {
                        var parent_id = $(obj[0]).attr('data-id');
                        $.post("{Yii::$app->urlManager->createUrl('categories/copy-move')}", { 'type' : cell_type, 'categories_id' : cell_identify, 'parent_id' : parent_id }, function(data, status){
                            if (status == "success") {
                              initCategoryTree(data);
                              $("#categorysearch").val('');
                            } else {
                                alert("Request error.");
                            }
                        },"html");
                    }
                //}
            
                
                
            } else {
                var obj = $(elem).parents("li.li_block");
                if (obj[0] != undefined) {
                    var cell_identify = $(ui.item[0]).find('.cell_identify').val();
                    var cell_type = $(ui.item[0]).find('.cell_type').val();
                    if (cell_type == 'product') {
                        var brand_id = $(obj[0]).children('span.brand_li').children('span.brand_text').attr('id');
                        //console.log(brand_id);
                        //console.log(cell_identify);
                        $.post("{Yii::$app->urlManager->createUrl('categories/copy-move')}", { 'type' : 'brand', 'products_id' : cell_identify, 'brand_id' : brand_id }, function(data, status){
                            if (status == "success") {
                                resetStatement();
                            } else {
                                alert("Request error.");
                            }
                        },"html");
                    }
                } else {
                    var obj = $(elem).parents("span.cat_li");
                    if (obj[0] != undefined) {
                        
                                        
                var cell_identify = $(ui.item[0]).find('.cell_identify').val();
                var cell_type = $(ui.item[0]).find('.cell_type').val();
                
                    if (cell_type == 'product') {
                        var title = $(obj[0]).children('span').text();
                        var categories_id = $(obj[0]).children('span').attr('id');
                        bootbox.dialog({
                            message: '<div class=""><label class="control-label">{$smarty.const.TEXT_CHOISE_METHOD} </label><div class=""><label class="radio"><input type="radio" class="uniform" name="copy_to" value="move" checked>{$smarty.const.TEXT_INFO_HEADING_MOVE_PRODUCT}</label><label class="radio"><input type="radio" class="uniform" name="copy_to" value="link">{$smarty.const.TEXT_COPY_AS_LINK}</label><label class="radio"><input type="radio" class="uniform" name="copy_to" value="dublicate">{$smarty.const.TEXT_COPY_AS_DUPLICATE}</label></div><label class="control-label">{$smarty.const.TEXT_COPY_ATTRIBUTES} ({$smarty.const.TEXT_COPY_ATTRIBUTES_ONLY})</label><div class=""><label class="radio"><input type="radio" class="uniform" name="copy_attributes" value="yes" checked>{$smarty.const.TEXT_YES}</label><label class="radio"><input type="radio" class="uniform" name="copy_attributes" value="no">{$smarty.const.TEXT_NO}</label></div></div>',

                            title: "{$smarty.const.TEXT_MOVE_OR_COPY_PRODUCT_TO} " + title,
                            buttons: {
                                    success: {
                                            label: "{$smarty.const.TEXT_YES}",
                                            className: "btn btn-primary",
                                            callback: function() {
                                                var copy_to = $('input[name="copy_to"]:checked').val();
                                                var copy_attributes = $('input[name="copy_attributes"]:checked').val();
                                                var current_category_id = $('#global_id').val();
                                                $.post("{Yii::$app->urlManager->createUrl('categories/copy-move')}", { 'type' : cell_type, 'products_id' : cell_identify, 'categories_id' : categories_id, 'copy_to' : copy_to, 'copy_attributes' : copy_attributes, 'current_category_id' : current_category_id }, function(data, status){
                                                    if (status == "success") {
                                                        resetStatement();
                                                    } else {
                                                        alert("Request error.");
                                                    }
                                                },"html");
                                            }
                                    },
                                    cancel: {
                                            label: "{$smarty.const.IMAGE_CANCEL|escape:'javascript'}",
                                            className: "btn-cancel",
                                            callback: function() {
                                                    //console.log("Primary button");
                                            }
                                    }
                            }
                        });
                    } else if (cell_type == 'category') {
                        var parent_id = $(obj[0]).children('span').attr('id');
                        $.post("{Yii::$app->urlManager->createUrl('categories/copy-move')}", { 'type' : cell_type, 'categories_id' : cell_identify, 'parent_id' : parent_id }, function(data, status){
                            if (status == "success") {
                                initCategoryTree(data);
                                $("#categorysearch").val('');
                            } else {
                                alert("Request error.");
                            }
                        },"html");
                    }
                //}
                        
                        
                    }
                }
            }
            return true;
          },
        update: function( event, ui ) {
            var disabled = $(ui.item[0]).find('div.handle_cat_list').hasClass('state-disabled');
            if (disabled == true) {
                bootbox.alert("Sorting disabled for search mode!");
                return false;
            }
            var listing_type = $('#listing_type').val();
            $.post("{Yii::$app->urlManager->createUrl('categories/sort-order')}?listing_type=" + listing_type + "&category_id=" + $('#global_id').val() + "&brand_id=" + $('#brand_id').val(), $(this).sortable('serialize'), function(data, status){
                if (status == "success") {
                    if (listing_type == 'category') {
                      initCategoryTree(data);
                      $("#categorysearch").val('');
                    }
                    resetStatement();
                } else {
                    alert("Request error.");
                }
            },"html");
        },
        cursor: "move",
        cursorAt: { left: -5 } ,
        helper: function( event, element ) {
            var count_items = getSelectedCatalogCount();
            if (count_items > 0) {
                var selected = '';
                $('#categoriesTable .js-cat-batch').not('.js-cat-batch-master').each(function () {
                    if ( !this.checked ) return;
                    var type = $(this).parents('tr').find('input.cell_type').val();
                    if (type == 'category') {
                        var name = $(this).parents('tr').find('div.cat_name').children('b').text();
                        selected += '<b>' + name + '</b><br>';
                    }
                    if (type == 'product') {
                        var name = $(this).parents('tr').find('span.prodNameC').text();
                        selected += name + '<br>';
                    }
                });
                return $( "<div class='ui-widget-header ui-widget-header-cat' style='height: auto;'>"+selected+"</div>" );
            }
            if (element[0] != undefined) {
                return $(element[0]);
            }
            return $( "<div class='ui-widget-header'>Wrong item</div>" );
        },
        /*sort: function( event, ui ) {
            $("li.dd-item").removeClass('dd-hovered');
            var elem = document.elementFromPoint(event.clientX, event.clientY);
            var obj = $(elem).parents("li.dd-item");
            if (obj[0] != undefined) {
                $(obj[0]).addClass('dd-hovered');
            }
        },*/
      handle: ".handle",
      items: "tr:not(.ui-state-disabled)"
    }).disableSelection();
    
    $( ".brand_box ul" ).sortable({
        axis: 'y',
        update: function( event, ui ) {
            //console.log(event);
            //console.log(ui.item);
            //var data = $(this).sortable('serialize');
            //console.log(data);
            $.post("{Yii::$app->urlManager->createUrl('categories/sort-order')}", $(this).sortable('serialize'), function(data, status){
                if (status == "success") {
                    //resetStatement();
                } else {
                    alert("Request error.");
                }
            },"html");
        },
        handle: ".handle"
    }).disableSelection();
 
    $('.edit_cat').popUp({
        box: "<div class='popup-box-wrap'><div class='around-pop-up'></div><div class='popup-box popupEditCat'><div class='pop-up-close'></div><div class='popup-heading cat-head'>Editing category <span class='js-popup-category-name'></span></div><div class='pop-up-content'><div class='preloader'></div></div></div></div>"
    });
    $('.delete_cat').popUp({
        box: "<div class='popup-box-wrap'><div class='around-pop-up'></div><div class='popup-box popupBrandDelete'><div class='pop-up-close'></div><div class='popup-heading cat-head'>Delete category</div><div class='pop-up-content'><div class='preloader'></div></div></div></div>"
    });
    $('.edit_brand').popUp({
        box: "<div class='popup-box-wrap'><div class='around-pop-up'></div><div class='popup-box popupEditCat'><div class='pop-up-close'></div><div class='popup-heading cat-head'>Editing brand <span class='js-popup-brand-name'></span></div><div class='pop-up-content'><div class='preloader'></div></div></div></div>"
    });
    $('.delete_brand').popUp({
        box: "<div class='popup-box-wrap'><div class='around-pop-up'></div><div class='popup-box popupBrandDelete'><div class='pop-up-close'></div><div class='popup-heading cat-head'>Delete brand</div><div class='pop-up-content'><div class='preloader'></div></div></div></div>"
    });



    var color = '#ff0000';

    var chighlight = function(obj, reg){
            if (reg.length == 0) return;
            $(obj).html($(obj).text().replace( new RegExp( "(" +  reg  + ")" , 'gi' ), '<font style="color:'+color+'">$1</font>'));
            return;
    }

    var cunhighlight = function(obj){
            $(obj).html($(obj).text());
    }

    $('#categorysearch').on('keyup', function(e){
      delayKeyUp(function(){
        var str = $('#categorysearch').val();

        $.post("categories/categoryfilter", { 'categorysearch': str, 'collapsed': $("#cat_main_box_switch_collapse .collapse_all").hasClass('switch_active')}, function(data, status){
          if (status == "success") {
            initCategoryTree(data);
          } else {
            alert('Request error.');
          }
        },'html');

      },1000);
    });
    
    var bsearch = null;
    var bstarted = false;
    $('#brandsearch').on('focus keyup', function(e){
        if ($(this).val().length == 0){
                bstarted = false;
        }
        if (!bstarted && e.type == 'focus'){
            // $('.cat_main_box').find('ol').addClass('open').children('ul').show();
            // $('#nav').find('.arrow').removeClass('icon-plus').addClass('icon-minus');
        }
        
        bstarted = true;
        var str = $(this).val();
        bsearch = new RegExp(str, 'i');

        $.each($('.brand_box').find('span.brand_text'), function(i, e){
                cunhighlight(e);
                if (!bsearch.test($(e).text())){
                    //console.log($(e));
                        $(e).parent().parent().hide();
                } else {
                        $(e).parents('ul li').show();
                        //$(e).next().show();
                        chighlight(e, str);
                }
        });
            

    });

   $('.js_create_new_product, .js_create_new_category').on('click',function() {
     if ( $("#listing_type").val()=='category' ) {
       var href = $(this).attr('href');
       var check_url = href.match(/(\?|&)category_id=\d+/);
       if ( check_url ) {
         href = href.replace(check_url[0],check_url[1]+'category_id='+$('#global_id').val());
       }else{
         href += ((href.indexOf('?')===-1)?'?':'&')+'category_id='+$('#global_id').val();
       }
       $(this).attr('href',href);
     }
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

    $('#list_bread_crumb').on('click','.js-category-navigate',function(event){
        changeCategory(event.target);
    });

    $(document).on('focus', 'input.js-sources',function(event){
        var $input = $(event.target);
        if ( $input.hasClass('js-sources') && !$input.hasClass('ui-autocomplete-input') ){
            $input.autocomplete({
                source: "{Yii::$app->urlManager->createUrl(['categories/sources'])}",
                minLength: 0,
                autoFocus: true,
                delay: 200,
                appendTo: $input.parent(),
                select: function( event, ui ) {
                    event.preventDefault();
                    $($input).val(ui.item.value);
                    $($input).trigger('blur');
                }
            }).focus(function () {
                $(this).autocomplete("search");
            });
            $($input).autocomplete().data( "ui-autocomplete" )._renderItem = function( ul, item ) {
                if ( this.term && this.term!='>' ) {
                    item.text = item.text.replace(new RegExp('(' + $.ui.autocomplete.escapeRegex(this.term) + ')', 'gi'), '<b>$1</b>');
                }
                return $( "<li>" )
                    .data("item.autocomplete", item)
                    .append( "<a>" + item.text + "</a>" )
                    .appendTo( ul );
            };
        }
    });

});
</script>                                               

                                <!--===Actions ===-->
        <div class="row right_column" id="catalog_management" style="display: none;">
            <div class="widget box">
                <div class="widget-content" id="catalog_management_data">
                    <div class="scroll_col"></div>
                    <div class="batchCol" style="display: none">
                        <div class="or_box_head">{$smarty.const.TEXT_BATCH_ACTIONS}</div>
                        <div class="after btn-wr-top1 disable-btn js-batch-buttons" style="margin: 4px;">
                            <div>
                                <a href="javascript:void(0)" onclick="deleteSelectedCatalog();" class="btn btn-del btn-no-margin">{$smarty.const.TEXT_DELETE_SELECTED}</a>
                                <a href="javascript:void(0)" onclick="switchCatalogState(1);" class="btn btn-on-sel">{$smarty.const.TEXT_ON_SELECTED}</a>
                                <a href="javascript:void(0)" onclick="switchCatalogState(0);" class="btn btn-off-sel">{$smarty.const.TEXT_OFF_SELECTED}</a>
                                <a href="javascript:void(0)" onclick="batchMove();" class="btn btn-move">{$smarty.const.IMAGE_MOVE}</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
</div>
</div>
</div>
				<!--===Actions ===-->
				<!-- /Page Content -->