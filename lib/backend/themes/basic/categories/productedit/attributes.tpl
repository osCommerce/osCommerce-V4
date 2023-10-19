
<div class="btn-box-inv-price after">
    <span class="full-attr-price dis_module" data-value="1">{$smarty.const.TEXT_FULL_PRICE}</span>
    <span class="add-attr-price active" data-value="0">{$smarty.const.TEXT_ADDITIONAL_PRICE}</span>
</div>

<input type="hidden" name="products_price_full" id="full_add_price" value="0"/>

{*Doesn't work*}
{*<div class="product-attribute-setting" {if !($app->controller->view->selectedAttributes|default:null) || $pInfo->is_bundle} style="display:none;"{/if}>*}
{*    <span>{$smarty.const.TEXT_ATTRIBUTES_W_QUANTITY}</span>*}
{*    {\yii\helpers\Html::checkbox('show_attributes_quantity', $pInfo->settings->show_attributes_quantity, ['value' => 1, 'class' => "check_bot_switch_on_off"])}*}
{*</div>*}

<div class="attr-box-wrap after">
  <div class="attr-box attr-box-1">
    <div class="widget widget-attr-box box box-no-shadow" style="margin-bottom: 0;">
      <div class="widget-header">
        <h4>{$smarty.const.TAB_ATTRIBUTES}</h4>
        <div class="box-head-serch after mb-2">
          <input type="search" value="" id="search-by-attributes" placeholder="{$smarty.const.TAB_SEARCH_ATTR}" class="form-control" />
          <button onclick="return false"></button>
        </div>
      </div>
      <div class="widget-content">
        <select class="attr-tree" size="25" name="attributes" ondblclick="addSelectedAttribute()" style="width: 100%; height: 100%; border: none;" multiple="multiple">
            {if $app->controller->view->attributeTemplates['options']}
              <optgroup label="{$app->controller->view->attributeTemplates['label']}" id="tpl">
                  {foreach $app->controller->view->attributeTemplates['options'] as $option}
                    <option value="{$option['value']}">{$option['name']}</option>
                  {/foreach}
              </optgroup>
            {/if}
          {foreach $app->controller->view->attributes as $optgroup}
            <optgroup label="{$optgroup['label']}" id="{$optgroup['id']}">
              {foreach $optgroup['options'] as $option}
                <option value="{$option['value']}">{$option['name']}</option>
              {/foreach}
            </optgroup>
          {/foreach}
        </select>
      </div>
    </div>
  </div>
  <div class="attr-box attr-box-2">
    <span class="btn btn-primary" onclick="addSelectedAttribute()"></span>
  </div>
  <div class="attr-box attr-box-3">
    <div class="widget-new widget-attr-box box box-no-shadow" style="margin-bottom: 0;">
      <div class="widget-header">
        <h4>{$smarty.const.TEXT_ASSIGNED_ATTR}</h4>
        <div class="box-head-serch after mb-2">
          <input type="search" placeholder="{$smarty.const.TEXT_SEARCH_ASSIGNED_ATTR}" class="form-control" id="attribute_search"/>
          <button onclick="return false"></button>
        </div>
      </div>
      <div class="widget-content" id="selected_attributes_box">
{$attributes=$app->controller->view->selectedAttributes|default:null}
{$products_id={$pInfo->products_id} }
{include file='../product-new-option.tpl'}
      </div>
    </div>
  </div>
</div>
{if $app->controller->view->showInventory == true}
  <div class="widget box box-no-shadow inventory-box" style="margin-bottom: 0;">
    <div class="widget-header"><h4>{$smarty.const.BOX_CATALOG_INVENTORY}</h4></div>
    {if \common\helpers\Extensions::isAllowed('Inventory')}
    <div class="widget-content widget-inv" id="product-inventory-box">
    {else}
    <div class="widget-content widget-inv dis_module" id="product-inventory-box">
    {/if}    
    </div>
  </div>
{/if}

<script type="text/javascript">
  var ajax_attribute_add_in_progress = 0;
  function addSelectedAttribute() {
    var new_products_options_ids = '', assigned_products_options_ids = '', products_options_values_ids = ''; // pass comma separated ids
    var template_apply = [];

    $( 'select[name="attributes"] option:selected' ).each(function() {
      var $opt_group = $(this).parent();
      var products_options_id = $opt_group.attr('id');
      var products_options_values_id = $(this).val();
      if ( products_options_id=='tpl' ) {
        template_apply[0] = products_options_values_id;
        return;
      }
      if ( $( '#products_attributes_id_'+products_options_id+'_'+products_options_values_id ).length ) {
        //already exist
      } else {
        products_options_values_ids += products_options_values_id + ',';
        if ($( "#attr-option-"+products_options_id ).length ) {
          assigned_products_options_ids += products_options_id + ',';
        } else {
          new_products_options_ids += products_options_id + ',';
        }
      }
    });

      if ( template_apply.length>0 ) {
          ajax_attribute_add_in_progress += 1;
          bootbox.confirm('{$smarty.const.TEXT_CONFIRM_APPLY_OPTION_TEMPLATE|escape:'javascript'} ',function(result){
              if ( result ){
                  $.post(
                      "{Yii::$app->urlManager->createUrl('categories/selected-attributes')}",
                      { 'products_id': '{$pInfo->products_id}', 'options_templates_id':template_apply[0], 'products_tax_class_id':$('input[name="products_tax_class_id"]').val(), 'without_inventory': $('#productInventorySwitch').val() },
                      function(data, status, xhr, dataType) {
                          if (status == "success") {
                              $('#selected_attributes_box').html(data);
                              ajax_attribute_add_in_progress -= 1;
                              updateInventoryBox();
                          } else {
                              alert("Request error.");
                          }
                      });
              }
          });

          assigned_products_options_ids = '';
          new_products_options_ids = '';
      }

    if (assigned_products_options_ids != '') {
      ajax_attribute_add_in_progress += 1;
      $.post(
        "{Yii::$app->urlManager->createUrl('categories/product-new-attribute')}",
        { 'products_id': '{$pInfo->products_id}', 'products_options_id' : assigned_products_options_ids, 'products_options_values_id' : products_options_values_ids, 'without_inventory': $('#productInventorySwitch').val() },
        function(dataJ, status, xhr, dataType) {
            if (status == "success") {
              for (i=0;i<dataJ.length; i++) {
                products_options = dataJ[i]['products_options_id'];
                products_options_values = dataJ[i]['products_options_values_id'];
                data = dataJ[i]['data'];
                var $target_tbody = $(".attr-option-"+products_options+" tbody");
                var insert_order_locate = ',';
                $target_tbody.find('input[name^="products_attributes_id\['+products_options+'\]"]').each(function(){
                  var val_id_match = this.name.match(/products_attributes_id\[\d+\]\[(\d+)\]/);
                  if ( val_id_match ) {
                    insert_order_locate = insert_order_locate + val_id_match[1] + ',';
                  }
                });
                var after_val_id = '', before_val_id = '', id_pass = false;
                $('select[name="attributes"] optgroup#' + products_options + ' option').each(function(){
                  if ( before_val_id ) return;
                  if ( this.value==products_options_values ) {
                    id_pass = true;
                  }else
                  if ( insert_order_locate.indexOf(','+this.value+',')!=-1 ){
                    if ( id_pass ) {
                      before_val_id = this.value;
                    }else{
                      after_val_id = this.value;
                    }
                  }
                });
                if ( after_val_id ) {
                  $target_tbody.find('input[name^="products_attributes_id\['+products_options+'\]\['+after_val_id+'\]"]').parents('tr[role="row"]').after(data);
                }else if( before_val_id ) {
                  $target_tbody.find('input[name^="products_attributes_id\['+products_options+'\]\['+before_val_id+'\]"]').parents('tr[role="row"]').before(data);
                }else {
                  $target_tbody.append(data);
                }
                if ( $(".attr-option-"+products_options+" tbody").find('.js-option-value').length>1 ) {
                  $(".attr-option-"+products_options+" tbody").sortable('enable');
                }
              }
              ajax_attribute_add_in_progress -= 1;
              {if \common\helpers\Extensions::isAllowed('Inventory')}
              //inventory or not
                updateInventoryBox();
              {else}
                //or not
                if (ajax_attribute_add_in_progress == 0 ){
                  $('ul[id^="invPrice"] [data-bs-toggle="tab"]').off('shown.bs.tab').on('shown.bs.tab', invPriceTabsShown);
                  $('ul[id^="attr_popup"] [data-bs-toggle="tab"]').off('shown.bs.tab').on('shown.bs.tab', invPriceTabsShown);
                  $('.js_inventory_group_price input.price-options').off('click').on('click', priceOptionsClick);
                }
              {/if}
            } else {
              alert("Request error.");
            }
        },"json");
    }
          //group not exist product-new-option
    if (new_products_options_ids != '') {
      ajax_attribute_add_in_progress += 1;
      $.post(
        "{Yii::$app->urlManager->createUrl('categories/product-new-option')}",
        { 'products_id': '{$pInfo->products_id}', 'products_options_id' : new_products_options_ids, 'products_options_values_id' : products_options_values_ids, 'without_inventory': $('#productInventorySwitch').val() },
        function(dataJ, status, xhr, dataType) {
            if (status == "success") {
              for (i=0;i<dataJ.length; i++) {
                products_options = dataJ[i]['products_options_id'];
                data = dataJ[i]['data'];
                var insert_order_locate = ',';
                var $added_options = $("#selected_attributes_box .js-option");
                $added_options.each(function(){
                  insert_order_locate = insert_order_locate + $(this).attr('data-option_id')+',';
                });
                var after_opt_id = '', before_opt_id = '', id_pass = false;
                $('select[name="attributes"] optgroup').each(function() {
                  if ( before_opt_id ) return;
                  if ( this.id==products_options ) {
                    id_pass = true;
                  }else
                  if ( insert_order_locate.indexOf(','+this.id+',')!=-1 ){
                    if ( id_pass ) {
                      before_opt_id = this.id;
                    }else{
                      after_opt_id = this.id;
                    }
                  }
                });
                if ( after_opt_id ) {
                  $added_options.filter('[data-option_id="'+after_opt_id+'"]').after(data);
                }else if( before_opt_id ) {
                  $added_options.filter('[data-option_id="'+before_opt_id+'"]').before(data);
                }else {
                  $("#selected_attributes_box").append(data);
                }
                if ( $(".attr-option-"+products_options+" tbody").find('.js-option-value').length>1 ) {
                  $(".attr-option-"+products_options+" tbody").sortable('enable');
                }else{
                    if ($(".attr-option-"+products_options+" tbody").data('uiSortable')) {
                        $(".attr-option-"+products_options+" tbody").sortable('disable');
                    }
                }
              }
              ajax_attribute_add_in_progress -= 1;
              {if \common\helpers\Extensions::isAllowed('Inventory')}
             //inventory or not
               updateInventoryBox();
             {else}
               //or not
               if (ajax_attribute_add_in_progress == 0 ){
                 $('ul[id^="invPrice"] [data-bs-toggle="tab"]').off('shown.bs.tab').on('shown.bs.tab', invPriceTabsShown);
                 $('ul[id^="attr_popup"][data-bs-toggle="tab"]').off('shown.bs.tab').on('shown.bs.tab', invPriceTabsShown);
                 $('.js_inventory_group_price input.price-options').off('click').on('click', priceOptionsClick);
               }
             {/if}
            } else {
              alert("Request error.");
            }
      },"json");
      }
    $(".product-attribute-setting").show();
    return false;
  }

  function deleteSelectedAttribute(obj) {
    var optionBox = $(obj).parent().parent();
    var option_value_id = $(obj).parents('.js-option-value').attr('data-option_value_id');;
    var option_id = $(obj).parents('.js-option').attr('data-option_id');;
    $(obj).parent().remove();
    var $sort_input = $('input[name="products_option_values_sort_order['+option_id+']"]');
    if ($sort_input.length>0) $sort_input.val($sort_input.val().replace(','+option_value_id+',',','));
    var findtr = $(optionBox).find('tr');
    if (findtr[0] == undefined) {
      $(optionBox).parent().parent().parent().remove();
    }
    if ( $(".attr-option-"+option_id +" tbody").find('.js-option-value').length==1 ) {
      $(".attr-option-"+option_id +" tbody").sortable('disable');
    }
    {if \common\helpers\Extensions::isAllowed('Inventory')}
      updateInventoryBox();
    {/if}
    return false;
  }

  function updateInventoryBox() {
    if (ajax_attribute_add_in_progress != 0 ) return; // not all ajax requests are finished - inventory not allowed
{if \common\helpers\Extensions::isAllowed('Inventory')}
      if ( $('#productInventorySwitch').val()!='1' ) {
          ///inventory allowed
          //VL2check18 - no function found?? $('#save_product_form').trigger('attributes_changed');
          $.post("{Yii::$app->urlManager->createUrl('categories/product-inventory-box')}", $('#save_product_form').serialize(), function (data, status) {
              if (status == "success") {
                  $("#product-inventory-box").html(data);
                  $('#save_product_form').trigger('inventory_arrived');
              } else {
                  alert("Request error.");
              }
          }, "html");

          if ($("table[class^='attr-option-'],table[class*=' attr-option-']").length <= 1) {
              $('.one-attribute').show();
              $('.more-attributes').hide();
              $('.inventory-box').css({
                  'height': 0, 'overflow': 'hidden'
              });
          } else {
              $('.one-attribute').hide();
              $('.more-attributes').show();
              $('.inventory-box').css({
                  'height': '', 'overflow': ''
              });
          }
      }else{
          $('ul[id^="invPrice"] [data-bs-toggle="tab"]').off('shown.bs.tab').on('shown.bs.tab', invPriceTabsShown);
          $('ul[id^="attr_popup"] [data-bs-toggle="tab"]').off('shown.bs.tab').on('shown.bs.tab', invPriceTabsShown);
      }
{else}
        //or not
        if (ajax_attribute_add_in_progress == 0 ){
          $('ul[id^="invPrice"] [data-bs-toggle="tab"]').off('shown.bs.tab').on('shown.bs.tab', invPriceTabsShown);
          $('ul[id^="attr_popup"] [data-bs-toggle="tab"]').off('shown.bs.tab').on('shown.bs.tab', invPriceTabsShown);
        }
{/if}
  }

  var color = '#ff0000';
  var athighlight = function(obj, reg){
    if (reg.length == 0) return;
    $(obj).html($(obj).text().replace( new RegExp( "(" +  reg  + ")" , 'gi' ), '<font style="color:'+color+'">$1</font>'));
    return;
  }
  var atunhighlight = function(obj){
    $(obj).html($(obj).text());
  }
  var atsearch = null;
  var atstarted = false;
  $(document).ready(function() {
    $('#search-by-attributes').on('focus keyup', function(e){
      $('select[name="attributes"]').find('option').parent().hide();
      if ($(this).val().length == 0){
        atstarted = false;
      }
      if (!atstarted && e.type == 'focus'){
        $('select[name="attributes"]').find('option').show();
        $('select[name="attributes"]').find('option').parent().show();
      }
      atstarted = true;
      var str = $(this).val();
      atsearch = new RegExp(str, 'i');
      $.each($('select[name="attributes"]').find('option'), function(i, e){
        atunhighlight(e);
        if (!atsearch.test($(e).text())){
          $(e).hide();
        } else {
          $(e).show();
          $(e).parent().show();
          athighlight(e, str);
        }
      });
    });

    {if !$hideenInventory|default:null && $app->controller->view->showInventory == true}
    updateInventoryBox();
    {/if}
  });
</script>