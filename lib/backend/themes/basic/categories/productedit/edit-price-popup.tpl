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
      $('a[href="#popup_{$hash}"]').click();
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

{function productSaleOnlyPriceBlock}
{* $data: [ name => val], $fieldSuffix: '[1][0]'  $idSuffix: '-1-0' *}
{* workaround for switchers: group on/off *}
{if $smarty.const.CUSTOMERS_GROUPS_ENABLE != 'True'}
  {if empty($data['products_group_price'])  }
    {$data['products_group_price']=0}
    {$data['products_group_price_gross']=0}
  {/if}
  {if empty($data['products_group_special_price']) }
    {$data['products_group_special_price']=0}
    {$data['products_group_special_price_gross']=0}
  {/if}
{/if}
{if !$app->controller->view->useMarketPrices }
  {$data['currencies_id']=$default_currency['id']}
{/if}
{if !isset($data['products_group_special_price']) || $data['products_group_special_price']==''}
  {if $data['groups_id']>0 }
    {$data['products_group_special_price']='-2'}
  {else}
    {$data['products_group_special_price']='0'}
  {/if}
  {$showSalesDiv=0}
{else}
  {$showSalesDiv=1}
{/if}
{$showSalesDiv=1}

    <div id="group_price_container{$idSuffix}" class="js_group_price" data-base_price="{$data['base_price']|escape}" data-group_discount="{$data['tabdata']['groups_discount']}" data-currencies-id="{$data['currencies_id']}" data-base_special_price="{$data['base_specials_price']|escape}" >

      <div id="div_wrap_hide{$idSuffix}" {if round($data['products_group_price'])==-1}style="display:none;"{/if}>
        <!-- specials/sales -->
        <div class="our-pr-line after our-pr-line-check-box1 dfullcheck sale_to_dis {if $data['specials_disabled']>0 }dis_module{/if}">
          <div class="{if ($default_currency['id']!=$data['currencies_id']) }market_sales_switch{/if}" {*if ($default_currency['id']!=$data['currencies_id']) }style="display:none;"{/if*}>

            {if $data['groups_id']>0 }
            <div class="our-pr-line after div_sale_prod div_sale_prod{$idSuffix}" {if ($showSalesDiv==0)}style="display:none;"{/if}>
              <label>{$smarty.const.TEXT_ENABLE_SALE}</label>
              <label for="popt{$idSuffix}_s2"><input type="radio" class="price-options" id="popt{$idSuffix}_s2" value="-2" {if $data['products_group_special_price']=='-2'}checked{/if} data-idsuffix="{$idSuffix}"/>{$smarty.const.TEXT_PRICE_SWITCH_MAIN_PRICE}</label>
              <label for="popt{$idSuffix}_s1"><input type="radio" class="price-options" id="popt{$idSuffix}_s1" value="1" {if round($data['products_group_special_price'])>=0}checked{/if} data-idsuffix="{$idSuffix}"/>{sprintf($smarty.const.TEXT_PRICE_SWITCH_OWN_PRICE, $data['tabdata']['title'])}</label>
              <label for="popt{$idSuffix}_s0"><input type="radio" class="price-options" id="popt{$idSuffix}_s0" value="-1" {if $data['products_group_special_price']=='-1'}checked{/if} data-idsuffix="{$idSuffix}"/>{sprintf($smarty.const.TEXT_PRICE_SWITCH_DISABLE, $data['tabdata']['title'])}</label>
            </div>
            {else}
            <div class="our-pr-line after div_sale_prod">
              <label for="popt{$idSuffix}_s2"><input type="radio" class="price-options" id="popt{$idSuffix}_s2" value="1" {if $data['products_group_special_price']!='-1'}checked{/if} data-idsuffix="{$idSuffix}"/>{sprintf($smarty.const.TEXT_ENABLED_FOR, $data['tabdata']['title'])}</label>
              <label for="popt{$idSuffix}_s0"><input type="radio" class="price-options" id="popt{$idSuffix}_s0" value="-1" {if $data['products_group_special_price']=='-1'}checked{/if} data-idsuffix="{$idSuffix}"/>{sprintf($smarty.const.TEXT_PRICE_SWITCH_DISABLE, $data['tabdata']['title'])}</label>
            </div>

            {/if}

          </div>
        </div>
        <div class="{if ($default_currency['id']!=$data['currencies_id']) }market_sales_switch{/if} sale_to_dis {if $data['specials_disabled']>0 }dis_module{/if}">
        <div id="div_sale_prod{$idSuffix}" class="sale-prod-line-block after div_sale_prod div_sale_prod{$idSuffix}" {if ($showSalesDiv==0 || $data['products_group_special_price']==-1)}style="display:none;"{/if}>
          <div class="_sale-prod-line our-pr-line">
          <div>
            <label class="sale-info1">{$smarty.const.TEXT_SALE}<span class="colon">:</span></label>
            {if $data['products_group_special_price']>0.001}
              {$val = $data['products_group_special_price']}
            {else}
              {$val=''}
              {*$val = $data['base_specials_price']*((100-$data['tabdata']['groups_discount'])/100)*}
            {/if}
            <input id="special_price{$idSuffix}" data-idsuffix="{$idSuffix}" name="special_price{$fieldSuffix|escape}" value='{$val|escape}' onKeyUp="updateGrossPrice(this);" data-roundTo="{$data['round_to']}" class="form-control mask-money" {if $data['groups_id']>0 && round($data['products_group_special_price'])==-2}style="display:none;"{/if} data-precision="{$smarty.const.MAX_CURRENCY_EDIT_PRECISION}" data-currency="{$data['currencies_id']}"/>
{if $data['groups_id']>0 }
            <span id="span_special_price{$idSuffix}" class="form-control-span"{if $data['products_group_specials_price']>-0.99}style="display:none;"{/if}>{$currencies->formatById($val, false, $data['currencies_id'])|escape}</span>
{/if}
          </div>
          <div>
            <label class="sale-info1">{$smarty.const.TEXT_SALE_GROSS}<span class="colon">:</span></label>
            {if $data['products_group_special_price_gross']>0.001}
              {$val = $data['products_group_special_price_gross']}
            {else}
              {*$val = $data['base_specials_price_gross']*((100-$data['tabdata']['groups_discount'])/100)*}
              {$val=''}
            {/if}
            <input id="special_price_gross{$idSuffix}" data-idsuffix="{$idSuffix}" value='{$val|escape}' onKeyUp="updateNetPrice(this);" class="form-control mask-money" {if $data['groups_id']>0 && round($data['products_group_special_price'])==-2}style="display:none;"{/if}  data-currency="{$data['currencies_id']}" data-roundto="{$data['round_to']}"/>
{if true || $data['groups_id']>0 }
            <span id="span_special_price_gross{$idSuffix}" class="form-control-span" {if $data['products_group_specials_price']<-1.1}style="display:none;"{/if}>{$currencies->formatById($val, false, $data['currencies_id'])}</span>
{/if}
          </div>
          </div>
        </div>
        </div>


      </div>
    </div>
{/function}


{function SalesParams}
  {*tabs=$app->controller->view->price_tabs tabparams=$tabparams  fieldsData=$app->controller->view->price_tabs_data  id_prefix = $id_prefix*}
  {if $smarty.const.CUSTOMERS_GROUPS_ENABLE != 'True' || !$app->controller->view->useMarketPrices}
    {if $smarty.const.CUSTOMERS_GROUPS_ENABLE != 'True' && !$app->controller->view->useMarketPrices}
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



{*
          <div class="limits">
            <div class="sale-prod-line _our-pr-line">
              <div class="_disable-btn">
                <label class="inline">{$smarty.const.TEXT_MAX_QTY_TO_SELL}<span class="colon">:</span></label>
                <div class="info-hint"><div class="info-hint-box"><div class="info-hint-mustache"></div>{$smarty.const.TEXT_LEAVE_EMPTY_FOR_NO_LIMITS}</div></div>
                <input name="total_qty{$fieldSuffix|escape}" value='{if !empty($data['total_qty'])}{$data['total_qty']}{/if}' class="form-control"/>
              </div>
              <div class="_disable-btn">
                <label>{$smarty.const.TEXT_MAX_QTY_TO_SELL_IN_ORDER}<span class="colon">:</span></label>
                <input name="max_per_order{$fieldSuffix|escape}" value='{if !empty($data['max_per_order'])}{$data['max_per_order']}{/if}' class="form-control"/>
              </div>
            </div>
          </div>
        </div>
*}
{/function}
