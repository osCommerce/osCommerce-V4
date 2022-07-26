{use class="yii\helpers\Html"}
<div class="page-header">
  <div class="page-title">
    <h3>{$app->controller->view->headingTitle}</h3>
  </div>
</div>
<form action="{Yii::$app->urlManager->createUrl('products-groups/products-update')}" method="post" id="products_groups_products" name="products_groups_products">
{Html::hiddenInput('products_groups_id', $eInfo->products_groups_id)}
<div class="xl-pr-box" id="box-xl-pr">
  <div class="after">
    <div class="attr-box attr-box-1">
      <div class="widget widget-attr-box box box-no-shadow" style="margin-bottom: 0;">
        <div class="widget-header">
          <h4>{$smarty.const.FIND_PRODUCTS}</h4>
          <div class="box-head-serch after">
            <input type="search" id="products-group-search-by-products" placeholder="{$smarty.const.SEARCH_BY_ATTR}" class="form-control">
            <button onclick="return false"></button>
          </div>
        </div>
        <div class="widget-content">
          <select id="products-group-search-products" size="25" style="width: 100%; height: 100%; border: none;" ondblclick="addSelectedGroup()">
          </select>
        </div>
      </div>
    </div>
    <div class="attr-box attr-box-2">
      <span class="btn btn-primary" onclick="addSelectedGroup()"></span>
    </div>
    <div class="attr-box attr-box-3">
      <div class="widget-new widget-attr-box box box-no-shadow" style="margin-bottom: 0;">
        <div class="widget-header">
          <h4>{$smarty.const.FIELDSET_ASSIGNED_PRODUCTS}</h4>
          <div class="box-head-serch after">
            <input type="search" id="search-products-groups-assigned" placeholder="{$smarty.const.SEARCH_BY_ATTR}" class="form-control">
            <button onclick="return false"></button>
          </div>
        </div>
        <div class="widget-content">
          <table class="table assig-attr-sub-table products-group-products">
            <thead>
            <tr role="row">
              <th></th>
              <th>{$smarty.const.TEXT_IMG}</th>
              <th>{$smarty.const.TEXT_LABEL_NAME}</th>
              <th></th>
            </tr>
            </thead>
            <tbody id="products-groups-assigned">
            {foreach $app->controller->view->groupProducts as $eKey => $group_product}
              {include file="new-product.tpl" group_product=$group_product}
            {/foreach}
            </tbody>
          </table>
          <input type="hidden" value="" name="group_sort_order" id="group_sort_order"/>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="btn-bar">
  <div class="btn-left"><a href="{Yii::$app->urlManager->createUrl(['products-groups/index', 'eID' => $eInfo->products_groups_id])}" class="btn btn-cancel-foot">Cancel</a></div>
  <div class="btn-right"><button class="btn btn-primary">Save</button></div>
</div>
</form>
 
<script type="text/javascript">
  function addSelectedGroup() {
    $( 'select#products-group-search-products option:selected' ).each(function() {
      var products_id = $(this).val();
      if ( $('input[name="products_group_products_id[]"][value="' + products_id + '"]').length ) {
        //already exist
      } else {
        $.post("{Yii::$app->urlManager->createUrl('products-groups/new-product')}", { 'products_id': products_id }, function(data, status) {
          if (status == "success") {
            $( ".products-group-products tbody" ).append(data);
          } else {
            alert("Request error.");
          }
        },"html");
      }
    });

    return false;
  }

  function deleteSelectedGroup(obj) {
    $(obj).parent().remove();
    return false;
  }

  var color = '#ff0000';
  var phighlight = function(obj, reg){
    if (reg.length == 0) return;
    $(obj).html($(obj).text().replace( new RegExp( "(" +  reg  + ")" , 'gi' ), '<font style="color:'+color+'">$1</font>'));
    return;
  }

  var searchHighlightExisting = function(e){
    var $rows = $(e.data.rows_selector);
    var search_term = $(this).val();
    $rows.each(function(){
      var $row = $(this);
      var $value_text = $row.find(e.data.text_selector);
      var search_match = true;

      if ( !$row.data('raw-value') ) $row.data('raw-value', $value_text.html());
      var prop_value = $row.data('raw-value');
      if ( search_term.length>0 ) {
        var searchRe = new RegExp(".*" + (search_term + "").replace(/([.?*+\^\$\[\]\\(){}|-])/g, "\\$1") + ".*", 'i');
        if (searchRe.test(prop_value)) {
          phighlight($value_text, search_term);
        } else {
          $value_text.html(prop_value);
          search_match = false;
        }
      }else{
        $value_text.html(prop_value);
      }

      if ( search_match ) {
        $row.show();
      }else{
        $row.hide();
      }
    });
  }

  $(document).ready(function() {
    $('#search-products-groups-assigned').on('focus keyup', { rows_selector: '#products-groups-assigned tr', text_selector: '.ast-name-group-product'}, searchHighlightExisting);

    $('#products-group-search-by-products').on('focus keyup', function(e) {
      var str = $(this).val();
      $.post( "{Yii::$app->urlManager->createUrl('products-groups/product-search')}?q="+encodeURIComponent(str), function( data ) {
        $( "select#products-group-search-products" ).html( data );
        psearch = new RegExp(str, 'i');
        $.each($('select#products-group-search-products').find('option'), function(i, e){
          if (psearch.test($(e).text())){
            phighlight(e, str);
          }
        });
      });
    }).keyup();

    $( ".products-group-products tbody" ).sortable({
      handle: ".sort-pointer",
      axis: 'y',
      update: function( event, ui ) {
        var data = $(this).sortable('serialize', { attribute: "prefix" });
        $("#products_group_sort_order").val(data);
      },
    }).disableSelection();

  });
</script>