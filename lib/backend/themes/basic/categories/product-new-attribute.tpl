{*
This file is part of osCommerce ecommerce platform.
 * osCommerce the ecommerce

@link https://www.oscommerce.com
@copyright Copyright (c) 2000-2022 osCommerce LTD

Released under the GNU General Public License
 * For the full copyright and license information, please view the LICENSE.TXT file that was distributed with this source code.
*}

{include file='../assets/tabs.tpl' scope="global"}
{function attributePriceBlock}
{* $data: [ name => val], $fieldSuffix: '[1][0]'  $idSuffix: '-1-0' *}
{$idSuffix="-`$upridSuffix``$idSuffix`"}
{$fieldSuffix="`$upridSuffix``$fieldSuffix`"}
    <div id="group_price_container{$idSuffix}" class="js_inventory_group_price js_group_price" data-base_price="{if isset($data['base_price'])}{$data['base_price']|escape:quotes}{/if}" data-group_discount="{if isset($data['tabdata']['groups_discount'])}{$data['tabdata']['groups_discount']}{/if}" data-currencies-id="{$data['currencies_id']}" data-uprid_suffix="{$upridSuffix}">
{* workaround for switchers: group on/off *}
{if (!\common\helpers\Extensions::isCustomerGroupsAllowed() && $app->controller->view->useMarketPrices != true)  || $data['groups_id']==0}
  {if {$data['products_group_price']<0} }
    {$data['products_group_price']=0}
    {$data['products_group_price_gross']=0}
  {/if}
  {if !isset($data['products_group_special_price']) || $data['products_group_special_price']<0 }
    {$data['products_group_special_price']=0}
    {$data['products_group_special_price_gross']=0}
  {/if}
{/if}
{if $data['groups_id']>0 }
  {if !isset($data['products_group_price']) || $data['products_group_price']==''}
    {$data['products_group_price']=-2}
  {/if}
        <div class="our-pr-line after">
          {*<div class="switch-toggle switch-3 switch-candy">*}
            <label for="iopt{$idSuffix}_m2"><input type="radio" class="price-options" id="iopt{$idSuffix}_m2" value="-2" {if {round($data['products_group_price'])}==-2}checked{/if} data-idSuffix="{$idSuffix}"/>{$smarty.const.TEXT_PRICE_SWITCH_MAIN_PRICE}</label>
            <label for="iopt{$idSuffix}_m1"><input type="radio" class="price-options" id="iopt{$idSuffix}_m1" value="1" {if {round($data['products_group_price'])}>=0}checked{/if} data-idSuffix="{$idSuffix}"/>{sprintf($smarty.const.TEXT_PRICE_SWITCH_OWN_PRICE, $data['tabdata']['title'])}</label>
            <label for="iopt{$idSuffix}_m0"><input type="radio" class="price-options" id="iopt{$idSuffix}_m0" value="-1" {if {round($data['products_group_price'])}==-1}checked{/if} data-idSuffix="{$idSuffix}"/>{sprintf($smarty.const.TEXT_PRICE_SWITCH_DISABLE, $data['tabdata']['title'])}</label>
          {*</div>*}
        </div>
{/if}
      <div id="div_wrap_hide{$idSuffix}" {if {round($data['products_group_price'])}==-1}style="display:none;"{/if}>
<!-- main price -->
        <div class="our-pr-line after">
          <div>
            <label>{$smarty.const.TEXT_NET_PRICE}</label>
            <input id="products_group_price{$idSuffix}" name="products_group_price_{$fieldSuffix|escape:quotes}" value='{$data['products_group_price']|escape:quotes}' onKeyUp="updateGrossPrice(this);" data-roundTo="{$data['round_to']}" class="form-control{if (!\common\helpers\Extensions::isCustomerGroupsAllowed() || $data['groups_id']==0) && ($app->controller->view->useMarketPrices != true || $default_currency['id']==$data['currencies_id'])} default_currency{/if}" {if {round($data['products_group_price'])}==-2}style="display:none;"{/if}/>
{if {$data['groups_id']}>0 }
            <span id="span_products_group_price{$idSuffix}" class="form-control-span"{if {round($data['products_group_price'])}>=0}style="display:none;"{/if}>{if isset($data['base_price'])}{$currencies->formatById($data['base_price']*((100-$data['tabdata']['groups_discount'])/100), false, $data['currencies_id'])|escape:quotes}{/if}</span>
{/if}
          </div>
          {*??? there is separate tab.
          <div class="disable-btn supplier-price-cost sbalr">
            <label>&nbsp;</label>
            <a href="javascript:void(0)" class="btn" onclick="return chooseSupplierPrice('products_group_price{$idSuffix}')" {if {round($data['products_group_price'])}==-2}style="display:none;"{/if}>{$smarty.const.TEXT_PRICE_COST}</a>
          </div>*}
        </div>
        <div class="our-pr-line option-gross-price-line after">
          <div>
            <label>{$smarty.const.TEXT_GROSS_PRICE}</label>
            <input id="products_group_price_gross{$idSuffix}" value='{$data['products_group_price_gross']|escape:quotes}' onKeyUp="updateNetPrice(this);" class="form-control{if (!\common\helpers\Extensions::isCustomerGroupsAllowed() || $data['groups_id']==0) && ($app->controller->view->useMarketPrices != true || $default_currency['id']==$data['currencies_id'])} default_currency{/if}"{if {round($data['products_group_price'])}==-2}style="display:none;"{/if}/>
{if {$data['groups_id']}>0 }
            <span id="span_products_group_price_gross{$idSuffix}" class="form-control-span"{if {round($data['products_group_price'])}>=0}style="display:none;"{/if}>{if isset($data['base_price_gross'])}{$currencies->formatById($data['base_price_gross']*((100-$data['tabdata']['groups_discount'])/100), false, $data['currencies_id'])|escape:quotes}{/if}</span>
{/if}
          </div>
        </div>
      </div>
    </div>
{/function}

{function newVirtualOptionValue }
  {foreach $options as $option}
    {$option['products_options_id']=$products_options_id} {** mnogo raznogo koda mlya *}
    {$upridSuffix = "`$products_id`-`$products_options_id`-`$option['products_options_values_id']`"}
  <tr role="row" class="js-option-value" data-option_value_id="{$option['products_options_values_id']}">
      <td class="sort-pointer"></td>
        {if \common\helpers\Acl::checkExtensionAllowed('AttributesImages', 'allowed')}
          {\common\extensions\AttributesImages\AttributesImages::productBlock($option, $option)}
        {/if}
      <td class="name-ast">
          {$option['products_options_values_name']}
          <input type="hidden" name="products_attributes_id[{$products_options_id}][{$option['products_options_values_id']}]" id="products_attributes_id_{$products_options_id}_{$option['products_options_values_id']}" value="{$option['products_attributes_id']}" />
      </td>
      <td class="set-ditails text-center"><input type="checkbox" class="js-option-default-group" data-option-group="{$option['products_options_id']}" name="default_option_value[{$option['products_options_id']}][{$option['products_options_values_id']}]" value="1" {if $option['default_option_value']}checked{/if}></td>
      <td class="ast-price ast-price-net one-attribute-force">
          {$smarty.const.TEXT_NET}<br>
          <span class="inventory-price-net-{$upridSuffix}" id="attr_list_price-{$upridSuffix}">{$option['price_prefix']}{$option['net_price_formatted']}</span>
      </td>
      <td class="ast-price ast-price-gross one-attribute-force">
          {$smarty.const.TEXT_GROSS}<br>
          <span class="inventory-price-gross-{$upridSuffix}" id="attr_list_price_gross-{$upridSuffix}">{$option['price_prefix']}{$option['gross_price_formatted']}</span>
      </td>
      <td class="set-ditails one-attribute-force">

          <a href="#id-{$upridSuffix}" class="btn inventory-popup-link">{$smarty.const.SET_UP_DETAILS}</a>
<!-- price (& weight) popup -->
      <div class="popup-box-wrap-page popup-box-wrap-page-1" style="display: none" id="id-{$upridSuffix}">
        <div class="around-pop-up-page"></div>
        <div class="popup-box-page">
          <div class="pop-up-close-page"></div>
          <div class="pop-up-content-page">
            <div class="popup-heading">
              {$smarty.const.SET_UP_DETAILS}&nbsp;-&nbsp;{$option['products_options_values_name']}
            </div>
            <div class="popup-content">
              <div class="widget-content">
                <div class="tabbable tabbable-custom">
                  <div class="tab-content tab-content-popup">

                  {$tbs = [['title'=>$smarty.const.TEXT_PRICE, 'callback'=>'attrPriceTab', 'tabs_type' => 'hTab', 'cssClass' => 'tabs-attr-price'],
                           ['title'=>$smarty.const.TEXT_WEIGHT, 'callback' => 'attrWeightTab', 'tabs_type' => 'hTab', 'cssClass' => 'tabs-attr-weight']]}
                  {call hTab data=$tbs id_prefix="attr_popup`$upridSuffix`"}

                  {function attrPriceTab}
                    <div class="tab-pane tab-prices active{if strpos($option['price_prefix'],'%')} option-percent-price{/if}" id="tab_{$upridSuffix}-1">
{if empty($products_price_full)}
      <div class="our-pr-line after">
        <label for="invPricePrefix-{$upridSuffix}">{$smarty.const.TEXT_PRICE_PREFIX}</label>
        <select id="invPricePrefix-{$upridSuffix}" name="inventorypriceprefix_{$upridSuffix|escape:quotes}" class="form-control default_currency">
          <option value="+" {if $option['price_prefix']=='+'}selected{/if}>+</option>
          <option value="-" {if $option['price_prefix']=='-'}selected{/if}>-</option>
          <option value="+%" {if $option['price_prefix']=='+%'}selected{/if}>+%</option>
          <option value="-%" {if $option['price_prefix']=='-%'}selected{/if}>-%</option>
          <option value="+%b" {if $option['price_prefix']=='+%b'}selected{/if}>+% of base price</option>
          <option value="-%b" {if $option['price_prefix']=='-%b'}selected{/if}>-% of base price</option>
        </select>
      </div>
{/if}

                      {if {$app->controller->view->price_tabs|@count} > 0 }
                        {$tabparams = $app->controller->view->price_tabparams}
                        {$tabparams[count($tabparams)-1]['callback'] = 'attributePriceBlock'}
                        {$id_prefix = "invPrice{$upridSuffix}"}
                        {call mTab tabs=$app->controller->view->price_tabs tabparams=$tabparams  fieldsData=$option['prices'] id_prefix = $id_prefix}

                      {else}
                        {call attributePriceBlock data=$option['prices']  id_prefix = 'invPrice'}

                      {/if}
                    </div>
                    <script>
                        $(document).ready(function(){
                            $('#invPricePrefix-{$upridSuffix}').on('change', function(){
                                if (($(this).val()||'').indexOf('%')!=-1) {
                                    $('#tab_{$upridSuffix}-1').addClass('option-percent-price');
                                }else{
                                    $('#tab_{$upridSuffix}-1').removeClass('option-percent-price');
                                }
                            });
                        })
                    </script>
                  {/function}

                  {function attrWeightTab}
                    {$id_suffix = "`$option['products_options_id']`_`$option['products_options_values_id']`"}
                      <div class="our-pr-line after attr-virtual attr-weight-tab">
                        <input type="checkbox" id="attr_file_switch{$id_suffix}" name="attr_file_switch[{$option['products_options_id']}][{$option['products_options_values_id']}]" value="1" {if isset($option['products_file']) && $option['products_file']!=''}checked{/if} class="attr_file_switch" data-togglecss="attr-virtual{$id_suffix}">{$smarty.const.TEXT_IS_VIRTUAL}
                      </div>
                      <div class="our-pr-line after attr-virtual{$id_suffix}" {if !isset($option['products_file']) || $option['products_file']==''}style="display:none"{/if}>
                        <label>{$smarty.const.TEXT_PRODUCTS_FILE}</label>
                        <div class="attachment-upload-container-attr" data-id="{$id_suffix}">
                          <div class="upload-file-wrap">
                            <div class="upload-file-template">{$smarty.const.TEXT_DROP_FILES}<br>{$smarty.const.TEXT_OR}<br><span class="btn">{$smarty.const.IMAGE_UPLOAD}</span></div>
                            <div class="upload-file"></div>
                          </div>
                        </div>
                        <span id="upload-file-name{$id_suffix}"></span>
                        <div class="our-pr-line after">
                          {if !empty($option['products_file_url'])}<a href="{$option['products_file_url']}">{$option['products_file']}</a>{else}{$option['products_file']}{/if}<br>
                          <p class="upload-hidden"><label>{$smarty.const.TEXT_DOWNLOAD_FILE}<input class="form-control" type="text" id="attr_file{$id_suffix}" name="attr_file[{$option['products_options_id']}][{$option['products_options_values_id']}]"/></label></p>
                          <label>{$smarty.const.TEXT_PRODUCTS_MAXDAYS}<input class="form-control" type="text" name="products_attributes_maxdays[{$option['products_options_id']}][{$option['products_options_values_id']}]" value="{$option['products_attributes_maxdays']|escape:quotes}"></label>
                          <label>{$smarty.const.TEXT_PRODUCTS_MAXCOUNT}<input class="form-control" type="text" name="products_attributes_maxcount[{$option['products_options_id']}][{$option['products_options_values_id']}]" value="{$option['products_attributes_maxcount']|escape:quotes}"></label>
                          <input type="hidden" name="attr_previous_file[{$option['products_options_id']}][{$option['products_options_values_id']}]" value="{$option['products_file']|escape}">
                          <label><input type="checkbox" name="delete_attr_file[{$option['products_options_id']}][{$option['products_options_values_id']}]" value="yes">{$smarty.const.TEXT_PRODUCTS_IMAGE_REMOVE_SHORT}</label>
                        </div>
                      </div>

                      <div class="our-pr-line after attr-virtual{$id_suffix}" {if isset($option['products_file']) && $option['products_file']!=''}style="display:none"{/if}>
                        <div class="inv-weight-prefix">
                        <select name="products_attributes_weight_prefix[{$option['products_options_id']}][{$option['products_options_values_id']}]" class="form-control form-control-small">
                            <option value="+"{if $option['products_attributes_weight_prefix'] == '+'} selected{/if}>+</option>
                            <option value="-"{if $option['products_attributes_weight_prefix'] == '-'} selected{/if}>-</option>
                            <option value="+%" {if $option['products_attributes_weight_prefix']=='+%'}selected{/if}>+%</option>
                            <option value="-%" {if $option['products_attributes_weight_prefix']=='-%'}selected{/if}>-%</option>
                        </select>
                        </div>
                        <div class="inv-weight">
                          <input name="products_attributes_weight[{$option['products_options_id']}][{$option['products_options_values_id']}]" value="{$option['products_attributes_weight']}" type="text" class="form-control form-control-small" placeholder="0.00" />
                        </div>
                      </div>
                      {if $tot = \common\helpers\Extensions::isAllowed('TypicalOperatingTemp')}
                        {$tot::attributesBlock($option)}
                      {/if}
                  {/function}

                </div>
              </div>
            </div>
            <div class="popup-buttons">
              <span class="btn btn-primary btn-save2" data-upridsuffix="{*'-' is required*}-{$upridSuffix}">{$smarty.const.IMAGE_UPDATE}</span>
              <span class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</span>
            </div>
          </div>
        </div>
      </div>
      </div>
      </td>
      <td class="remove-ast" onclick="deleteSelectedAttribute(this)"></td>
  </tr>
  {/foreach}
  {if {!$isIncluded} }
  <script type="text/javascript">
  $(document).ready(function() {
    {if !defined('ADMIN_TOO_MANY_IMAGES') || (is_array($app->controller->view->images) && $app->controller->view->images|@count < intval(ADMIN_TOO_MANY_IMAGES))}
    $('.divselktr-{$option['products_options_id']}').multiselect({
        multiple: true,
        height: '205px',
        header: 'See the images in the rows below:', //2dotranslation
        noneSelectedText: 'Select',//2dotranslation
        selectedText: function(numChecked, numTotal, checkedItems){
          return numChecked + ' of ' + numTotal;//2dotranslation
        },
        selectedList: false,
        show: ['blind', 200],
        hide: ['fade', 200],
        position: {
            my: 'left top',
            at: 'left bottom'
        }
    });
    {/if}
    $('.attachment-upload-container-attr').not('.inited').each(function() {
      var _attach = $(this);
      var id = $(this).attr('data-id')
      $('.upload-file', _attach).dropzone({
        url: "{Yii::$app->urlManager->createUrl('upload/index')}",
        maxFiles: 1,
        uploadMultiple: false,
        sending:  function(e, data) {
          $('.upload-remove', _attach).on('click', function(){
            $('.dz-details', _attach).remove()
          })
        },
        dataType: 'json',
        previewTemplate: '<div class="dz-details" style="display: none;"></div>',
        drop: function(){
          $('.upload-file', _attach).html('')
        },
        success: function(e, data) {
          $('#upload-file-name' + id).text(e.name);
          $('#attr_file' + id).val(e.name);
          //console.log( e.name );
          //$('.upload-file', _this).html('');

        },
      });
      $(this).addClass('inited');
    });

  });
  </script>
  {/if}
{/function}

{if {!isset($productNewAttributeIncluded)} || {$productNewAttributeIncluded!==true} }
  {call newVirtualOptionValue 
                       options=$options
                       products_id=$products_id
                       products_options_id=$products_options_id
                       isIncluded=false }
{/if}

