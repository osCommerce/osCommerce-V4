{\backend\assets\MultiSelectAsset::register($this)|void}
{use class="\common\classes\platform"}
{use class="\common\helpers\Html"}
{$platforms = platform::getList(false)}
<style type="text/css">
    .col-md-1, .col-md-2, .col-md-3, .col-md-4, .col-md-5, .col-md-6, .col-md-7, .col-md-8, .col-md-9, .col-md-10, .col-md-11, .col-md-12 {
        padding-left: 5px;
        padding-right: 5px;
    }
    #save_voucher_form textarea {
        min-height: 70px;
        font-weight: normal;
        font-size: 11px;
        background: #fff!important;
        color: #333!important;
    }
</style>

<div id="voucher_management_data">
<form id="save_voucher_form" name="new_voucher" onSubmit="return saveVoucher();">
<div class="popupCategory">
    <div class="tabbable tabbable-custom">
        <ul class="nav nav-tabs top_tabs_ul">
            <li class="active" data-bs-toggle="tab" data-bs-target="#tab_3"><a>{$smarty.const.TEXT_MAIN_DETAILS}</a></li>
            <li data-bs-toggle="tab" data-bs-target="#tab_2"><a>{$smarty.const.TEXT_NAME_DESCRIPTION}</a></li>
            {if platform::isMulti()}
            <li data-bs-toggle="tab" data-bs-target="#tab_4"><a>{$smarty.const.TEXT_COMMON_PLATFORM_TAB}</a></li>
            {/if}
        </ul>
        <div class="tab-content">
            <div class="tab-pane topTabPane tabbable-custom-b active" id="tab_3">

<div class="" style="max-width: 1200px">
    <div class="row m-b-2">
        <div class="col-md-4">

            <div class="row">
                <div class="col-md-6 label-field">
                    {$smarty.const.COUPON_AMOUNT}
                    <div class="info-hint"><div class="info-hint-box"><div class="info-hint-mustache"></div>{$smarty.const.COUPON_AMOUNT_HELP}</div></div>
                </div>
                <div class="col-md-3">
                    <input type="text" name="coupon_amount" value="{$coupon['coupon_amount']}" class="form-control">
                </div>
                <div class="col-md-3">
                    {$coupon_currency}
                </div>
            </div>

        </div>
        <div class="col-md-4">

            {if $has_csv_data == '0' || !$cid }
                <div class="row">
                    <div class="col-md-6 label-field">
                        {$smarty.const.COUPON_CODE}
                        <div class="info-hint"><div class="info-hint-box"><div class="info-hint-mustache"></div>{$smarty.const.COUPON_CODE_HELP}</div></div>
                    </div>
                    <div class="col-md-5">
                        <input type="text" name="coupon_code" value="{$coupon['coupon_code']}" class="form-control">
                    </div>
                </div>
            {/if}
        </div>
        <div class="col-md-4">

            <div class="row">
                <div class="col-md-6 label-field">
                    {$smarty.const.COUPON_MIN_ORDER}
                    <div class="info-hint"><div class="info-hint-box"><div class="info-hint-mustache"></div>{$smarty.const.COUPON_MIN_ORDER_HELP}</div></div>
                </div>
                <div class="col-md-5">
                    <input type="text" name="coupon_minimum_order" value="{$coupon['coupon_minimum_order']}" class="form-control">
                </div>
            </div>
        </div>
    </div>

    <div class="row m-b-2">
        <div class="col-md-4">

            <div class="row">
                <div class="col-md-6 label-field">
                    {$smarty.const.TEXT_SPEND_PARTLY}
                    <div class="info-hint"><div class="info-hint-box"><div class="info-hint-mustache"></div>{$smarty.const.TEXT_SPEND_PARTLY_INTRO}</div></div>
                </div>
                <div class="col-md-5">
                    <input type="checkbox" name="spend_partly" value="1" class="check_on_off"{if $spend_partly} checked{/if}>
                </div>
            </div>

        </div>
    </div>

    <div class="row m-b-2">
        <div class="col-md-4">

            <div class="row">
                <div class="col-md-6 label-field">
                    {$smarty.const.AMOUNT_WITH_TAX}
                    <div class="info-hint"><div class="info-hint-box"><div class="info-hint-mustache"></div>{$smarty.const.COUPON_AMOUNT_WITH_TAX_HELP}</div></div>
                </div>
                <div class="col-md-5">
                    <input type="checkbox" name="flag_with_tax" value="1" class="check_on_off" {if $coupon['flag_with_tax']} checked{/if}>
                </div>
            </div>

        </div>
        <div class="col-md-4">

            <div class="row">
                <div class="col-md-6 label-field">{$smarty.const.TEXT_PRODUCTS_TAX_CLASS}
                    <div class="info-hint"><div class="info-hint-box"><div class="info-hint-mustache"></div>{$smarty.const.COUPON_CODE_HELP}</div></div>
                </div>
                <div class="col-md-5">
                    {*\backend\models\Configuration::tep_cfg_pull_down_tax_classes($coupon['tax_class_id'])*}
                    {Html::dropDownList('configuration_value', $coupon['tax_class_id'], $coupon_taxes)}
                </div>
            </div>

        </div>
    </div>

    <div class="row m-b-2">
        <div class="col-md-4">

            <div class="row">
                <div class="col-md-6 label-field">
                    {$smarty.const.TEXT_FREE_SHIPPING}
                </div>
                <div class="col-md-5">
                    <input type="checkbox" name="free_shipping" value="1" class="check_on_off" {if $coupon['free_shipping']} checked{/if}>
                </div>
            </div>

        </div>
        <div class="col-md-4">

            <div class="row">
                <div class="col-md-6 label-field">
                    {$smarty.const.COUPON_USES_SHIPPING}
                </div>
                <div class="col-md-5">
                    <input type="checkbox" name="uses_per_shipping" value="1" class="check_on_off" {if $coupon['uses_per_shipping']} checked{/if}>
                </div>
            </div>

        </div>
    </div>

    <div class="row m-b-2">
        <div class="col-md-4">
            <div class="row">
                <div class="col-md-6 label-field">
                    {$smarty.const.TEXT_POS_COUPON}<span class="colon">:</span>
                </div>
                <div class="col-md-5">
                    {Html::checkbox('pos_only', !empty($coupon.pos_only), ['value' => 1, 'class'=>'check_on_off'])}
                </div>
            </div>
        </div>

        <div class="col-md-4">

            <div class="row">
                <div class="col-md-6 label-field">
                    {$smarty.const.TEXT_FOR_RECOVERY}
                    <div class="info-hint"><div class="info-hint-box"><div class="info-hint-mustache"></div>{$smarty.const.FOR_RECOVERY_HELP}</div></div>
                </div>
                <div class="col-md-5">
                    <input type="checkbox" name="coupon_for_recovery_email" value="1" class="check_on_off"{if $coupon_for_recovery_email} checked{/if}>
                </div>
            </div>

        </div>
        <div class="col-md-4">

            <div class="row">
                <div class="col-md-6 label-field">
                    {$smarty.const.TEXT_DISABLE_FOR_SPECIAL}
                </div>
                <div class="col-md-5">
                    <input type="checkbox" name="disable_for_special" value="1" class="check_on_off"{if $coupon['disable_for_special']} checked{/if}>
                </div>
            </div>

        </div>
    </div>

    <div class="row m-b-2">
        <div class="col-md-4">

            <div class="row">
                <div class="col-md-6 label-field">
                    {$smarty.const.COUPON_USES_COUPON}
                    <div class="info-hint"><div class="info-hint-box"><div class="info-hint-mustache"></div>{$smarty.const.COUPON_USES_COUPON_HELP}</div></div>
                </div>
                <div class="col-md-3">
                    <input type="text" name="uses_per_coupon" value="{$coupon['uses_per_coupon']}" class="form-control">
                </div>
            </div>

        </div>
        <div class="col-md-4">

            <div class="row">
                <div class="col-md-6 label-field">
                    {$smarty.const.COUPON_USES_USER}
                    <div class="info-hint"><div class="info-hint-box"><div class="info-hint-mustache"></div>{$smarty.const.COUPON_USES_USER_HELP}</div></div>
                </div>
                <div class="col-md-3">
                    <input type="text" name="uses_per_user" value="{$coupon['uses_per_user']}" class="form-control">
                </div>
            </div>

        </div>
    </div>

    {if $has_csv_data == '0' || !$cid }
        <div class="row m-b-2">
            <div class="col-md-6">

                <div class="row">
                    <div class="col-md-4 label-field">
                        {$smarty.const.TEXT_RESTRICT_TO_CUSTOMERS}
                        <div class="info-hint"><div class="info-hint-box"><div class="info-hint-mustache"></div>{$smarty.const.TEXT_RESTRICT_TO_CUSTOMERS_HELP}</div></div>
                    </div>
                    <div class="col-md-8">
                        <input type="text" name="restrict_to_customers" value="{$coupon['restrict_to_customers']}" class="form-control">
                    </div>
                </div>

            </div>
        </div>
    {/if}

        <div class="row m-b-2">
            <div class="col-md-8">

                <div class="row">
                    <div class="col-md-6 label-field">
                        {$smarty.const.COUPON_SINGLE_PER_ORDER}
                        <div class="info-hint"><div class="info-hint-box"><div class="info-hint-mustache"></div>{$smarty.const.COUPON_SINGLE_PER_ORDER_HELP}</div></div>
                    </div>
                    <div class="col-md-6">
                        <input type="checkbox" name="single_per_order" value="1" class="check_on_off"{if $coupon['single_per_order']} checked{/if}>
                    </div>
                </div>

            </div>
        </div>

    <div class="row m-b-2">
        <div class="col-md-8">

            <div class="row">
                <div class="col-md-6 label-field">
                    {$smarty.const.TEXT_MAX_PRODUCT_QTY}
                </div>
                <div class="col-md-1">
                    <input type="checkbox" name="products-max-allowed-qty" value="1" class="show-hide-box"{if $coupon['products_max_allowed_qty']} checked{/if}>
                </div>
                <div class="col-md-2 products-max-allowed-qty">
                    <input type="text" name="products_max_allowed_qty" value="{$coupon['products_max_allowed_qty']}" class="form-control">
                </div>
            </div>

        </div>
    </div>

    <div class="row m-b-2">
        <div class="col-md-8">

            <div class="row">
                <div class="col-md-6 label-field">
                    {$smarty.const.TEXT_APPLY_COUPON_PRODUCT}
                </div>
                <div class="col-md-1">
                    <input type="checkbox" name="products-id-per-coupon" value="1" class="show-hide-box"{if $coupon['products_id_per_coupon']} checked{/if}>
                </div>
                <div class="col-md-2 products-id-per-coupon">
                    <input type="text" name="products_id_per_coupon" value="{$coupon['products_id_per_coupon']}" class="form-control">
                </div>
            </div>

        </div>
    </div>

    <div class="row m-b-2">
        <div class="col-md-6">

            <div class="row">
                <div class="col-md-4 label-field">
                    {$smarty.const.COUPON_PRODUCTS}
                    <div class="info-hint"><div class="info-hint-box"><div class="info-hint-mustache"></div>{$smarty.const.COUPON_PRODUCTS_HELP}</div></div>
                    <div class="m-t-2">
                        <a href="{$app->urlManager->createUrl('coupon_admin/treeview')}" class="btn popup">{$smarty.const.IMAGE_VIEW}</a>
                    </div>
                </div>
                <div class="col-md-8">
                        <input type="hidden" name="restrict_to_products" value="{$coupon['restrict_to_products']}" class="form-control">
                        <textarea type="text" name="restrict_to_products_names" class="form-control" data-href="{$app->urlManager->createUrl('coupon_admin/treeview')}" >{$restrict_to_products_names}</textarea>
                </div>
            </div>

        </div>
        <div class="col-md-6">

            <div class="row">
                <div class="col-md-4 label-field">
                    {$smarty.const.TEXT_EXCLUDE_PRODUCTS}
                    <div class="info-hint"><div class="info-hint-box"><div class="info-hint-mustache"></div>{$smarty.const.TEXT_EXCLUDE_PRODUCTS_HELP}</div></div>
                    <div class="m-t-2">
                        <a href="{$app->urlManager->createUrl(['coupon_admin/treeview', 'input' =>'exclude'])}" class="btn popup">{$smarty.const.IMAGE_VIEW}</a>
                    </div>
                </div>
                <div class="col-md-8">
                        <input type="hidden" name="exclude_products" value="{$coupon['exclude_products']}" class="form-control">
                        <textarea type="text" name="exclude_products_names" class="form-control"  data-href="{$app->urlManager->createUrl(['coupon_admin/treeview', 'input' =>'exclude'])}" >{$exclude_products_names}</textarea>
                </div>
            </div>

        </div>
    </div>

    <div class="row m-b-2">
        <div class="col-md-6">

            <div class="row">
                <div class="col-md-4 label-field">
                    {$smarty.const.COUPON_CATEGORIES}
                    <div class="info-hint"><div class="info-hint-box"><div class="info-hint-mustache"></div>{$smarty.const.COUPON_CATEGORIES_HELP}</div></div>
                    <div class="m-t-2">
                        <a href="{$app->urlManager->createUrl('coupon_admin/treeview')}" class="btn popup">{$smarty.const.IMAGE_VIEW}</a>
                    </div>
                </div>
                <div class="col-md-8">
                    <input type="hidden" name="restrict_to_categories" value="{$coupon['restrict_to_categories']}" class="form-control">
                    <textarea type="text" name="restrict_to_categories_names" class="form-control" data-href="{$app->urlManager->createUrl('coupon_admin/treeview')}" >{$restrict_to_categories_names}</textarea>
                </div>
            </div>

        </div>
        <div class="col-md-6">

            <div class="row">
                <div class="col-md-4 label-field">
                    {$smarty.const.TEXT_EXCLUDE_CATEGORIES}
                    <div class="info-hint"><div class="info-hint-box"><div class="info-hint-mustache"></div>{$smarty.const.TEXT_EXCLUDE_CATEGORIES_HELP}</div></div>
                    <div class="m-t-2">
                        <a href="{$app->urlManager->createUrl(['coupon_admin/treeview', 'input' =>'exclude'])}" class="btn popup">{$smarty.const.IMAGE_VIEW}</a>
                    </div>
                </div>
                <div class="col-md-8">
                    <input type="hidden" name="exclude_categories" value="{$coupon['exclude_categories']}" class="form-control">
                    <textarea type="text" name="exclude_categories_names" class="form-control" data-href="{$app->urlManager->createUrl(['coupon_admin/treeview', 'input' =>'exclude'])}" >{$exclude_categories_names}</textarea>
                </div>
            </div>

        </div>
    </div>

    <div class="row m-b-2">
        <div class="col-md-4">

            <div class="row">
                <div class="col-md-6 label-field">
                    {$smarty.const.COUPON_MANUFACTURERS}
                    <div class="info-hint"><div class="info-hint-box"><div class="info-hint-mustache"></div>{$smarty.const.COUPON_MANUFACTURERS_HELP}</div></div>
                </div>
                <div class="col-md-5">
                    {\yii\helpers\Html::dropDownList('restrict_to_manufacturers[]', explode(",", $coupon['restrict_to_manufacturers']), \Yii\helpers\Arrayhelper::map(\common\helpers\Manufacturers::get_manufacturers(), 'id', 'text'), ['class' => '', 'multiple' => 'multiple', 'data-role' => 'multiselect'])}
                </div>
            </div>

        </div>
    </div>

    <div class="row m-b-2">
        <div class="col-md-4">

            <div class="row">
                <div class="col-md-6 label-field">
                    {$smarty.const.COUPON_GROUPS}
                    <div class="info-hint"><div class="info-hint-box"><div class="info-hint-mustache"></div>{$smarty.const.COUPON_GROUPS_HELP}</div></div>
                </div>
                <div class="col-md-5">
                    {\yii\helpers\Html::dropDownList('coupon_groups[]', explode(",", $coupon['coupon_groups']), \common\helpers\Group::get_customer_groups_list(), ['class' => '', 'multiple' => 'multiple', 'data-role' => 'multiselect'])}
                </div>
            </div>

        </div>
    </div>

    <div class="row m-b-2">
        <div class="col-md-4">

            <div class="row">
                <div class="col-md-6 label-field">
                    {$smarty.const.COUPON_COUNTRIES}
                    <div class="info-hint"><div class="info-hint-box"><div class="info-hint-mustache"></div>{$smarty.const.COUPON_COUNTRIES_HELP}</div></div>
                </div>
                <div class="col-md-5">
                    {\yii\helpers\Html::dropDownList('restrict_to_countries[]', explode(",", $coupon['restrict_to_countries']), \common\helpers\Country::new_get_countries(), ['class' => '', 'multiple' => 'multiple', 'data-role' => 'multiselect'])}
                </div>
            </div>

        </div>
    </div>

    <div class="row m-b-2">
        <div class="col-md-4">

            <div class="row">
                <div class="col-md-6 label-field">
                    {$smarty.const.COUPON_STARTDATE}
                    <div class="info-hint"><div class="info-hint-box"><div class="info-hint-mustache"></div>{$smarty.const.COUPON_STARTDATE_HELP}</div></div>
                </div>
                <div class="col-md-5">
                    <input type="text" name="coupon_startdate" value="{$coupon_start_date}" class="form-control date-control startdate datepicker">
                </div>
            </div>

        </div>
        <div class="col-md-4">

            <div class="row">
                <div class="col-md-6 label-field">
                    {$smarty.const.COUPON_FINISHDATE}
                    <div class="info-hint"><div class="info-hint-box"><div class="info-hint-mustache"></div>{$smarty.const.COUPON_FINISHDATE_HELP}</div></div>
                </div>
                <div class="col-md-5">
                    <input type="text" name="coupon_finishdate" value="{$coupon_expire_date}" class="form-control date-control enddate datepicker">
                </div>
            </div>

        </div>
    </div>
    {foreach \common\helpers\Hooks::getList('coupon_admin/voucheredit', 'tab-content/main-detail/bottom') as $filename}
        {include file=$filename}
    {/foreach}
    </div>

                   <table cellspacing="0" cellpadding="0" width="100%">
{*
new coupon show 2 tabs: generate and upload
existing - only used during creation
*}
                        {if empty($cid)}
                           <tr><td colspan="2">
                             <div class="tabbable tabbable-custom after">
                                <ul class="nav nav-tabs top_tabs_ul">
                                  <li class="active" data-bs-toggle="tab" data-bs-target="#tab_3_1"><a>{$smarty.const.TEXT_GENERATE}</a></li>
                                  <li data-bs-toggle="tab" data-bs-target="#tab_2_1"><a>{$smarty.const.IMAGE_UPLOAD}</a></li>
                                </ul>
                                <div class="tab-content">
                                    <div class="tab-pane tabbable-custom active" id="tab_3_1">
                                       <table cellspacing="0" cellpadding="0" width="100%">
                        {/if}

                          {if $has_csv_data == '0' && $cid == true}
                          <!--coupon was created without csv uploaded items-->

                          {else}
                            {if empty($cid)}
                              </table></div>
                              <div class="tab-pane topTabPane tabbable-custom-b " id="tab_2_1">
                                 <table cellspacing="0" cellpadding="0" width="100%">
                            {/if}

                            <tr>
                                <td valign="top" class="label_name">{$smarty.const.COUPON_CUSTOMERS_COUPONS_CSV}
                                    {if $has_csv_data == '0'}<div class="info-hint"><div class="info-hint-box"><div class="info-hint-mustache"></div>{$smarty.const.COUPON_CUSTOMERS_COUPONS_CSV_HELP}</div></div>{/if}
                                </td>
                                  <td class="label_value">
                                      {if $has_csv_data == '0' && $cid == false}
                                      <div class="col-md-8">

                                          <div class="upload-box upload-box-wrap upload-box-no-buttons"
                                               data-name="coupon_csv"
                                               data-value=""
                                               data-upload="coupon_csv_loaded"
                                               data-type="image"
                                               data-acceptedFiles="image/*">
                                          </div>

                                      </div>
                                      {/if}
                                      {if $has_csv_data == '1' && $cid == true}
                                      <div class="coupon_vew">
                                          <input type="text" name="coupon_csv_loaded" value="{$customers_coupons_csv}" class="form-control" readonly="readonly">
                                          <a href="{$app->urlManager->createUrl(['coupon_admincustomerscodes/coupon_csv_loaded', 'cid' =>$cid])}" class="btn popup" data-class="popupEditCat">
                                              {$smarty.const.IMAGE_VIEW}
                                          </a>
                                      </div>
                                      {/if}
                                  </td>
                          </tr>
                          {/if}

                        {if empty($cid)}
                        </table></div></div><br />
                           </td></tr>
                        {/if}

                    </table>                   
            </div>
            <div class="tab-pane topTabPane tabbable-custom " id="tab_2">
                {if count($languages) > 1}
              <ul class="nav nav-tabs under_tabs_ul">
                {foreach $languages as $lKey => $lItem}
                  <li{if $lKey == 0} class="active"{/if} data-bs-toggle="tab" data-bs-target="#tab_{$lItem['code']}"><a>{$lItem['logo']}<span>{$lItem['name']}</span></a></li>
                {/foreach}
              </ul>
              {/if}
              <div class="tab-content {if count($languages) < 2}tab-content-no-lang{/if}">
                {foreach $languages as $lKey => $lItem}
                  <div class="tab-pane{if $lKey == 0} active{/if}" id="tab_{$lItem['code']}">
                    <table cellspacing="0" cellpadding="0" width="100%">
                      <tr>
                        <td class="label_name">{$smarty.const.COUPON_NAME}</td>
                        <td class="label_value"><input type="text" name="coupon_name[{$lItem['id']}]" value="{$coupon_name[$lItem['id']]}" class="form-control"></td>
                      </tr>
                      <tr>
                        <td class="label_name">{$smarty.const.COUPON_DESC}</td>
                        <td class="label_value"><textarea name="coupon_description[{$lItem['id']}]" cols="24" rows="3">{$coupon_desc[$lItem['id']]}</textarea></td>
                      </tr>
                    </table>
                  </div>
                {/foreach}
              </div>
            </div>
            {if platform::isMulti()}
            <div class="tab-pane topTabPane tabbable-custom " id="tab_4">
                <div class="content ">
                    {Html::checkbox('check_platforms', !empty($coupon.check_platforms), ['value' => 1, 'class'=>'check_on_off'])}{$smarty.const.TEXT_COUPONS_RESTRICT_PLATFORMS}
                    <div class="platforms-container" id="platforms_container" style="{if empty($coupon.check_platforms)}display:none{/if}">
                    {include '../assets/platforms.tpl'}
                    </div>
                </div>
            </div>
            {/if}
        </div>
    </div>
    <div class="btn-bar edit-btn-bar">
        <div class="btn-left"><a href="javascript:void(0)" class="btn btn-cancel-foot" onclick="return backStatement()">{$smarty.const.IMAGE_CANCEL}</a></div>
        <div class="btn-right">
            {if !$cid}<button class="btn btn-primary js-batch-create" type="button">{$smarty.const.TEXT_SAVE_BATCH}</button>{/if}
            <button class="btn btn-primary">{$smarty.const.IMAGE_SAVE}</button>
        </div>
    </div>
</div>
<input type="hidden" name="coupon_id" value="{$cid}" />
</form>
</div>
<script type="text/javascript">
var form_prepared = true;
var catIds=[];
var prodIds=[];

function backStatement() {
    window.history.back();
    return false;
}
function saveVoucher(batchParams) {
	checkSelectedTaxZone();
    var $visible_currency_selector = $('#save_voucher_form [name="_coupon_currency"]');
    if ( $visible_currency_selector.length>0 ){
        var $coupon_amount = $('#save_voucher_form [name="coupon_amount"]');
        $coupon_amount.val( $coupon_amount.val().replace(/%*$/,'') );
        if ($visible_currency_selector.val()==='%'){
            $coupon_amount.val( $coupon_amount.val()+'%' );
        }
    }

	if (form_prepared){
        var postData = $('#save_voucher_form').serializeArray();
        if ( batchParams ){
            postData.push(batchParams);
            //$('body').append('<div class="around-pop-up"></div>');
            alertMessage('<p style="padding: 20px; text-align: center">In progress... Please wait</p>');
            $('.popup-box-wrap .pop-up-close').hide();
        }
		$.post("{$app->urlManager->createUrl('coupon_admin/voucher-submit')}", postData, function(data, status){
            $('.popup-box-wrap').remove();
			if (status == "success") {
				$('#voucher_management_data').html(data);
			} else {
				alert("Request error.");
			}
		},"html");	
	}
    return false;
}

function checkSelectedTaxZone(){
	if ($('select[name=configuration_value]').val() == 0 && $('input[name=flag_with_tax]:checkbox').prop('checked')){
	   form_prepared = false;
	   bootbox.dialog({
        message: '<div class=""><label class="control-label">'+"{$smarty.const.TEXT_SELECT_TAX_CLASS}"+'</label></div>',
        title: "{$smarty.const.ICON_WARNING}",
          buttons: {
            cancel: {
              label: "{$smarty.const.TEXT_BTN_OK}",
              className: "btn-cancel",
              callback: function() {
				
              }
            }
          }
      });
	} else {
	 form_prepared = true;
	}
}
var _old_uses = 0;
$(document).ready(function(){
    $("#checkPlatforms").on('change, switchChange.bootstrapSwitch', function(){
        $("#platforms_container").toggle();
    });

    $("select[data-role=multiselect]").multipleSelect({
        multiple: true,
        filter: true,
    });
    $( ".startdate.datepicker" ).datepicker({
        changeMonth: true,
        changeYear: true,
        showOtherMonths:true,
        autoSize: false,
		//minDate: '1',
        dateFormat: '{$smarty.const.DATE_FORMAT_DATEPICKER}',
        onClose: function( selectedDate ) {
          $( ".enddate.datepicker" ).datepicker( "option", "minDate", selectedDate );
       }			
    });

    $( ".enddate.datepicker" ).datepicker({
        changeMonth: true,
        changeYear: true,
        showOtherMonths:true,
        autoSize: false,
		//minDate: '1',
        dateFormat: '{$smarty.const.DATE_FORMAT_DATEPICKER}',
        onClose: function( selectedDate ) {
          $( ".startdate.datepicker" ).datepicker( "option", "maxDate", selectedDate );
       }		
    });
	
	_old_uses = $('input[name=uses_per_coupon]').val();
  $(".check_on_off").bootstrapSwitch(
    {
      onText: "{$smarty.const.SW_ON}",
      offText: "{$smarty.const.SW_OFF}",
      handleWidth: '20px',
      labelWidth: '24px',
      onSwitchChange: function (element, argument) {
        if (element.target.name == 'coupon_for_recovery_email'){
          if (argument) {
            $('input[name=uses_per_coupon]').val('');
          } else {
            $('input[name=uses_per_coupon]').val(_old_uses);
          }        
        } else if (element.target.name == 'flag_with_tax'){
			if (argument) {
				checkSelectedTaxZone();				
			}
		}
        return true;
      },
    }
  );

  $(".show-hide-box").each(function(){
      if ($(this).prop('checked')) {
          $('.' + $(this).attr('name')).show();
      } else {
          $('.' + $(this).attr('name')).hide();
      }
  }).bootstrapSwitch({
      onText: "{$smarty.const.SW_ON}",
      offText: "{$smarty.const.SW_OFF}",
      handleWidth: '20px',
      labelWidth: '24px',
      onSwitchChange: function (element, argument) {

          if (argument) {
              $('.' + element.target.name).show();
          } else {
              $('.' + element.target.name).hide();
              $('.' + element.target.name + ' input').val('');
          }
          return true;
      },
  });

	$('.popup').popUp({
      box: "<div class='popup-box-wrap'><div class='around-pop-up'></div><div class='popup-box'><div class='popup-heading cat-head'>{$smarty.const.TEXT_BANNER_NEW_GROUP}</div><div class='pop-up-content'><div class='preloader'></div></div></div></div>",
      box_class: $(this).data('class')
	});

    $('.js-batch-create').on('click',function(){
        bootbox.prompt({
            title: "Enter coupon count",
            inputType: 'number',
            callback: function (result) {
                if ( result && !isNaN(parseInt(result)) && parseInt(result)>0) {
                    saveVoucher({ name:'coupon_count', value:parseInt(result) });
                }
            }
        });
    });
	})

$.fn.uploads2 = function(options){
  var option = jQuery.extend({
    overflow: false,
    box_class: false
  },options);

  var body = $('body');
  var html = $('html');

  return this.each(function() {
    var _this = $(this);
    if (_this.data('value')) {
      _this.html('\
    <div class="upload-file-wrap">\
      <div class="upload-file-template">{$smarty.const.TEXT_DRAG_DROP|escape:'html'}<br><span class="btn">{$smarty.const.IMAGE_UPLOAD|escape:'html'}</span></div>\
      <div class="upload-file dz-clickable dz-started"><div class="dz-details dz-processing dz-success dz-image-preview"><div class="dz-filename"><span data-dz-name="">' + _this.data('value') + '</span></div><div class="upload-remove"></div></div></div>\
      <div class="upload-hidden"><input type="hidden" name="' + _this.data('name') + '"/></div>\
    </div>');
      $('.upload-remove', _this).click(function(){
        $('.upload-file', _this).html('');
        _this.removeAttr('data-value');
        $('input[name="' + _this.data('name').replace('upload_docs', 'values') + '"]').val('');
      })
    } else {
      _this.html('\
    <div class="upload-file-wrap">\
      <div class="upload-file-template">{$smarty.const.TEXT_DRAG_DROP|escape:'html'}<br><span class="btn">{$smarty.const.IMAGE_UPLOAD|escape:'html'}</span></div>\
      <div class="upload-file"></div>\
      <div class="upload-hidden"><input type="hidden" name="' + _this.data('name') + '"/></div>\
    </div>');
    }

    $('.upload-file', _this).dropzone({
      url: "{Yii::$app->urlManager->createUrl('upload')}",
      sending:  function(e, data) {
        $('.upload-hidden input[type="hidden"]', _this).val(e.name);
        $('.upload-remove', _this).on('click', function(){
          $('.dz-details', _this).remove()
        })
      },
      previewTemplate: '<div class="dz-details"><div class="dz-filename"><span data-dz-name=""></span></div><div class="upload-remove"></div></div>',
      dataType: 'json',
      drop: function(){
        $('.upload-file', _this).html('');
      }
    });

  })
};

$('.upload-box').fileManager();

$('.upload_doc').uploads2();

$(function(){
    let $couponAmount = $('input[name="coupon_amount"]');
    let $couponCurrency = $('select[name="coupon_currency"]');
    let $_couponAmount = $couponAmount.clone()
    let $_couponCurrency = $couponCurrency.clone()

    $couponAmount.hide().after($_couponAmount);
    $couponCurrency.hide().after($_couponCurrency);
    $_couponAmount.attr('name', '_' + $_couponAmount.attr('name'));
    $_couponCurrency.attr('name', '_' + $_couponCurrency.attr('name'))
    $_couponCurrency.prepend('<option name="%">%</option>')

    fromCouponAmount();
    $_couponAmount.on('keyup', fromCouponAmount)
    $_couponCurrency.on('change', fromCouponCurrency)

    function fromCouponAmount() {
        $couponAmount.val($_couponAmount.val())
        if ($_couponAmount.val().slice(-1) == '%') {
            $_couponCurrency.val('%')
            $_couponCurrency.trigger('change');
            $_couponAmount.val($_couponAmount.val().slice(0, $_couponAmount.val().length - 1))
        }
    }
    function fromCouponCurrency() {
        $couponAmount.val($_couponAmount.val())
        if ($_couponCurrency.val() == '%') {
            $couponAmount.val($_couponAmount.val() + '%')
        } else {
            $couponCurrency.val($_couponCurrency.val())
        }
    }

    $('textarea[data-href]').css('cursor', 'pointer').on('click', function() {
        $('<a href="' + $(this).data('href') + '"></a>').popUp({
            box: "<div class='popup-box-wrap'><div class='around-pop-up'></div><div class='popup-box'><div class='popup-heading cat-head'>{$smarty.const.TEXT_BANNER_NEW_GROUP}</div><div class='pop-up-content'><div class='preloader'></div></div></div></div>",
            box_class: $(this).data('class')
        }).trigger('click')
    })

})
</script>