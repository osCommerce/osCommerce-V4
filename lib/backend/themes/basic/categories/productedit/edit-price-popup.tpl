{*
This file is part of osCommerce ecommerce platform.
 * osCommerce the ecommerce

@link https://www.oscommerce.com
@copyright Copyright (c) 2000-2022 osCommerce LTD

Released under the GNU General Public License
 * For the full copyright and license information, please view the LICENSE.TXT file that was distributed with this source code.
*}

{include file='../../assets/tabs.tpl' scope="global"}
{use class="common\helpers\Html"}
{\backend\assets\BDTPAsset::register($this)|void}
{use class="backend\components\Currencies"}
{use class="Yii"}

{Currencies::widget()}


﻿<div class="group-price-table" style="padding: 10px">
    ﻿<div class="wait-loading"></div>
    {Html::beginForm(['categories/group-price-submit'], 'post', ['name' => "product_edit", 'id' => "save_item_form", 'onsubmit' => "return savePopupItem();"])}
    {Html::hiddenInput('products_id', $pInfo->products_id)}
    {call SalesParams}


    <table cellspacing="0" cellpadding="0" width="100%">
      <tr><td colspan="2" class="">
            <div class="tabbable-custom" style='padding-top:10px'>
                {*include file='price_js.tpl'*}
                <div class="tab-content group-price-details">
                    {*$price_tab_callback = 'productSaleOnlyPriceBlock'*}
                    {$hideSuppliersPart = 1}
                    {*include file='price.tpl'*}
                </div>
            </div>
      </td></tr>
    </table>
    <div class=" col-md-12">
      <div class="btn-bar col-md-12">
        <div class="btn-left"><a class="btn btn-cancel" href="{$back_url}">{$smarty.const.IMAGE_CANCEL}</a></div>
        <div class="btn-right"><input class="btn btn-primary" type="submit" value="{$smarty.const.IMAGE_SAVE}"></div>
      </div>
    </div>
{Html::endForm()}
</div>
<script>
$(document).ready(function(){
  {if $popup}
    {if $hash}
      $('[data-bs-target="#popup_{$hash}"]').click();
    {/if}
    $('.btn-cancel').popUp({ 'box_class':'popupSales' });
  {/if}

    $('select.group-id').on('change', function(){
        $('.pop-up-content .group-price-table').addClass('loading');
        $('.pop-up-content .group-price-table').css('cursor', 'wait');
        $('.pop-up-content .group-price-details').removeClass('inited');
        $.post("categories/product-price-edit", 
            'products_id={$products_id}&currencies_id={$currencies_id}&only_price=1&group_id=' + $(this).val(),
            function (data, status) {
                if (status == "success") {
                    $('.pop-up-content .group-price-details').html(data);
                    var $priceBlock = $('.pop-up-content .group-price-details');

                    updateVisibleGrossInputs($priceBlock);
                    // init new visible bootstrapSwitch
                    tab = $priceBlock.not(".inited");
                    if (tab.length) {
                      tab.addClass('inited');

                      $('.check_sale_prod:visible, .check_points_prod:visible, .check_qty_discount_prod:visible, .check_gift_wrap:visible, .check_shipping_surcharge:visible, .check_disable_discount:visible, .check_delivery_option:visible', tab).bootstrapSwitch(bsPriceParams);
                      $('.r_check_sale_prod', tab).on('click', rPriceSwitch);
                      $('.r_check_sale_prod', tab).on('vswitch', bsPriceSwitch);
                    }
                    var mask_money = $('.mask-money');
                    if (mask_money && mask_money.length<0+{intval($smarty.const.CATALOG_SPEED_UP_DESIGN)})
                    mask_money.setMaskMoney();

                    $('.pop-up-content .group-price-table').removeClass('loading');
                    $('.pop-up-content .group-price-table').css('cursor', '');

                } else {
                    alert("Request error.");
                }
        }, "html");
    });

})

    function savePopupItem() {

      if (typeof unformatMaskMoney == 'function') {
        unformatMaskMoney();
      }
      $.post("categories/group-price-submit", $('#save_item_form').serialize(), function (data, status) {
        if (status == "success") {
          var msg = "{$smarty.const.MESSAGE_SAVED|escape:'html'}";
          if (data.result != 1) {
            msg = '<div class="alert alert-danger">' + data.message + '</div>';
          }
          /*else {
            $('.mask-money').setMaskMoney();
          }*/
          bootbox.alert({
            title: "{$smarty.const.TEXT_EDIT_GROUP_PRICE|escape:'html'}",
            message: msg,
            size: 'small',
          });
          if (data.result == 1) {
              $('.pop-up-content .btn-cancel').click();
          }
        } else {
            alert("Request error.");
        }
      }, "json");
        return false;
    }

</script>

{function SalesParams}
  {*tabs=$app->controller->view->price_tabs tabparams=$tabparams  fieldsData=$app->controller->view->price_tabs_data  id_prefix = $id_prefix*}
  {if !\common\helpers\Extensions::isCustomerGroupsAllowed() || !$app->controller->view->useMarketPrices}
    {if !\common\helpers\Extensions::isCustomerGroupsAllowed() && !$app->controller->view->useMarketPrices}
      {$data=$fieldsData}
    {else}
      {$data=$fieldsData[0]}
    {/if}
  {else}
    {$data=$fieldsData[0][0]}
  {/if}

          <div class="customer-groups">
            <div class="sale-prod-line _our-pr-line">
                {$smarty.const.TEXT_GROUP}<span class="colon">:</span>
                {Html::dropDownList('group_id', $group_id, $groups, ['class' => 'group-id form-control'])}
            </div>
          </div>


{/function}
