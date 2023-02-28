
<div class="after">
  <div class="prop-box prop-box-1">
    <div class="widget box box-no-shadow" style="margin-bottom: 0;">
      <div class="widget-header">
        <h4>{$smarty.const.TEXT_PROP}</h4>
        <div class="box-head-serch after">
          <input type="search" id="search-by-properties" placeholder="{$smarty.const.TEXT_SEARCH_PROP}" class="form-control">
          <button onclick="return false;"></button>
        </div>
      </div>
      <div class="widget-content">
        <select size="22" name="properties" id="properties_box" class="attr-tree" style="width: 100%; height: 100%; border: none;">
          {include file="../../properties/properties_box.tpl"}
        </select>
        <div class="w-btn-list w-btn-add-prop">
          <a href="{Yii::$app->urlManager->createUrl('properties/category')}" class="add_properties_group btn" title="{$smarty.const.TEXT_ADD_NEW_GROUP}">{$smarty.const.TEXT_ADD_NEW_GROUP}</a><a href="{Yii::$app->urlManager->createUrl('properties/edit')}" class="add_property btn" title="{$smarty.const.TEXT_ADD_NEW_PROP}">{$smarty.const.TEXT_ADD_NEW_PROP}</a>
        </div>
      </div>
    </div>
  </div>
  <div class="prop-box prop-box-4">
    <div class="widget-new box box-no-shadow" style="margin-bottom: 0;">
      <div class="widget-header">
        <h4>{$smarty.const.TEXT_PROP_VAL}</h4>
      </div>
      <div class="widget-content">
        <div id="properties_values_box">
          &nbsp;
        </div>
      </div>
    </div>
  </div>
  <div class="prop-box prop-box-2">
    <span class="btn btn-primary" onclick="addPropertyValues()"></span>
  </div>
  <div class="prop-box prop-box-3">
    <div class="widget-new widget-attr-box box box-no-shadow" style="margin-bottom: 0;">
      <div class="widget-header">
        <h4>{$smarty.const.TEXT_ASSIGNED_PROP}</h4>
        <div class="box-head-serch after">
          <input type="search" placeholder="{$smarty.const.TEXT_SEARCH_ASSI_PROP}" class="form-control">
          <button onclick="return false;"></button>
        </div>
      </div>
      <div class="widget-content" id="selected_properties_box">
        {include file="property-values-selected.tpl" properties_hiddens=$app->controller->view->properties_hiddens properties_tree_array=$app->controller->view->properties_tree_array}
      </div>
    </div>
  </div>
</div>

<div id="categories_properties_management_data" style="display: none;"></div>
<script type="text/javascript">
  function saveProperty() {
    $.post("{Yii::$app->urlManager->createUrl('properties/save')}", $('#property_edit').serialize(), function (data, status) {
      if (status == "success") {
        $('.popup-box:last').trigger('popup.close');
        $('.popup-box-wrap:last').remove();
        $('#properties_box').html(data);
      } else {
        alert("Request error.");
      }
    }, "html");
    return false;
  }

  function selectProperty() {
    properties_id = $('select[name="properties"]').val();
    if (properties_id > 0) {
      $.post("{Yii::$app->urlManager->createUrl('categories/property-values')}", { 'properties_id' : properties_id }, function(data, status) {
        if (status == "success") {
          $( "#properties_values_box" ).html(data);
        } else {
          alert("Request error.");
        }
      },"html");
    }
    return false;
  }

  function addPropertyValues() {
    var properties_counter = 0;
    var properties_array = [];
    var values_array = [];
    var extra_values = [];
    var properties_id = $('select[name="properties"]').val();
    if (properties_id > 0) {
      properties_array[properties_counter] = properties_id;
      values_array[properties_counter] = [];
      $('input[name="values[]"]').each(function () {
        if ($(this).prop('checked')) {
          values_array[properties_counter].push($(this).val());
        } else if ($(this).prop('type') != 'radio')  {
          values_array[properties_counter].push(0);
        }
      });
      ///ln89 properties_array[properties_counter] = properties_id;
      extra_values[properties_counter] = [];
      $('input[name="extra_values[]"]').each(function () {
        extra_values[properties_counter].push($(this).val());
      });
      
      
      if (values_array[properties_counter].length > 0) {
        $('input[name="prop_ids[]"]').each(function () {
          properties_id = $(this).val();
          if (!in_array(properties_id, properties_array)) {
            properties_counter++;
            properties_array[properties_counter] = properties_id;
            values_array[properties_counter] = [];
            $('input[name="val_ids[' + properties_id + '][]"]').each(function () {
              values_array[properties_counter].push($(this).val());
            });
            extra_values[properties_counter] = [];
            $('input[name="val_extra[' + properties_id + '][]"]').each(function () {
              extra_values[properties_counter].push($(this).val());
            });
          }
        });
        $.post("{Yii::$app->urlManager->createUrl('categories/update-property-values')}", { 'properties_array' : properties_array, 'values_array' : values_array, 'extra_values' : extra_values }, function(data, status) {
          if (status == "success") {
            $( "#selected_properties_box" ).html(data);
          } else {
            alert("Request error.");
          }
        },"html");
      }
    }
  }

  function delPropertyValue(prop_id, val_id) {
    var properties_counter = 0;
    var properties_array = [];
    var values_array = [];
    var extra_values = [];
    if (prop_id > 0) {
      $('input[name="prop_ids[]"]').each(function () {
        properties_id = $(this).val();
        if (!in_array(properties_id, properties_array)) {
          properties_array[properties_counter] = properties_id;
          values_array[properties_counter] = [];
          $('input[name="val_ids[' + properties_id + '][]"]').each(function () {
            if (properties_id != prop_id || $(this).val() != val_id) {
              values_array[properties_counter].push($(this).val());
            }
          });
          extra_values[properties_counter] = [];
          $('input[name="val_extra[' + properties_id + '][]"]').each(function () {
            extra_values[properties_counter].push($(this).val());
          });
          if (values_array[properties_counter].length > 0) {
            properties_counter++;
          } else {
            properties_array.splice(properties_counter, 1);
            values_array.splice(properties_counter, 1);
          }
          
        }
      });
      $.post("{Yii::$app->urlManager->createUrl('categories/update-property-values')}", { 'properties_array' : properties_array, 'values_array' : values_array, 'extra_values' : extra_values }, function(data, status) {
        if (status == "success") {
          $( "#selected_properties_box" ).html(data);
        } else {
          alert("Request error.");
        }
      },"html");
    }
  }

  function in_array(value, array) {
    for(var i = 0; i < array.length; i++)  {
      if(array[i] == value) return true;
    }
    return false;
  }

  /*
   function deleteSelectedProperty(obj) {
   var optionBox = $(obj).parent().parent().parent().parent().parent();
   $(obj).parent().parent().parent().parent().remove();
   var findtr = $(optionBox).find('div.widget-content2');
   if (findtr[0] == undefined) {
   $(optionBox).remove();
   }
   return false;
   }
   */

  var color = '#ff0000';
  var propthighlight = function(obj, reg){
    if (reg.length == 0) return;
    $(obj).html($(obj).text().replace( new RegExp( "(" +  reg  + ")" , 'gi' ), '<font style="color:'+color+'">$1</font>'));
    return;
  }
  var propunhighlight = function(obj){
    $(obj).html($(obj).text());
  }
  var propsearch = null;
  var propstarted = false;
  $(document).ready(function() {
    $('#search-by-properties').on('focus keyup', function(e){
      $('select[name="properties"]').find('option').parent().hide();
      if ($(this).val().length == 0){
        atstarted = false;
      }
      if (!propstarted && e.type == 'focus'){
        $('select[name="properties"]').find('option').show();
        $('select[name="properties"]').find('option').parent().show();
      }
      propstarted = true;
      var str = $(this).val();
      propsearch = new RegExp(str, 'i');
      $.each($('select[name="properties"]').find('option'), function(i, e){
        propunhighlight(e);
        if (!propsearch.test($(e).text())){
          $(e).hide();
        } else {
          $(e).show();
          $(e).parent().show();
          propthighlight(e, str);
        }
      });
    });

    $('.add_property').popUp({
      box: "<div class='popup-box-wrap'><div class='around-pop-up'></div><div class='popup-box popup-properties'><div class='pop-up-close pop-up-close-alert'></div><div class='popup-heading cat-head'>{$smarty.const.TEXT_ADD_NEW_PROP}</div><div class='pop-up-content'><div class='preloader'></div></div></div></div>"
    });

    $('.add_properties_group').popUp({
      box: "<div class='popup-box-wrap'><div class='around-pop-up'></div><div class='popup-box popup-properties'><div class='pop-up-close pop-up-close-alert'></div><div class='popup-heading cat-head'>{$smarty.const.TEXT_ADD_NEW_GROUP}</div><div class='pop-up-content'><div class='preloader'></div></div></div></div>"
    });

    $('select#properties_box').change(function(){
      selectProperty();
    });

  });
</script>