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
﻿
<div class="featuredTable">
    {Html::beginForm(['featured/submit'], 'post', ['name' => "product_edit", 'id' => "save_item_form", 'onsubmit' => "return saveItem();"])}
    {Html::hiddenInput('products_id', $pInfo->products_id)}
    {Html::hiddenInput('item_id', $sInfo->featured_id|default:null)}

    <div class="row mb-3">
        <label class="col-4 text-end">
            {$smarty.const.TEXT_SPECIALS_PRODUCT}:
        </label>
        <div class="col-8">
            <a target="blank" href="{Yii::$app->urlManager->createUrl(['categories/productedit', 'pID' => $pInfo->products_id])}">{$backendProductDescription.products_name}</a>
        </div>
    </div>
    <div class="row mb-3">
        <label class="col-4 text-end pt-1">
            {$smarty.const.TEXT_FEATURED_TYPES}:
        </label>
        <div class="col-8">
            {Html::dropDownList('featured_type_id', $sInfo->featured_type_id|default:null, $featured_types, ['class'=>"form-control form-control-med"])}
        </div>
    </div>
    <div class="row mb-3">
        <label class="col-4 text-end pt-1">
            {$smarty.const.TABLE_HEADING_STATUS}:
        </label>
        <div class="col-8">
            {Html::checkbox('status', $sInfo->status|default:null, ['class'=>'check_on_off', 'value' => 1])}
        </div>
    </div>
    <div class="row mb-3">
        <label class="col-4 text-end pt-1">
            {$smarty.const.TEXT_START_DATE}:
        </label>
        <div class="col-8">
            {Html::textInput('start_date', \common\helpers\Date::datepicker_date($sInfo->start_date|default:null), ['class' => "datetimepicker form-control form-control-small", 'style' => 'width: 200px'])}
        </div>
    </div>
    <div class="row mb-3">
        <label class="col-4 text-end pt-1">
            {$smarty.const.TEXT_SPECIALS_EXPIRES_DATE}:
        </label>
        <div class="col-8">
            {Html::textInput('expires_date', \common\helpers\Date::datepicker_date($sInfo->expires_date|default:null), ['class' => "datetimepicker form-control form-control-small", 'style' => 'width: 200px'])}
        </div>
    </div>

<div class="btn-bar">
    <div class="btn-left"><a class="btn btn-cancel" href="{$back_url}">{$smarty.const.IMAGE_CANCEL}</a></div>
    <div class="btn-right"><input class="btn btn-confirm" type="submit" value="{$smarty.const.IMAGE_SAVE}"></div>
</div>

{Html::endForm()}
</div>
<script>
    $(document).ready(function () {
        $(".check_on_off").tlSwitch();
        $('#startDate').tlDatetimepicker();
        $('#expiresDate').tlDatetimepicker();
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


