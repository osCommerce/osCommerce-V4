{*
This file is part of osCommerce ecommerce platform.
 * osCommerce the ecommerce

@link https://www.oscommerce.com
@copyright Copyright (c) 2000-2022 osCommerce LTD

Released under the GNU General Public License
 * For the full copyright and license information, please view the LICENSE.TXT file that was distributed with this source code.
*}
{use class="common\helpers\Html"}
{\backend\assets\BDTPAsset::register($this)|void}

﻿<div class="featuredTable">
  {Html::beginForm(['featured/submit'], 'post', ['name' => "product_edit", 'id' => "save_item_form", 'onsubmit' => "return saveItem();"])}
  {Html::hiddenInput('products_id', $pInfo->products_id)}
  {Html::hiddenInput('item_id', $sInfo->featured_id|default:null)}
  <table cellspacing="0" cellpadding="0" width="100%">
    <tr><td class="label_name">{$smarty.const.TEXT_SPECIALS_PRODUCT}</td><td class="label_value_in">
        <a target="blank" href="{Yii::$app->urlManager->createUrl(['categories/productedit', 'pID' => $pInfo->products_id])}">{$backendProductDescription.products_name}</a>
        </div>
      </td></tr>
    <tr><td colspan="2" class="">
        <div class="js_featured col-md-6">
          <div class="after our-pr-line-check-box ">
            <div class="col-md-6">
              <label for="inactive">{$smarty.const.TEXT_FEATURED_TYPES}:</label>
              {Html::dropDownList('featured_type_id', $sInfo->featured_type_id|default:null, $featured_types, ['class'=>"form-control form-control-med"])}
            </div>
            <div class="col-md-6">
              <label for="status" accesskey="s">{$smarty.const.TABLE_HEADING_STATUS}</label>
              {Html::checkbox('status', $sInfo->status|default:null, ['class'=>'check_on_off', 'value' => 1])}
            </div>
          </div>
          <div>
            <div class="_sale-prod-line">
              <div class="col-md-6">
                <label for="startDate" accesskey="d">{$smarty.const.TEXT_START_DATE}</label>
                {Html::textInput('start_date', \common\helpers\Date::datepicker_date($sInfo->start_date|default:null), ['class' => "datetimepicker form-control form-control-small"])}
              </div>
              <div class="col-md-6">
                <label for="expiresDate" accesskey="e">{$smarty.const.TEXT_SPECIALS_EXPIRES_DATE}</label>
                {Html::textInput('expires_date', \common\helpers\Date::datepicker_date($sInfo->expires_date|default:null), ['class' => "datetimepicker form-control form-control-small"])}
              </div>
            </div>
          </div>
        </div>
      </td></tr>
  </table>
  <div class="btn-bar">
    <div class="btn-left"><a class="btn btn-cancel" href="{$back_url}">{$smarty.const.IMAGE_CANCEL}</a></div>
    <div class="btn-right"><input class="btn btn-confirm" type="submit" value="{$smarty.const.IMAGE_SAVE}"></div>
  </div>

  {Html::endForm()}
</div>
<script>
  $(document).ready(function () {
    $(".check_on_off").bootstrapSwitch({
      onText: "{$smarty.const.SW_ON}",
      offText: "{$smarty.const.SW_OFF}",
      handleWidth: '20px',
      labelWidth: '24px'
    });

    $('#startDate').datetimepicker({
      format: 'DD MMM YYYY h:mm A'
    });
    $('#expiresDate').datetimepicker({
      format: 'DD MMM YYYY h:mm A',
      useCurrent: false
    });
    $('#startDate').on("dp.change", function (e) {
      $('#startDate').data("DateTimePicker").minDate(e.date);
    });
    $('#expiresDate').on("dp.change", function (e) {
      $('#startDate').data("DateTimePicker").maxDate(e.date);
    });

  })

  function saveItem() {
    $.post("featured/submit", $('#save_item_form').serialize(), function (data, status) {
      if (status == "success") {
        var msg = "{$smarty.const.MESSAGE_SAVED|escape:'html'}";
        if (data.result != 1) {
          msg = '<div class="alert alert-danger">' + data.message + '</div>';
        } else {
          $('#itemId').val(data.item_id);
        }
        bootbox.alert({
          title: "{$smarty.const.BOX_CATALOG_FEATURED|escape:'html'}",
          message: msg,
          size: 'small',
        });
      } else {
        alert("Request error.");
      }
    }, "json");

    return false;
  }
</script>


