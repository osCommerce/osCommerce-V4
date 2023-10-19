{use class="common\helpers\Html"}
{\backend\assets\BDTPAsset::register($this)|void}
{*
2do
- updateGross/Net price check - groups_is_tax_applicable
- check apply_groups_discount_to_specials on specials ==-2
*}
<div class="{if empty($price_tab_callback)}edp-pc-box {/if}is-product-bundle after">
  {if $pInfo->parent_products_id}
      <div class="row product-main-detail-top-switchers">
        <div class="status-left"><span>{$smarty.const.TEXT_SUB_PRODUCT_WITH_PRICE}</span> <input type="checkbox" {if $pInfo->products_id_price!=$pInfo->parent_products_id}checked="checked"{/if} data-on="{$pInfo->products_id}" data-off="{$pInfo->parent_products_id}" value="1" class="check_on_off_subprice"></div>
      </div>
  {/if}
  <div class="{if !$popup}cbox-left{/if}">
<div class="edp-our-price-box-to-remove product-price-data">
  <div class="widget widget-full box box-no-shadow">
    <div class="widget-header"><h4>{$smarty.const.TEXT_OUR_PRICE}<span class="colon">:</span></h4></div>
    <div class="widget-content price-and-cost-content">
      {foreach \common\helpers\Hooks::getList('categories/productedit', 'prices-left-top') as $filename}
        {include file=$filename}
      {/foreach}
        
      <div class="tax-cl row align-items-center" style="max-width: 500px">
          <div class="col">
              <label>{$smarty.const.TEXT_PRODUCTS_TAX_CLASS}</label>
          </div>
          <div class="col">
              {Html::dropDownList('products_tax_class_id', $pInfo->products_tax_class_id, $app->controller->view->tax_classes, ['onchange'=>'updateGrossVisible(); $(\'.js-inventory-tax-class[disabled]\').val($(this).val());',  'class'=>'form-control', 'disabled' => !empty($hideSuppliersPart)  ])}
              {if \Yii::$app->controller->action->id=='specialedit'}
                  {Html::hiddenInput('specials_id', $pInfo->specials_id|default:null)}
              {/if}
          </div>
      </div>

  {if empty($price_tab_callback)}
    {$price_tab_callback = 'productPriceBlock'}
  {else}
    {call SalesParams tabs=$app->controller->view->price_tabs tabparams=$tabparams  fieldsData=$app->controller->view->price_tabs_data  id_prefix = $id_prefix}
  {/if}
  {if !$popup}
    {$id_prefix = 'tab_1_3'}
  {else}
    {$id_prefix = 'popup_tab_1_3'}
    {* popup edit special prices*}
  {/if}

  {if isset($app->controller->view->price_tabs) && $app->controller->view->price_tabs|@count > 0 }
{* 2improve if tabs order is changed you must update the following "main" group condition:
if !\common\helpers\Extensions::isCustomerGroupsAllowed() || substr($idSuffix, -2)=='_0'
*}
    {$tabparams = $app->controller->view->price_tabparams}
    {$tabparams[count($tabparams)-1]['callback'] = $price_tab_callback}

    {call mTab tabs=$app->controller->view->price_tabs tabparams=$tabparams  fieldsData=$app->controller->view->price_tabs_data  id_prefix = $id_prefix}

  {else}
    {call $price_tab_callback data=$app->controller->view->price_tabs_data  id_prefix = $id_prefix}
  {/if}
    </div>
  </div>
</div>

{function productPriceBlock }
{* $data: [ name => val], $fieldSuffix: '[1][0]'  $idSuffix: '_1_0' *}
    <div id="group_price_container{$idSuffix}" class="js_group_price" data-base_price="{$data['base_price']|escape}" data-group_discount="{if isset($data['tabdata']['groups_discount'])}{$data['tabdata']['groups_discount']}{/if}" data-currencies-id="{if isset($data['currencies_id'])}{$data['currencies_id']}{/if}" data-base_special_price="{$data['base_specials_price']|escape}" >
{* workaround for switchers: group on/off *}
{if !\common\helpers\Extensions::isCustomerGroupsAllowed()}
  {if {$data['products_group_price']<0} }
    {$data['products_group_price']=0}
    {$data['products_group_price_gross']=0}
  {/if}
  {if {$data['products_group_special_price']<0} }
    {$data['products_group_special_price']=0}
    {$data['products_group_special_price_gross']=0}
  {/if}
{/if}
{if !$app->controller->view->useMarketPrices }
  {$data['currencies_id']=$default_currency['id']}
{/if}

{if $ext = \common\helpers\Acl::checkExtensionAllowed('ProductBundles', 'allowed')}
        <div class="edp-line products_sets_discounts">
            <label>{$smarty.const.TEXT_SET_DISCOUNT}</label>
          {if {$data['groups_id']} > 0}
            <input name="products_group_sets_discount[{$data['groups_id']}]" value="{$data['products_sets_discount']}" type="text" class="form-control form-control-small" placeholder="0.00%" />
          {else}
            <input name="products_sets_discount" value="{$pInfo->products_sets_discount}" type="text" class="form-control form-control-small" placeholder="0.00%" />
          {/if}
        </div>
{/if}

{if {$data['groups_id']}>0 }
  {if !isset($data['products_group_price']) || $data['products_group_price']==''}
    {$data['products_group_price']=-2}
  {/if}
        <div class="our-pr-line after">
          {*<div class="switch-toggle switch-3 switch-candy">*}
            <label for="popt{$idSuffix}_m2"><input type="radio" class="price-options" id="popt{$idSuffix}_m2" value="-2" {if {round($data['products_group_price'])}==-2}checked{/if} data-idSuffix="{$idSuffix}"/>{$smarty.const.TEXT_PRICE_SWITCH_MAIN_PRICE}</label>
            <label for="popt{$idSuffix}_m1"><input type="radio" class="price-options" id="popt{$idSuffix}_m1" value="1" {if {round($data['products_group_price'])}>=0}checked{/if} data-idSuffix="{$idSuffix}"/>{sprintf($smarty.const.TEXT_PRICE_SWITCH_OWN_PRICE, $data['tabdata']['title'])}</label>
            {*<label for="popt{$idSuffix}_m0"><input type="radio" class="price-options" id="popt{$idSuffix}_m0" value="-1" {if {round($data['products_group_price'])}==-1}checked{/if} data-idSuffix="{$idSuffix}"/>{sprintf($smarty.const.TEXT_PRICE_SWITCH_DISABLE, $data['tabdata']['title'])}</label>*}
          {*</div>*}
        </div>
{/if}
      <div id="div_wrap_hide{$idSuffix}" {if {round($data['products_group_price'])}==-1}style="display:none;"{/if}>
<!-- main price -->
        <div class="our-pr-line after">
          <div>
            <label>{if PRICE_WITH_BACK_TAX == 'True'}{$smarty.const.TEXT_GROSS_PRICE}{else}{$smarty.const.TEXT_NET_PRICE}{/if}</label>
            <input id="products_group_price{$idSuffix}" name="products_group_price{$fieldSuffix|escape}" value='{$data['products_group_price']|escape}' onKeyUp="updateGrossPrice(this);" data-roundTo="{$data['round_to']}" data-precision="{$smarty.const.MAX_CURRENCY_EDIT_PRECISION}" data-currency="{$data['currencies_id']}" class="js-products_group_price form-control{if (!\common\helpers\Extensions::isCustomerGroupsAllowed() || $data['groups_id']==0) && ($app->controller->view->useMarketPrices != true || $default_currency['id']==$data['currencies_id'])} default_price {/if} mask-money" {if {round($data['products_group_price'])}==-2}style="display:none;"{/if}/>
{if {$data['groups_id']}>0 }
            <span id="span_products_group_price{$idSuffix}" class="form-control-span"{if {round($data['products_group_price'])}>=0}style="display:none;"{/if}>{$currencies->formatById($data['base_price']*((100-$data['tabdata']['groups_discount'])/100), false, $data['currencies_id'])|escape}</span>
{/if}
          </div>
          <div {if PRICE_WITH_BACK_TAX == 'True'}style="display: none;"{/if}>
            <label>{$smarty.const.TEXT_GROSS_PRICE}</label>
            <input id="products_group_price_gross{$idSuffix}" value='{$data['products_group_price_gross']|escape}' onKeyUp="updateNetPrice(this);" data-currency="{$data['currencies_id']}" class="form-control mask-money"{if {round($data['products_group_price'])}==-2}style="display:none;"{/if}/>
              {if {$data['groups_id']}>0 }
                <span id="span_products_group_price_gross{$idSuffix}" class="form-control-span"{if {round($data['products_group_price'])}>=0}style="display:none;"{/if}>{$currencies->formatById($data['base_price_gross']*((100-$data['tabdata']['groups_discount'])/100), false, $data['currencies_id'])|escape}</span>
              {/if}
          </div>
        </div>
          {if (!\common\helpers\Extensions::isCustomerGroupsAllowed() || $data['groups_id']==0) && ($app->controller->view->useMarketPrices != true || $default_currency['id']==$data['currencies_id'])}
              {* supplier price is caclulated for default currency only*}
        <div class="our-pr-line dfullcheck after">
            <div class="supplier-price-cost disable-btn is-not-bundle">
                <span id="autoprice_info" style="display: none;">{$smarty.const.TEXT_PRICE_COST}: <a href="{\Yii::$app->urlManager->createUrl(['configuration/index', 'groupid' => 'BOX_CATALOG_SUPPIERS', 'row' => 1])}">{$smarty.const.SUPPLIER_PRICE_SELECTION}</a></span>
                <span id="manualprice" style="display: none;">
                     <a href="javascript:void(0)" class="btn" id="products_group_price{$idSuffix}_btn" onclick="return chooseSupplierPrice('products_group_price{$idSuffix}')" {if {round($data['products_group_price'])}==-2}style="display:none;"{/if}{if ((is_null($data['supplier_price_manual']) and SUPPLIER_UPDATE_PRICE_MODE=='Auto') or (!is_null($data['supplier_price_manual']) and $data['supplier_price_manual']==0))} disabled="disabled"{/if}>{$smarty.const.TEXT_PRICE_COST}</a>
                     <a href="javascript:void(0)" class="btn" id="products_group_price_undo_btn" onclick="return clickUndoPriceBtn(this)" style="display: none;">{$smarty.const.TEXT_SUPPLIER_PRICE_UNDO}</a>
                </span>
              <div class="pull-right">
                <label>
                  {$smarty.const.TEXT_PRICE_BASED_ON_SUPPLIER_AUTO}
                  <input type="checkbox" value="1" id="supplier_auto_price{$idSuffix}" name="supplier_auto_price{$fieldSuffix|escape}" auto-price="products_group_price{$idSuffix}" class="check_sale_prod" {if ((is_null($data['supplier_price_manual']) and SUPPLIER_UPDATE_PRICE_MODE=='Auto') or (!is_null($data['supplier_price_manual']) and $data['supplier_price_manual']==0))}  checked="checked"{/if} />
                </label>
              </div>
            </div>
        </div>
          {/if}

        <!-- specials/sales -->
        {if true}
            {if !$popup}
          <div class="sales-block " id="sales_{$data['groups_id']}_{$data['currencies_id']}">
          {if $pInfo->products_id>0}
            {$salesDetails = \common\helpers\Specials::getProductStatus($pInfo->products_id, {\common\helpers\Tax::get_tax_rate_value($pInfo->products_tax_class_id)}, $data['groups_id'], $data['currencies_id'])}
            <div class="our-pr-line after sales-block-head {if empty($salesDetails['prices'])}sales-block-head-only{/if}">
              <span class="sales-rules"><label>{$smarty.const.TEXT_SALES_RULES}<span class="colon">:</span></label>
                {if (!empty($salesDetails['id']))}
                <span class="sales-most-recent">{$smarty.const.TEXT_MOST_RECENT}<span class="colon">:</span></span>
               
                <a href="{Yii::$app->urlManager->createUrl(['specials/index-popup', 'prid' => $pInfo->products_id, '_hash_' => $data['tabdata']['html_id'] ])}" class="right-link">{$smarty.const.TEXT_MORE}</a>
                <a href="{Yii::$app->urlManager->createUrl(['specials/specialedit', 'products_id' => $pInfo->products_id, 'popup' => 1, 'popup_edit' => 1, 'id' => $salesDetails['id'], '_hash_' => $data['tabdata']['html_id']])}" class="right-link">{$smarty.const.IMAGE_EDIT}</a>
                {else}
                  <a href="{Yii::$app->urlManager->createUrl(['specials/specialedit', 'products_id' => $pInfo->products_id, 'popup' => 1, '_hash_' => $data['tabdata']['html_id']])}" class="right-link">{$smarty.const.IMAGE_ADD}</a>
                {/if}
                <span class="salas-rules-current">{$salesDetails['description']}</span>
              </span>

              {*if !empty($salesDetails)}{else}{$smarty.const.TEXT_ADD}{/if*}
            </div>
                {if !empty($salesDetails['total_qty']) || !empty($salesDetails['max_per_order'])}
            <div class="our-pr-line after">
                <span class="sales-most-recent">{$smarty.const.TEXT_QTY_LIMITS}<span class="colon">:</span></span>
            <span class="sold {if !empty($salesDetails['total_qty']) && $salesDetails['total_qty']<=$salesDetails['sold']}sold-out red{/if}">
                  {$salesDetails['total_qty']} <span title="{$smarty.const.TABLE_HEADING_PRODUCTS_SOLD|escape}" style="cursor:pointer" class="sold {if $salesDetails['total_qty']<=$salesDetails['sold']}sold-out red{/if}">({$salesDetails['sold']})</span> / {$salesDetails['max_per_order']}
              </span>
            </div>
                {/if}
            {if !empty($salesDetails['prices']['text'])}
            <div class="our-pr-line after">
              <div>
                {if (!empty($salesDetails['start_date']))}
                <label class="sale-date start">{$smarty.const.TEXT_START_DATE}<span class="colon">:</span></label>
                <span class="form-control-span">{$salesDetails['start_date']}</span>
                {/if}
              </div>
              <div>
                {if (!empty($salesDetails['expires_date']))}
                <label class="sale-date ">{$smarty.const.TEXT_EXPIRY_DATE}<span class="colon">:</span></label>
                <span class="form-control-span">{$salesDetails['expires_date']}</span>
                {/if}
              </div>
            </div>
            {if (!empty($salesDetails['prices']))}
            <div class="our-pr-line after">
              <div>
                <label class="sale-info-n">{$smarty.const.TEXT_SALE}<span class="colon">:</span></label>
                <span class="form-control-span">{$salesDetails['prices']['text']}</span>
              </div>
              <div>
                <label class="sale-info-n">{$smarty.const.TEXT_SALE_GROSS}<span class="colon">:</span></label>
                <span class="form-control-span">{$salesDetails['prices']['text_inc']}</span>
              </div>
            </div>
            {/if}
            {/if}
          {/if}
          </div>
          {/if}
        {else}{* old code*}
{if !isset($data['specials_disabled'])}
  {$data['specials_disabled']=-1}
{/if}
        <div class="our-pr-line after our-pr-line-check-box dfullcheck sale_to_dis {if $data['specials_disabled']>0 }dis_module{/if}">
          <div class="{if ($default_currency['id']!=$data['currencies_id']) }market_sales_switch{/if}" {if ($default_currency['id']!=$data['currencies_id']) }style="display:none;"{/if}>
            {if !\common\helpers\Extensions::isCustomerGroupsAllowed() || $data['groups_id']==0 }
              {if !\common\helpers\Extensions::isCustomerGroupsAllowed() }
                {$dataToSwitch=$idSuffix}
              {else}
                {$dataToSwitch=substr($idSuffix, 0, -2)}
              {/if}
              <label>
              {if $data['specials_disabled'] < 0}
                {$smarty.const.TEXT_ENABLE_SALE}<span class="colon">:</span>
              {else}
                {$smarty.const.BOX_CATALOG_SPECIALS}<span class="colon">:</span>
              {/if}
              </label>
                <span class="sales-state-label">
{if $data['sales_status'] > 0}
  {$smarty.const.TEXT_ACTIVE}
{elseif $data['specials_scheduled'] && $data['specials_disabled']==0}
  {$smarty.const.TEXT_SCHEDULED}
{elseif $data['specials_disabled']>0}
  <strong>{$smarty.const.TEXT_DISABLED}</strong>
{/if}
              </span>
              {if $data['specials_disabled']==-1}
              <input type="checkbox" value="1" id="special_status{$idSuffix}" data-toswitch="{if ($default_currency['id']==$data['currencies_id'])}market_sales_switch,{/if}div_sale_prod{$dataToSwitch}" name="special_status{$fieldSuffix|escape}" class="check_sale_prod" {if {$data['sales_status'] > 0}} checked="checked" {/if} data-defaults-set="special_price{$idSuffix},special_price_gross{$idSuffix}" data-defaults-on="0" data-defaults-off="-1"/>

              {else}
                <br>
                <input type="radio" value="0" id="r1_special_status{$idSuffix}" data-toswitch="{if ($default_currency['id']==$data['currencies_id'])}market_sales_switch,{/if}div_sale_prod{$dataToSwitch}" name="special_status{$fieldSuffix|escape}" class="r_check_sale_prod" data-defaults-set="special_price{$idSuffix},special_price_gross{$idSuffix}" data-defaults-on="0" data-defaults-off="-1"/>{$smarty.const.IMAGE_DELETE}

              <input type="radio" value="-1" id="r2_special_status{$idSuffix}" data-toswitch="{if ($default_currency['id']==$data['currencies_id'])}market_sales_switch,{/if}div_sale_prod{$dataToSwitch}" name="special_status{$fieldSuffix|escape}" class="r_check_sale_prod" {if $data['specials_disabled'] > 0} checked="checked" {/if} data-defaults-set="special_price{$idSuffix},special_price_gross{$idSuffix}" data-defaults-on="0" data-defaults-off="-1"/>{$smarty.const.TEXT_DISABLE}

              <input type="radio" value="2" id="r3_special_status{$idSuffix}" data-toswitch="{if ($default_currency['id']==$data['currencies_id'])}market_sales_switch,{/if}div_sale_prod{$dataToSwitch}" name="special_status{$fieldSuffix|escape}" class="r_check_sale_prod" {if $data['specials_scheduled'] && $data['specials_disabled']==0} checked="checked" {/if} data-defaults-set="special_price{$idSuffix},special_price_gross{$idSuffix}" data-defaults-on="0" data-defaults-off="-1"/>{$smarty.const.TEXT_SCHEDULE}

              <input type="radio" value="1" id="r4_special_status{$idSuffix}" data-toswitch="{if ($default_currency['id']==$data['currencies_id'])}market_sales_switch,{/if}div_sale_prod{$dataToSwitch}" name="special_status{$fieldSuffix|escape}" class="r_check_sale_prod" {if {$data['sales_status'] > 0}} checked="checked" {/if} data-defaults-set="special_price{$idSuffix},special_price_gross{$idSuffix}" data-defaults-on="0" data-defaults-off="-1"/>{$smarty.const.IMAGE_ACTIVATE}
              {/if}
            {/if}

{if !isset($data['products_group_special_price']) || $data['products_group_special_price']==''}
  {if {$data['groups_id']}>0 }
    {$data['products_group_special_price']='-2'}
  {else}
    {$data['products_group_special_price']='0'}
  {/if}
  {$showSalesDiv=0}
{else}
  {$showSalesDiv=1}
{/if}
{if $data['groups_id']>0 }
        <div class="our-pr-line after div_sale_prod div_sale_prod{$idSuffix}" {if ($showSalesDiv==0)}style="display:none;"{/if}>
          <label>{$smarty.const.TEXT_ENABLE_SALE}</label>
          {*<div class="switch-toggle switch-3 switch-candy">*}
            <label for="popt{$idSuffix}_s2"><input type="radio" class="price-options" id="popt{$idSuffix}_s2" value="-2" {if $data['products_group_special_price']=='-2'}checked{/if} data-idSuffix="{$idSuffix}"/>{$smarty.const.TEXT_PRICE_SWITCH_MAIN_PRICE}</label>
            <label for="popt{$idSuffix}_s1"><input type="radio" class="price-options" id="popt{$idSuffix}_s1" value="1" {if {round($data['products_group_special_price'])}>=0}checked{/if} data-idSuffix="{$idSuffix}"/>{sprintf($smarty.const.TEXT_PRICE_SWITCH_OWN_PRICE, $data['tabdata']['title'])}</label>
            <label for="popt{$idSuffix}_s0"><input type="radio" class="price-options" id="popt{$idSuffix}_s0" value="-1" {if $data['products_group_special_price']=='-1'}checked{/if} data-idSuffix="{$idSuffix}"/>{sprintf($smarty.const.TEXT_PRICE_SWITCH_DISABLE, $data['tabdata']['title'])}</label>
          {*</div>*}
        </div>
{/if}
          </div>
        </div>
        <div class="{if ($default_currency['id']!=$data['currencies_id']) }market_sales_switch{/if} sale_to_dis {if $data['specials_disabled']>0 }dis_module{/if}" {if ($default_currency['id']!=$data['currencies_id'] && $data['sales_status']!=1) }style="display:none;"{/if}>
        <div id="div_sale_prod{$idSuffix}" class="sale-prod-line-block after div_sale_prod div_sale_prod{$idSuffix}" {if ($showSalesDiv==0 || $data['products_group_special_price']==-1)}style="display:none;"{/if}>
          <div class="_sale-prod-line our-pr-line">
          {if (!\common\helpers\Extensions::isCustomerGroupsAllowed() || $data['groups_id']==0) && ($app->controller->view->useMarketPrices != true || $default_currency['id']==$data['currencies_id'])}
            <div class="_disable-btn">
              <label>{$smarty.const.TEXT_START_DATE}</label>
              <input id="special_start_date{$idSuffix}" {*if {$data['sales_status'] > 0}}readonly="readonly"{/if*} name="special_start_date{$fieldSuffix|escape}" value='{\common\helpers\Date::datepicker_date_time($data['start_date'])}' class="tldatetimepicker form-control"/>
            </div>
            <div class="_disable-btn">
              <label>{$smarty.const.TEXT_EXPIRY_DATE}</label>
              <input id="special_expires_date{$idSuffix}" name="special_expires_date{$fieldSuffix|escape}" value='{\common\helpers\Date::datepicker_date_time($data['expires_date'])}' class="tldatetimepicker form-control form-control-small"/>
            </div>
          {/if}
          </div>
          <div class="_sale-prod-line our-pr-line">
          <div>
            <label class="sale-info">{$smarty.const.TEXT_SALE}<span class="colon">:</span></label>
            <input id="special_price{$idSuffix}" data-idsuffix="{$idSuffix}" name="special_price{$fieldSuffix|escape}" value='{if $data['products_group_special_price']>0.001}{$data['products_group_special_price']|escape}{/if}' onKeyUp="updateGrossPrice(this);" data-roundTo="{$data['round_to']}" class="form-control mask-money" {if $data['groups_id']>0 && round($data['products_group_special_price'])==-2}style="display:none;"{/if} data-precision="{$smarty.const.MAX_CURRENCY_EDIT_PRECISION}"/>
{if $data['groups_id']>0 }
            <span id="span_special_price{$idSuffix}" class="form-control-span"{if round($data['products_group_specials_price'])>=0}style="display:none;"{/if}>{$currencies->formatById($data['base_specials_price']*((100-$data['tabdata']['groups_discount'])/100), false, $data['currencies_id'])|escape}</span>
{/if}
          </div>
          <div>
            <label class="sale-info">{$smarty.const.TEXT_SALE_GROSS}<span class="colon">:</span></label>
            <input id="special_price_gross{$idSuffix}" data-idsuffix="{$idSuffix}" value='{if $data['products_group_special_price_gross']>0}{$data['products_group_special_price_gross']|escape}{/if}' onKeyUp="updateNetPrice(this);" class="form-control mask-money" {if $data['groups_id']>0 && round($data['products_group_special_price'])==-2}style="display:none;"{/if}/>
{if $data['groups_id']>0 }
            <span id="span_special_price_gross{$idSuffix}" class="form-control-span" {if {round($data['products_group_specials_price'])}>=0}style="display:none;"{/if}>{$currencies->formatById($data['base_specials_price_gross']*((100-$data['tabdata']['groups_discount'])/100), false, $data['currencies_id'])|escape}</span>
{/if}
          </div>
          </div>
        </div>
        </div>
          {/if}
<!-- gift wrap  -->
        <div class="our-pr-line after our-pr-line-check-box dfullcheck">
          <div>
            <label>{$smarty.const.TEXT_GIVE_WRAP}</label>
              <input type="checkbox" value="1"  id="gift_wrap{$idSuffix}" name="gift_wrap{$fieldSuffix|escape}" class="check_gift_wrap" {if {$data['gift_wrap_id'] > 0}} checked="checked" {/if} />
          </div>
        </div>
        <div class="our-pr-line after div_gift_wrap" id="div_gift_wrap{$idSuffix}" {if not {$data['gift_wrap_id'] > 0}} style="display:none;" {/if}>
          <div>
            <label>{$smarty.const.TEXT_NET_PRICE}</label>
            <input id="gift_wrap_price{$idSuffix}" name="gift_wrap_price{$fieldSuffix|escape}" value='{$data['gift_wrap_price']|escape}' onKeyUp="updateGrossPrice(this);" data-roundTo="{$data['round_to']}" class="form-control"/>
          </div>
          <div class="disable-btn">
            <label>{$smarty.const.TEXT_GROSS_PRICE}</label>
            <input id="gift_wrap_price_gross{$idSuffix}" value='{$data['gift_wrap_price_gross']|escape}' onKeyUp="updateNetPrice(this);" class="form-control"/>
          </div>
        </div>
{if \common\helpers\Acl::checkExtensionAllowed('DeliveryOptions', 'allowed')} {* todo: move to hook 'categories/productedit', 'prices-priceblock-bottom' *}
        <div class="our-pr-line after our-pr-line-check-box dfullcheck">
          <div>
            <label>{$smarty.const.EXT_DELIVERY_OPTIONS_TEXT_ALLOW_DELIVERY_OPTIONS}</label>
              <input type="checkbox" value="1"  id="delivery_option{$idSuffix}" name="delivery_option{$fieldSuffix|escape}" class="check_delivery_option" {if {$data['delivery_option'] > 0}} checked="checked" {/if} />
          </div>
        </div>
{/if}
<!-- shipping surcharge  -->
        <div class="our-pr-line after our-pr-line-check-box dfullcheck">
          <div>
            <label>{$smarty.const.TEXT_SHIPPING_SURCHARGE}</label>
              <input type="checkbox" value="1"  id="shipping_surcharge{$idSuffix}" name="shipping_surcharge{$fieldSuffix|escape}" class="check_shipping_surcharge" {if {$data['shipping_surcharge_price'] > 0}} checked="checked" {/if} />
          </div>
        </div>
        <div class="our-pr-line after div_shipping_surcharge" id="div_shipping_surcharge{$idSuffix}" {if not {$data['shipping_surcharge_price'] > 0}} style="display:none;" {/if}>
          <div>
            <label>{$smarty.const.TEXT_NET_PRICE}</label>
            <input id="shipping_surcharge_price{$idSuffix}" name="shipping_surcharge_price{$fieldSuffix|escape}" value='{$data['shipping_surcharge_price']|escape}' onKeyUp="updateGrossPrice(this);" data-roundTo="{$data['round_to']}" class="form-control"/>
          </div>
          <div class="disable-btn">
            <label>{$smarty.const.TEXT_GROSS_PRICE}</label>
            <input id="shipping_surcharge_price_gross{$idSuffix}" value='{$data['shipping_surcharge_price_gross']|escape}' onKeyUp="updateNetPrice(this);" class="form-control"/>
          </div>
        </div>
<!-- q-ty discount -->
        <div class="our-pr-line after our-pr-line-check-box dfullcheck">
          <div>
            <label>{$smarty.const.TEXT_QUANTITY_DISCOUNT}</label>
            {* always - else imposible to set up per group without discount to all if !\common\helpers\Extensions::isCustomerGroupsAllowed() || substr($idSuffix, -2)=='_0'*}
              {*{$dataToSwitch=$idSuffix}  inventory *}
              <input type="checkbox" value="1" name="qty_discount_status{$fieldSuffix|escape}" data-toswitch="prod_qty_discount{$idSuffix}" class="check_qty_discount_prod" id="check_qty_discount_prod{$idSuffix}" {if isset($data['qty_discounts']) && $data['qty_discounts']|@count > 0} checked="checked" {/if} />
            {*/if*}
          </div>
        </div>
        <div id="hide_wrap_price_qty_discount{$idSuffix}" class="prod_qty_discount{$idSuffix}" {if !isset($data['qty_discounts']) || $data['qty_discounts']|@count==0 }style="display:none;"{/if}>
          <div id="wrap_price_qty_discount{$idSuffix}" class="wrap-quant-discount">
              {if isset($data['qty_discounts'])}
              {foreach $data['qty_discounts'] as $qty => $prices}
                {call productQtyDiscountRow }
              {/foreach}
              {/if}
          </div>
          <div class="quant-discount-btn div_qty_discount_prod">
            <span class="btn btn-add-more" id="prod_qty_discount{$idSuffix}" data-idSuffix="{$idSuffix}" data-fieldSuffix="{$fieldSuffix|escape}" data-callback="addQtyDiscountRow">{$smarty.const.TEXT_AND_MORE}</span>
          </div>
        </div>
      </div>
    </div>
    {foreach \common\helpers\Hooks::getList('categories/productedit', 'prices-priceblock-bottom') as $filename}
        {include file=$filename}
    {/foreach}
    <!-- disable any promo/discounts-->
    {if (!\common\helpers\Extensions::isCustomerGroupsAllowed() || $data['groups_id']==0)
        && ($app->controller->view->useMarketPrices != true || $default_currency['id']==$data['currencies_id'])
        && !$popup}
    <div class="our-pr-line after our-pr-line-check-box dfullcheck">
      <div>
        <label>{$smarty.const.TEXT_DISABLE_ANY_PROMO_DISCOUNTS}</label>
          <input type="checkbox" value="1"  id="disable_discount" name="disable_discount" class="check_disable_discount" {if $pInfo->disable_discount > 0} checked="checked" {/if} />
      </div>
    </div>
    {/if}
{/function}

{function  productQtyDiscountRow}{strip}
              <div class="quant-discount-line after div_qty_discount_prod">
                <div>
                  <label>{$smarty.const.TEXT_PRODUCTS_QUANTITY_INFO}</label>
                  <input id="discount_qty{$idSuffix}_{if isset($prices@iteration)}{$prices@iteration}{/if}" name="discount_qty{$fieldSuffix|escape}[{if isset($price@index)}{$price@index}{/if}]" value="{if isset($qty)}{$qty}{/if}" {if \common\helpers\Extensions::isAllowed('Inventory')}onchange="updateInventoryBox(this);"{/if} class="form-control"/>
                </div><div>
                  <label>{$smarty.const.TEXT_NET}</label>
                  <input id="discount_price{$idSuffix}_{if isset($prices@iteration)}{$prices@iteration}{/if}" name="discount_price{$fieldSuffix|escape}[{if isset($price@index)}{$price@index}{/if}]" value="{if isset($prices['price'])}{$prices['price']}{/if}" onKeyUp="updateGrossPrice(this);" data-roundTo="{if isset($data['round_to'])}{$data['round_to']}{/if}" class="form-control"/>
                </div><div>
                  <label>{$smarty.const.TEXT_GROSS}</label>
                  <input id="discount_price_gross{$idSuffix}_{if isset($prices@iteration)}{$prices@iteration}{/if}" value="{if isset($prices['price_gross'])}{$prices['price_gross']}{/if}" onKeyUp="updateNetPrice(this);" class="form-control"/>
                </div>
                <span class="rem-quan-line"></span>
              </div>
{/strip}{/function}


  </div>
{if !$hideSuppliersPart && $TabAccess->allowSuppliersData()}
  <div class="cbox-right is-not-bundle">
  <div class="tax-cl">
        <label>{$smarty.const.TEXT_RRP}<span class="colon">:</span></label>
          {Html::input('text', 'products_price_rrp', $pInfo->products_price_rrp, ['class'=>'form-control mask-money'])}
      </div>
    <div class="widget box box-no-shadow {if $pInfo->parent_products_id}disabled_block{/if}" style="margin-bottom: 0;">
      <div class="widget-header"><h4>{$smarty.const.TEXT_SUPPLIER_COST}</h4>
        <div class="edp-line">
                <span class="edp-qty-t" style="display:none;">{$smarty.const.TEXT_APPLICABLE}</b></span>
            </div>
          <span id="supplier-default-sort-holder" style="display: block; float: right; text-align: right;"><label>{$smarty.const.TEXT_SUPPLIER_SORT_HEADER} {Html::checkbox('suppliers-default-sort', {$pInfo->supplier_default_sort}, ['class' => 'check_on_off'])}</label></span>
      </div>
      <div class="widget-content edp-qty-update">
        {include file="supplierproduct-list.tpl"}
        <div class="ed-sup-btn-box">
          <a href="{Yii::$app->urlManager->createUrl(['categories/supplier-select', 'uprid' => $pInfo->products_id])}" class="btn select_supplier">{$smarty.const.TEXT_SELECT_ADD_SUPPLIER}</a>
        </div>
      </div>
    </div>
  </div>
{/if}
{if !$hideSuppliersPart && $pInfo->parent_products_id==0}
{if \common\helpers\Acl::checkExtensionAllowed('ProductBundles', 'allowed') && \common\helpers\Acl::rule(['TABLE_HEADING_PRODUCTS', 'IMAGE_EDIT', 'TAB_BUNDLES'])}
  <div class="cbox-right is-bundle">
    <div class="widget box box-no-shadow" style="margin-bottom: 0;">
      <div class="widget-header"><h4>{$smarty.const.TAB_BUNDLES}</h4></div>
      <div class="widget-content" id="bundles-placeholder">
          {\common\extensions\ProductBundles\ProductBundles::productBlock($pInfo)}
      </div>
    </div>
  </div>
{/if}
{/if}
</div>

<script type="text/javascript">
  {$idSuffix=''}
  {if \common\helpers\Extensions::isCustomerGroupsAllowed()}{$idSuffix="`$idSuffix`_0"}{/if}
  {if $app->controller->view->useMarketPrices }{$idSuffix="`$idSuffix`_0"}{/if} {*2check*}
              
  var rSaleOn = 1, rSavedSaleStart = '';
  function rPriceSwitch (event) {
    var switchSale = false;
    if ($(event.target).val()==0) {
      if (rSaleOn==1) switchSale = true;
      rSaleOn = 0;
    } else {
      if (rSaleOn==0) switchSale = true;
      rSaleOn = 1;
      if ($(event.target).val()==-1) {
        $('.sale_to_dis').addClass('dis_module');
      } else {
        $('.sale_to_dis').removeClass('dis_module');
      }
      /* some logic about today/empty start date if new sale is enabled (not required with force flags)
      if ($('#special_start_date{$idSuffix}').val() != '') {
        rSavedSaleStart = $('#special_start_date{$idSuffix}').val();
      }
      if ($(event.target).val()==1) {
        $('#special_start_date{$idSuffix}').prop('readonly', true);
        $('#special_start_date{$idSuffix}').val('');
      } else {
        $('#special_start_date{$idSuffix}').prop('readonly', false);
        if (rSavedSaleStart != '') {
          $('#special_start_date{$idSuffix}').val(rSavedSaleStart);
        }
      }
      */
    }

    if (switchSale) {
      $(event.target).trigger('vswitch', rSaleOn);
    }
  }

function bsPriceSwitch (element, argument) {
                        var t = $(this).attr('data-toswitch');
                        if (typeof(t) != 'undefined') { //all divs, css class of which is starting with t
                          tmp = t.split(",");
                          if (tmp.length>1) {
                            sel = '[class*="' + tmp.join('"], [class*="') + '"]';
                          }else {
                            sel = '[class*="' + t +'"]';
                          }
                        } else {
                          sel = '#div_' + $(this).attr('id');
                        }
                        var setDefaults = $(this).attr('data-defaults-set');
                        if (argument) {
                          $(sel).show();
                          if (typeof(setDefaults) != 'undefined') {
                            var defval=$(this).attr('data-defaults-on');
                          }
                        } else {
                          $(sel).hide();
                          if (typeof(setDefaults) != 'undefined') {
                            var defval=$(this).attr('data-defaults-off');
                          }
                        }
                        if (typeof(setDefaults) != 'undefined' && typeof(defval) != 'undefined') {
                          $(setDefaults.split(',')).each(function() {
                            $('#'+this).val(defval);
                          });
                        }
                        var autoPrice = $(this).attr('auto-price');
                        if (typeof(autoPrice) != 'undefined') {
                            if (argument) {
                                $('#' + autoPrice +'_btn').attr('disabled', 'disabled');
                                chooseSupplierAutoPrice(autoPrice);
                            } else {
                                $('#' + autoPrice +'_btn').removeAttr('disabled');
                            }
                            $(document).trigger('supplier-autoprice:changed', [argument]);
                        }
                        return true;
                      };

var bsPriceParams = {
                      onSwitchChange: bsPriceSwitch,
                      onText: "{$smarty.const.SW_ON|escape:'javascript'}",
                      offText: "{$smarty.const.SW_OFF|escape:'javascript'}",
                      handleWidth: '20px',
                      labelWidth: '24px'
                    };

                
                    
          $(document).ready(function() {
              $('.nav-tabs-left-scroll').slimScroll({
                  height: '100%',
                  scrollTo: '0px'
              })
/*
            $('#special_start_date{$idSuffix}').tlDatetimepicker();
            $('#special_expires_date{$idSuffix}').tlDatetimepicker();
            
            $('#special_start_date{$idSuffix}').on("dp.change", function (e) {
                $('#special_expires_date{$idSuffix}').data("DateTimePicker").minDate(e.date);
            });
            $('#special_expires_date{$idSuffix}').on("dp.change", function (e) {
                $('#special_start_date{$idSuffix}').data("DateTimePicker").maxDate(e.date);
            });
*/
 
            $('.btn-add-more').click(addQtyDiscountRow);
            $('.rem-quan-line').click(function() {
              $(this).parent().remove();
            //2do  updateInventoryBox();
            });
            /// if group == 0 update all
            $('input.default_price[name^="products_group_price"]').on('change', updateGrossVisible);
              $('.check_sale_prod, .check_points_prod, .check_qty_discount_prod, .check_gift_wrap, .check_shipping_surcharge, .check_disable_discount, .check_delivery_option').tlSwitch(bsPriceParams);
            $('.r_check_sale_prod').on('click', rPriceSwitch);
            $('.r_check_sale_prod').on('vswitch', bsPriceSwitch);

              /// late/lazy update gross price (only on visible tabs)
            $('ul[id^={$id_prefix}] [data-bs-toggle="tab"]').on('shown.bs.tab', function () {
              // 2do update gross price inputs
              updateVisibleGrossInputs($($(this).attr('href')));
              // init new visible bootstrapSwitch
              tab = $($(this).attr('href')).not(".inited");
              if (tab.length) {
                tab.addClass('inited');

                $('.r_check_sale_prod', tab).on('click', rPriceSwitch);
                $('.r_check_sale_prod', tab).on('vswitch', bsPriceSwitch);
              }
            });

            $('.select_supplier').popUp({
              box: "<div class='popup-box-wrap'><div class='around-pop-up'></div><div class='popup-box popupEditCat'><div class='pop-up-close'></div><div class='popup-heading cat-head'>{$smarty.const.TEXT_SELECT_ADD_SUPPLIER}</div><div class='pop-up-content'><div class='preloader'></div></div></div></div>"
            });

            $('input.price-options').off('click').on('click', priceOptionsClick);

            $('.js_group_price').on('change_state',function(event, state){
              //deprecated
              //!!! was recalculate prices (gross/net) for each group js_group_price => div includes base or special prices (switcher + inputs)
              //for particular group+currency  ;)
              //
              var $block = $(this);
              var $all_input = $block.find('[name^="products_groups_prices_"]');
              var base_ref = '#base_price';
              if ( $all_input.length==0 ) {
                $all_input = $block.find('[name^="special_groups_prices_"]');
                base_ref = '#base_sale_price';
              }
              var $main_input = $block.find('.js_price_input');
              //
              var base_val = parseFloat($(base_ref).val()) || 0;
              if ( base_ref=='#base_sale_price' && $(base_ref).val().indexOf('%')!==-1 ) {
                var main_price = $('#base_price').val(),
                        base_percent = parseFloat($(base_ref).val().substring(0,$(base_ref).val().indexOf('%')));
                base_val = main_price - ((base_percent/100)*main_price);
              }
              var new_val = ((100-parseFloat($block.attr('data-group_discount')))/100*base_val);

              //dependant block (specials, points, q-ty discount
              var $dep_block = $block.closest('.tab-pane').find('.js_price_dep');

              if (base_ref == '#base_sale_price') $dep_block = $([]);
              if ( parseFloat(state)==-1 ) {
                $all_input.removeAttr('readonly');
                $all_input.removeAttr('disabled');
                $main_input.val('-1');
                $block.find('.js_price_block').hide();
                $dep_block.hide();
              }else if(parseFloat(state)==-2){
                if ( $dep_block.is(':hidden') ) $dep_block.show();
                $all_input.removeAttr('readonly');
                $all_input.removeAttr('disabled');
                $main_input.val(new_val);
                $main_input.trigger('keyup');
                $all_input.attr({ readonly:'readonly',disabled:'disabled' });
                $block.find('.js_price_block').show();
              }else{
                if ( $dep_block.is(':hidden') ) $dep_block.show();
                $all_input.removeAttr('readonly');
                $all_input.removeAttr('disabled');
                if ( parseFloat($main_input.val())<=0 ) {
                  $main_input.val(new_val);
                  $main_input.trigger('keyup');
                }
                $block.find('.js_price_block').show();
              }
            });

            $('.js_group_price [name^="popt_"]').on('click',function(){
              $(this).parents('.js_group_price').trigger('change_state',[$(this).val()]);
              if ( parseFloat($(this).val()) ==-1) {
                $('.js_group_price').find('[name^="s'+this.name+'"]').filter('[value="-1"]').trigger('click');
              }
            });
            $('.js_group_price [name^="spopt_"]').on('click',function(){
              $(this).parents('.js_group_price').trigger('change_state',[$(this).val()]);
            });
            // init on load - moved to server part
            /*
            $('.js_group_price').each(function(){
              var $main_input = $(this).find('.js_price_input');
              var switch_name_locate = ($main_input.length>0 && $main_input[0].name.indexOf('special_groups_prices_')===0)?'spopt_':'popt_';
              var price = parseFloat($main_input.val());
              if (price==-1) {
                $(this).find('[name^="'+switch_name_locate+'"]').filter('[value="-1"]').trigger('click');
              }else if (price==-2) {
                $(this).find('[name^="'+switch_name_locate+'"]').filter('[value="-2"]').trigger('click');
              }else {
                $(this).find('[name^="'+switch_name_locate+'"]').filter('[value="1"]').trigger('click');
              }
              //$(this).trigger('change_state',[]);
            });*/
            $('#base_price').on('change',function(){
              //update group prices (discount based)
              $('.js_group_price [name^="popt_"]').filter('[value="-2"]').trigger('click');
              //update inventory prices
              updateAllPrices();
            });
            $('#base_sale_price').on('change',function(){
              //update group sale/special prices (discount based)
              $('.js_group_price [name^="spopt_"]').filter('[value="-2"]').trigger('click');
            });

            $(document).on('supplier-autoprice:changed', function (event, argument) {
                $('#autoprice_info').toggle(argument);
                $('#manualprice').toggle(!argument);
                // Net and Gross price edits:
                $('#products_group_price_0').prop('disabled', Boolean(argument));
                $('#products_group_price_gross_0').attr('disabled', Boolean(argument));
            });
            let arg = Boolean({((is_null($pInfo->supplier_price_manual) and SUPPLIER_UPDATE_PRICE_MODE=='Auto') or (!is_null($pInfo->supplier_price_manual) and $pInfo->supplier_price_manual==0))});
            $(document).trigger('supplier-autoprice:changed', [arg]);
          });
        </script>
<script>
    function addQtyDiscountRow() {
        var idSuffix = $(this).attr('data-idSuffix');
        var fieldSuffix = $(this).attr('data-fieldSuffix');
        var num;
        tmp = $('#wrap_price_qty_discount' + idSuffix + ' .quant-discount-line:last input:first').attr('id');
        if (typeof tmp !== 'undefined') {
            num = 1+parseInt(tmp.split('_').pop());
        } else {
            num = 0;
        }


        {call productQtyDiscountRow assign="js_row" data=false fieldSuffix='_fieldSuffix' idSuffix='_idSuffix'}
        var product_discount_row = '{$js_row|escape:quotes}';
        $('#wrap_price_qty_discount' + idSuffix).append(
            product_discount_row.replace(/_idSuffix_/g, idSuffix + '_' + num).replace(/_fieldSuffix\[\]/g, fieldSuffix + '[' + num + ']')
        ).show();
        $('#wrap_price_qty_discount' + idSuffix + ' .rem-quan-line').off('click').click(function() {
            ($(this).parent()).remove();
        });
        return false;
    }
</script>
{if !$hideSuppliersPart}
<div id="supplierProductDetailEdit" class="hidden js-SupplierExtraDataPopup popup-box-wrap-page">
  <div class="around-pop-up-page"></div>
  <div class='popup-box-page'>
    <div class='pop-up-close-page'></div><div class='popup-heading cat-head'>{$smarty.const.TEXT_SUPPLIERS_PRODUCT_DETAILS}</div>
    <div class='pop-up-content-page'>
      <div class="popup-content bind-edit">
      </div>
      <div class="popup-buttons">
        <div class="btn-toolbar">
          <div class="pull-left">
            <button type="button" class="btn js-extra-close-button">{$smarty.const.TEXT_CLOSE}</button>
          </div>
          <div class="text-right">
            <button type="button" class="btn btn-primary js-extra-update-button">{$smarty.const.TEXT_UPDATE}</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
{/if}
<!-- edit dialog for supplier landed price -->
<div class="modal fade" id="editLandedPrice" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="vertical-alignment-helper">
        <div class="modal-dialog vertical-align-center modal-little">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{$smarty.const.TEXT_SUPPLIERS_EDIT_LANDED_PRICE}</h5>
                </div>
                <div class="modal-body">
                    <label class="modal-block">{Html::radio('modal-selected-type', true, ['value' => 'calculate'])} {$smarty.const.TEXT_SUPPLIER_LANDED_PRICE_CALC} <span id="modal-calculated-landed-price">10</span></label>
                    <label class="modal-block">{Html::radio('modal-selected-type', false, ['value' => 'manually'])} {$smarty.const.TEXT_SUPPLIER_LANDED_PRICE_MANUAL} </label>
                    {Html::textInput('modal-manually-landed-price', null, ['size' => 10, 'style' => 'width: 100px; margin-left: 17px'])}

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{$smarty.const.TEXT_CLOSE}</button>
                    <button type="button" class="btn btn-primary save-changes" data-dismiss="modal">{$smarty.const.IMAGE_SAVE}</button>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
    .vertical-alignment-helper {
        display:table;
        height: 100%;
        width: 100%;
        pointer-events:none;
    }
    .vertical-align-center {
        display: table-cell;
        vertical-align: middle;
        pointer-events:none;
    }
    .modal-content {
        width:inherit;
        max-width:inherit;
        height:inherit;
        margin: 0 auto;
        pointer-events:all;
    }
    .modal-little {
        max-width: 300px;
    }
    .modal-block {
        display: block;
    }

    /* supplier sort buttons */
    .move-up-down-btns {
        margin-top: -5px;
        width: 30px;
        float: left;
        font-size: small;
    }

    .move-up-down-btns button{
        border: 0px;
        color: black;
        padding: 0px 0px;
        cursor: pointer;
        display: block;
        background: none;
    }

    .move-up-down-btns button:disabled{
        color: gray;
        cursor: auto;
    }

    .move-up-down-btns button.btn-up:before {
        content: "\25b2";
    }
    .move-up-down-btns button.btn-down:before {
        content: "\25bc";
    }

    /* other */
    .overridden-price {
        font-weight: bold;
    }
    .js-overridden-mark {
        font-size: small;
        font-weight: bold;
    }
</style>
