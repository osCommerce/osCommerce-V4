{*
This file is part of osCommerce ecommerce platform.
 * osCommerce the ecommerce

@link https://www.oscommerce.com
@copyright Copyright (c) 2000-2022 osCommerce LTD

Released under the GNU General Public License
 * For the full copyright and license information, please view the LICENSE.TXT file that was distributed with this source code.
*}
{include file='../assets/tabs.tpl' scope="global"}
{use class="common\helpers\Html"}
{\backend\assets\BDTPAsset::register($this)|void}
{use class="backend\components\Currencies"}
{use class="Yii"}

<style type="text/css">
    .cbox-left {
        width: 100%;
    }
    .specialTable {
        max-width: 980px;
    }
</style>
{Currencies::widget()}
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
          <div class="sales-dates">
              {if !\common\helpers\Extensions::isCustomerGroupsAllowed() }
                {$dataToSwitch=$idSuffix}
              {else}
                {$dataToSwitch=substr($idSuffix, 0, -2)}
              {/if}
              {if $sInfo->specials_id|default:null>0 }
                <label>
                  {$smarty.const.TEXT_CURRENT_STATUS}<span class="colon">:</span>

                {if $data['specials_enabled'] > 0}
                  <strong>{$smarty.const.TEXT_MANUALLY_ACTIVATED} </strong>
                {elseif $data['specials_disabled']>0}
                  <strong>{$smarty.const.TEXT_MANUALLY_DISABLED} </strong>
                {else}
                  {if $data['specials_scheduled']}
                    {$smarty.const.TEXT_SCHEDULED}
                  {/if}
                  {if  $data['sales_status'] > 0}
                    {$smarty.const.TEXT_ACTIVE}
                  {else}
                    {$smarty.const.TEXT_INACTIVE}
                  {/if}
                {/if}
                </label>
              {/if}

              <div class="specials-rules m-b-2">
                  <div class="">
                      <label>
                      <input type="radio" value="2" id="r3_special_status" data-toswitch="{if ($default_currency['id']==$data['currencies_id'])}market_sales_switch,{/if}div_sale_prod{$dataToSwitch}" name="special_status{$fieldSuffix|escape}" class="r_check_sale_prod" {if $data['specials_disabled']<=0 && $data['specials_enabled']<=0 } checked="checked" {/if} data-defaults-set="special_price,special_price_gross" data-defaults-on="0" data-defaults-off="-1"/><span>
                              {$smarty.const.TEXT_ENABLE_SCHEDULE}</span>
                      </label>
                  </div>
                  <div class="">
                      <label>
                          <input type="radio" value="-1" id="r2_special_status" data-toswitch="{if ($default_currency['id']==$data['currencies_id'])}market_sales_switch,{/if}div_sale_prod{$dataToSwitch}" name="special_status{$fieldSuffix|escape}" class="r_check_sale_prod" {if $data['specials_disabled'] > 0} checked="checked" {/if} data-defaults-set="special_price,special_price_gross" data-defaults-on="0" data-defaults-off="-1"/><span>
                              {$smarty.const.TEXT_FORCE_DISABLE}</span>
                      </label>
                  </div>
                  <div class="">
                      <label>
                          <input type="radio" value="1" id="r4_special_status" data-toswitch="{if ($default_currency['id']==$data['currencies_id'])}market_sales_switch,{/if}div_sale_prod{$dataToSwitch}" name="special_status{$fieldSuffix|escape}" class="r_check_sale_prod" {if $data['specials_enabled'] > 0} checked="checked" {/if} data-defaults-set="special_price,special_price_gross" data-defaults-on="0" data-defaults-off="-1"/>
                          <span>{$smarty.const.TEXT_FORCE_ENABLE}</span>
                      </label>
                  </div>

              </div>

              <div class="row">
          <div class="dates col-7">
            <div class="_our-pr-line row">
              <div class="_disable-btn col-6">
                <label>{$smarty.const.TEXT_START_DATE}<span class="colon">:</span></label>
                <input id="special_start_date" name="special_start_date{$fieldSuffix|escape}" value='{\common\helpers\Date::datepicker_date_time($data['start_date'])}' class="tl-dtp form-control"/>
              </div>
              <div class="_disable-btn col-6">
                <label>{$smarty.const.TEXT_EXPIRY_DATE}<span class="colon">:</span></label>
                <input id="special_expires_date" name="special_expires_date{$fieldSuffix|escape}" value='{\common\helpers\Date::datepicker_date_time($data['expires_date'])}' class="tl-dtp form-control form-control-small"/>
              </div>
            </div>
          </div>

          <div class="limits col-5">
            <div class="_our-pr-line row">
              <div class="_disable-btn col-6">
                <label class="inline">{$smarty.const.TEXT_MAX_QTY_TO_SELL}<span class="colon">:</span></label>
                <div class="info-hint"><div class="info-hint-box"><div class="info-hint-mustache"></div>{$smarty.const.TEXT_LEAVE_EMPTY_FOR_NO_LIMITS}</div></div>
                <input name="total_qty{$fieldSuffix|escape}" value='{if !empty($data['total_qty'])}{$data['total_qty']}{/if}' class="form-control total_qty"/>
              </div>
              <div class="_disable-btn col-6">
                <label>{$smarty.const.TEXT_MAX_QTY_TO_SELL_IN_ORDER}<span class="colon">:</span></label>
                <input name="max_per_order{$fieldSuffix|escape}" value='{if !empty($data['max_per_order'])}{$data['max_per_order']}{/if}' class="form-control max_per_order"/>
              </div>
            </div>
          </div>
              </div>
        </div>

{/function}

﻿<div class="specialTable">
    {Html::beginForm(['specials/submit'], 'post', ['name' => "product_edit", 'id' => "save_item_form", 'onsubmit' => "return saveItem();"])}
    {Html::hiddenInput('products_id', $pInfo->products_id)}

    {if !$popup}
        <div class="row p-l-2 m-b-2">
            <div class="col-3">
                <b>{$smarty.const.TEXT_SPECIALS_PRODUCT}</b>
            </div>
            <div class="col-9">
                <a target="blank" href="{Yii::$app->urlManager->createUrl(['categories/productedit', 'pID' => $pInfo->products_id])}">{$backendProductDescription.products_name}</a>
            </div>
        </div>
    {/if}

    <div class="row m-b-2 p-l-2">
        <div class="col-5">
            <label>
                {if $smarty.const.PRICE_WITH_BACK_TAX == 'True'}
                    {$smarty.const.TEXT_GROSS_PRICE}
                {else}
                    {$smarty.const.TEXT_NET_PRICE}
                {/if}:
            </label>
            {$price}
        </div>
        <div class="col-6" {if $smarty.const.PRICE_WITH_BACK_TAX == 'True'}style="display: none;"{/if}>
            <label>{$smarty.const.TEXT_GROSS_PRICE}:</label> {$priceGross}
        </div>
    </div>

    <div class="row m-b-2 p-l-2">
        {if (true === \common\helpers\Acl::rule(['TEXT_SETTINGS', 'BOX_HEADING_SPECIALS_TAGS'])) }
            <div class="col-2">
                <label for="specialsTypeId"><a href="specials-types" target="blank">{$smarty.const.BOX_HEADING_SPECIALS_TAGS}</a>:</label>
            </div>

            <div class="col-3">
                {Html::dropDownList('specials_type_id', $sInfo->specials_type_id|default:null, $specials_types, ['class'=>"form-control form-control-med"])}
            </div>
        {/if}

        <div class="col-2">
            <label for="promoteType">{$smarty.const.BOX_HEADING_SPECIALS_PROMOTE_TYPE}:</label>
        </div>
        <div class="col-4">
            {Html::dropDownList('promote_type', $sInfo->promote_type|default:null, $promote_types, ['class'=>"form-control form-control-med"])}
        </div>
    </div>

    <div class="tabbable-custom">
        <div class="tab-content special-edit-settings">

            {$price_tab_callback = 'productSaleOnlyPriceBlock'}
            {$hideSuppliersPart = 1}
            {$specials_id = $sInfo->specials_id|default:null}
            {include file='../categories/productedit/price_js.tpl'}
            {include file='../categories/productedit/price.tpl'}
        </div>
    </div>

    <div class="btn-bar">
        <div class="btn-left">
            <a class="btn btn-cancel" href="{$back_url}">{$smarty.const.IMAGE_CANCEL}</a>
        </div>
        <div class="btn-right">
            <input class="btn btn-primary" type="submit" value="{$smarty.const.IMAGE_SAVE}">
        </div>
    </div>

    {Html::endForm()}
</div>

<script>
 {$idSuffix="_0"} // 2do marketing prices
$(document).ready(function(){
  {if $popup}
    {if $hash}
      $('a[href="#popup_{$hash}"]').click();
    {/if}
    $('.btn-cancel').popUp({ 'box_class':'popupSales' });
  {/if}
      $('input.tl-dtp').tlDatetimepicker();

    $(".limits .max_per_order").on("change", function (e) {
        try {
            var check = $(this).parents('.limits').find('.total_qty')[0].value;
            if (parseInt($(this).val()) > parseInt(check)) {
                $(this).val(parseInt(check));
            }
        } catch ( e ) { }
    });
    $(".limits .total_qty").on("change", function (e) {
        try {
            var check = $(this).parents('.limits').find('.max_per_order')[0].value;
            if (parseInt($(this).val()) < parseInt(check)) {
                $($(this).parents('.limits').find('.max_per_order')[0]).val(parseInt($(this).val()));
            }
        } catch ( e ) { }
    });

})

    function saveValidItem() {

      if (typeof unformatMaskMoney == 'function') {
        unformatMaskMoney();
      }
      $.post("specials/submit", $('#save_item_form').serialize(), function (data, status) {
        if (status == "success") {
          var msg = "{$smarty.const.MESSAGE_SAVED|escape:'html'}";
          if (data.result != 1) {
            msg = '<div class="alert alert-danger">' + data.message + '</div>';
          }
          /*else {
            $('.mask-money').setMaskMoney();
          }*/
          bootbox.alert({
            title: "{$smarty.const.BOX_CATALOG_SPECIALS|escape:'html'}",
            message: msg,
            size: 'small',
          });
          if (data.id > 0) {
            {if !$popup}
            setTimeout(function() {
              window.location = '{$app->urlManager->createUrl('specials/specialedit')}?id=' + data.id
            }, 2000);
            {else}
                {if $popup_edit} 
                    {* just close *}
              $('.popup-box .pop-up-close').click();

                {else} 
                    {* reload pop-up list *}
              $('.pop-up-content .btn-cancel').click();
              
                {/if}
            {/if}
          }
        } else {
            alert("Request error.");
        }
      }, "json");
    }

    function saveItem() {

      {if $smarty.const.SALE_STRICT_DATE == 'True'}
        $.post("specials/validate", $('#save_item_form').serialize(), function (data, status) {
            if (status == "success") {
              if (data.valid == 1) {
                saveValidItem();
              } else {
                bootbox.dialog({
                    title: "{$smarty.const.TEXT_SPECIALS_INTERSECT|escape:'html'}",
                    message: '<div class="alert alert-warning">' + "{$smarty.const.TEXT_SPECIALS_INTERSECT_MESSAGE|escape:'html'}<br>" + data.list  + '</div>',
                    size: 'large',
                    buttons: {
                      {if $smarty.const.ALLOW_SALES_UPDATE_DATES == 'True'}
                        ok: {
                            label: "{$smarty.const.TEXT_UPDATE_EXISTING|escape:'html'}",
                            className: 'btn-danger',
                            callback: function(){
                                if (typeof unformatMaskMoney == 'function') {
                                  unformatMaskMoney();
                                }
                                $.post("specials/submit", $('#save_item_form').serialize(), function (data, status) {
                                  if (status == "success") {
                                    var msg = "{$smarty.const.MESSAGE_SAVED|escape:'html'}";
                                    if (data.result != 1) {
                                      msg = '<span class="alert alert-danger">' + data.message + '</span>';
                                    } else {
                                      $('.mask-money').setMaskMoney();
                                    }
                                    bootbox.alert({
                                      'title': "{$smarty.const.BOX_CATALOG_SPECIALS|escape:'html'}",
                                      'message': msg,
                                      'size': 'small',
                                    });
                                  } else {
                                      alert("Request error.");
                                  }
                                }, "json");
                            }
                        },
                      {/if}
                        cancel: {
                            label: "{$smarty.const.TEXT_CANCEL|escape:'html'}",
                            className: 'btn-info',
                            callback: function(){
                                //console.log('Custom OK clicked');
                            }
                        }
                    }
                });

              }
            } else {
                alert("Request error.");
            }
        }, "json");

      {else}
        saveValidItem();
      {/if}

      return false;
    }
</script>

{function productSaleOnlyPriceBlock}
{* $data: [ name => val], $fieldSuffix: '[1][0]'  $idSuffix: '-1-0' *}
{* workaround for switchers: group on/off *}
{if !\common\helpers\Extensions::isCustomerGroupsAllowed()}
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
    <div id="group_price_container{$idSuffix}" class="js_group_price p-b-2" data-base_price="{$data['base_price']|escape}" data-group_discount="{$data['tabdata']['groups_discount']}" data-currencies-id="{$data['currencies_id']}" data-base_special_price="{$data['base_specials_price']|escape}" >

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
            <div class="div_sale_prod row m-b-2">
                <div class="col-5 align-right">
                    <label for="popt{$idSuffix}_s2"><input type="radio" class="price-options" id="popt{$idSuffix}_s2" value="1" {if $data['products_group_special_price']!='-1'}checked{/if} data-idsuffix="{$idSuffix}"/> {sprintf($smarty.const.TEXT_ENABLED_FOR, $data['tabdata']['title'])}</label>
                </div>
                <div class="col-5">
                    <label for="popt{$idSuffix}_s0"><input type="radio" class="price-options" id="popt{$idSuffix}_s0" value="-1" {if $data['products_group_special_price']=='-1'}checked{/if} data-idsuffix="{$idSuffix}"/> {sprintf($smarty.const.TEXT_PRICE_SWITCH_DISABLE, $data['tabdata']['title'])}</label>
                </div>
            </div>

            {/if}

          </div>
        </div>
        <div class="{if ($default_currency['id']!=$data['currencies_id']) }market_sales_switch{/if} sale_to_dis {if $data['specials_disabled']>0 }dis_module{/if}">
        <div id="div_sale_prod{$idSuffix}" class="sale-prod-line-block after div_sale_prod div_sale_prod{$idSuffix}" {if ($showSalesDiv==0 || $data['products_group_special_price']==-1)}style="display:none;"{/if}>
          <div class="_sale-prod-line row">
              <div class="{if $smarty.const.PRICE_WITH_BACK_TAX == 'True'}col-3 {else}col-2{/if} align-right">
                  <label class="sale-info1">
                        {if $smarty.const.PRICE_WITH_BACK_TAX == 'True'}
                            {$smarty.const.TEXT_GROSS_PRICE}
                        {else}
                            {$smarty.const.TEXT_NET_PRICE}
                        {/if}
                      <span class="colon">:</span></label>
              </div>
          <div class="col-3">
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
              <div class="col-3 align-right" {if $smarty.const.PRICE_WITH_BACK_TAX == 'True'}style="display: none;"{/if}>
                  <label class="sale-info1">{$smarty.const.TEXT_SALE_GROSS}<span class="colon">:</span></label>
              </div>
          <div class="col-3" {if $smarty.const.PRICE_WITH_BACK_TAX == 'True'}style="display: none;"{/if}>
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
