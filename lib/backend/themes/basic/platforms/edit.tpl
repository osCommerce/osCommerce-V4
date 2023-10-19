{use class="yii\helpers\Html"}
{use class="common\helpers\Acl"}
{\backend\assets\BDPAsset::register($this)|void}
<!--=== Page Content ===-->
<div id="platforms_management_data">
<!--===Customers List ===-->
<form name="save_item_form" id="save_item_form" enctype="multipart/form-data" onSubmit="return saveItem();">
<div class="box-wrap">
    <div class="create-or-wrap after create-cus-wrap">
        <div class="cbox-left">
            <div class="widget box box-no-shadow">
                <div class="widget-header widget-header-personal"><h4>{$smarty.const.CATEGORY_GENERAL}</h4></div>
                <div class="widget-content">
                    <div class="w-line-row w-line-row-1">
                        <div style="display:inline-block;">
                            <label>{$smarty.const.ENTRY_STATUS}</label>
                            {Html::checkbox('status', $pInfo->status|default:null, ['value'=>'1', 'class' => 'js_check_status'])}
                        </div>
                        <div class="marketplace_switcher" style="display:inline-block;margin-left:10px;">
                            <label>{$smarty.const.ENTRY_IS_MARKETPLACE}</label>
                            {Html::checkbox('is_marketplace', $pInfo->is_marketplace, ['value'=>'1', 'class' => 'js_check_is_marketplace'])}
                        </div>
                        <div class="virtual_switcher" style="display:inline-block;margin-left:10px;">
                            <label>{$smarty.const.ENTRY_IS_VIRTUAL}</label>
                            {Html::checkbox('is_virtual', $pInfo->is_virtual, ['value'=>'1', 'class' => 'js_check_is_virtual'])}
                        </div>
                    </div>
                    <div class="w-line-row w-line-row-1">
                        <div class="wl-td">
                            <label>{$smarty.const.ENTRY_PLATFORM_OWNER}<span class="fieldRequired">*</span></label>{Html::input('text', 'platform_owner', $pInfo->platform_owner|default:null, ['class' => 'form-control', 'required' => true])}
                        </div>
                    </div>
                    <div class="w-line-row w-line-row-1">
                        <div class="wl-td">
                            <label>{$smarty.const.ENTRY_PLATFORM_NAME}<span class="fieldRequired">*</span></label>{Html::input('text', 'platform_name', $pInfo->platform_name|default:null, ['class' => 'form-control', 'required' => true])}
                        </div>
                    </div>
                    <div class="no_marketplace_or_virtual" {if $pInfo->is_marketplace == 1 || $pInfo->is_virtual == 1} style="display: none;"{/if}>
                        <div class="w-line-row w-line-row-1">
                            <div class="wl-td">
                                <label>{$smarty.const.ENTRY_PLATFORM_URL}<span class="fieldRequired">*</span></label>{Html::input('text', 'platform_url', $pInfo->platform_url|default:null, ['class' => 'form-control marketplace_no_check'])}
                            </div>
                        </div>
                        <div class="w-line-row w-line-row-1">
                            <div class="wl-td">
                                <label>{$smarty.const.ENTRY_SSL_ENABLED}</label>
                                {*Html::checkbox('ssl_enabled', !!$pInfo->ssl_enabled, ['value'=>'1', 'class' => 'js_check_ssl_enabled'])*}
                                {Html::radio('ssl_enabled', ($pInfo->ssl_enabled == 0), ['value'=> '0', 'class' => 'js_check_ssl_enabled'])} NoSSL
                                {Html::radio('ssl_enabled', ($pInfo->ssl_enabled == 1), ['value'=> '1', 'class' => 'js_check_ssl_enabled'])} SSL
                                {Html::radio('ssl_enabled', ($pInfo->ssl_enabled == 2), ['value'=> '2', 'class' => 'js_check_ssl_enabled'])} FullSSL
                            </div>
                        </div>
                        <div class="w-line-row w-line-row-1 js_check_ssl_enabled_true">
                            <div class="wl-td">
                                <label>{$smarty.const.ENTRY_PLATFORM_URL_SECURE}</label>{Html::input('text', 'platform_url_secure', $pInfo->platform_url_secure|default:null, ['class' => 'form-control', 'placeholder' => PLACEHOLDER_PLATFORM_URL_SECURE])}
                            </div>
                        </div>
                        <div class="w-line-row w-line-row-1">
                            <div class="wl-td">
                                <label>{$smarty.const.ENTRY_PLATFORM_PREFIX}</label>{Html::input('text', 'platform_prefix', $pInfo->platform_prefix|default:null, ['class' => 'form-control'])}
                            </div>
                        </div>
                        {if \common\helpers\Acl::checkExtensionAllowed('BusinessToBusiness', 'allowed')}
                          {\common\extensions\BusinessToBusiness\BusinessToBusiness::frontendBlock($pInfo)}
                        {/if}

                        <div class="row align-items-center m-b-2">
                            <label class="col-5 align-right">{$smarty.const.ENTRY_PLATFORM_PLEASE_LOGIN}</label>
                            <div class="col-7">
                                {Html::checkbox('platform_please_login', !!($pInfo->platform_please_login|default:null), ['value'=>'1', 'class' => 'js_check_need_login'])}
                            </div>
                        </div>

                        <div class="row align-items-center m-b-2">
                            <label class="col-5 align-right">{$smarty.const.CHECKOUT_ONLY_FOR_LOGGED_CUSTOMERS}</label>
                            <div class="col-7">
                                {Html::checkbox('checkout_logged_customer', !!($pInfo->checkout_logged_customer|default:null), ['value'=>'1', 'class' => 'js_check_use_social_login'])}
                            </div>
                        </div>

                        <div class="row align-items-center m-b-2">
                            <label class="col-5 align-right">{$smarty.const.USE_SOCIAL_LOGIN}</label>
                            <div class="col-7">
                                {Html::checkbox('use_social_login', !!($pInfo->use_social_login|default:null), ['value'=>'1', 'class' => 'js_check_use_social_login'])}
                            </div>
                        </div>

                        {if $have_more_then_one_platform}
                            <div class="row align-items-center m-b-2">
                                <label class="col-5 align-right">{$smarty.const.ENTRY_IS_DEFAULT_PLATFORM}</label>
                                <div class="col-7">
                                    {Html::checkbox('is_default', !!($pInfo->is_default|default:null), array_merge(['value'=>'1', 'class' => 'js_check_default_platform'],$checkbox_default_platform_attr))}
                                    {Html::hiddenInput('present_is_default','1')}
                                </div>
                            </div>
                        {else}
                            {Html::hiddenInput('is_default','1')}
                            {Html::hiddenInput('present_is_default','1')}
                        {/if}
                    </div>
                    
                    <div class="w-line-row w-line-row-1 default_shop_selector">
                        <div class="wl-td">
                            <label>{$smarty.const.ENTRY_IS_DEFAULT_PLATFORM}</label>
                        {Html::dropDownList('default_platform_id', $pInfo->default_platform_id|default:null, $sattelits, ['class' => 'form-control'])}
                        </div>
                    </div> 
                    
                    <div class="w-line-row w-line-row-1 yes_virtual" {if !$pInfo->is_virtual}style="display:none;"{/if}>
                        <div class="wl-td">
                            <label>{$smarty.const.TEXT_PHYSICAL} {$smarty.const.TABLE_HEADING_PLATFORM}</label>
                            {Html::dropDownList('sattelit_id', $pInfo->sattelite_platform_id|default:null, $sattelits, ['class' => 'form-control'])}
                            <span><a href="" class="virtual_link" target="_blank" ></a></span>
                        </div>
                    </div>
                    <div class="w-line-row w-line-row-1 yes_virtual" {if !$pInfo->is_virtual}style="display:none;"{/if}>
                        <div class="wl-td input-group">
                            <label>{$smarty.const.TEXT_PLATFORM_CODE}<span class="fieldRequired">*</span></label>
                            {Html::input('text', 'platform_code', $pInfo->platform_code|default:null, ['class' => 'form-control platform_code'])}
                            <div class="input-group-addon" id="lnCodeGenerate" title="{$smarty.const.TEXT_GENERATE|escape:'html'}"><i class="icon-refresh"></i></div>
                        </div>
                    </div>
                    <div class="w-line-row w-line-row-1 w-line-row-req">
                          <span style="color: #f2353c; margin: 22px 0 0; display: block;">{$smarty.const.ENTRY_REQUIRED_FIELDS}</span>
                    </div>
                </div>
            </div>
            <div class="widget box box-no-shadow">
                <div class="widget-header widget-header-contact"><h4>{$smarty.const.CATEGORY_CONTACT}</h4></div>
                <div class="widget-content">
                    <div class="w-line-row w-line-row-1 contact_switcher">
                        <div class="wl-td">
                            <label>{$smarty.const.ENTRY_SAME_AS_DEFAULT}</label>
                            {Html::checkbox('is_default_contact', $pInfo->is_default_contact, ['value'=>'1', 'class' => 'js_check_is_default_contact'])}
                        </div>
                    </div>
                        <div class="no_default_contact">
                    <div class="w-line-row w-line-row-2">
                        <div class="wl-td">
                            <label>{$smarty.const.ENTRY_EMAIL_ADDRESS}<span class="fieldRequired">*</span></label>{Html::input('text', 'platform_email_address', $pInfo->platform_email_address|default:null, ['class' => 'form-control default_contact_no_check', 'id'=>'txtPlatformEmail', 'required' => true])}
                        </div>
                        <div class="wl-td">
                            <label>{$smarty.const.ENTRY_EMAIL_FROM}<span class="fieldRequired">*</span></label>{Html::input('text', 'platform_email_from', $pInfo->platform_email_from|default:null, ['class' => 'form-control default_contact_no_check', 'required' => true])}
                        </div>
                    </div>
                    <div class="w-line-row w-line-row-1">
                        <div class="wl-td">
                            <label>{$smarty.const.ENTRY_EMAIL_EXTRA}</label>{Html::input('text', 'platform_email_extra', $pInfo->platform_email_extra|default:null, ['class' => 'form-control default_contact_no_check'])}
                        </div>
                    </div>
                    <div class="w-line-row w-line-row-1">
                        <div class="wl-td">
                            <label>{$smarty.const.TEXT_CONTACT_US_EMAIL}</label>{Html::input('text', 'contact_us_email', $pInfo->contact_us_email|default:null, ['class' => 'form-control default_contact_no_check js-override-platform-email'])}
                        </div>
                    </div>
                    <div class="w-line-row w-line-row-1">
                        <div class="wl-td">
                            <label>{$smarty.const.TEXT_LANDING_FORM_EMAIL}</label>{Html::input('text', 'landing_contact_email', $pInfo->landing_contact_email|default:null, ['class' => 'form-control default_contact_no_check js-override-platform-email'])}
                        </div>
                    </div>
                    <div class="w-line-row w-line-row-2">
                        <div class="wl-td">
                            <label>{$smarty.const.ENTRY_TELEPHONE_NUMBER}</label>{Html::input('text', 'platform_telephone', $pInfo->platform_telephone|default:null, ['class' => 'form-control default_contact_no_check'])}
                        </div>
                        <div class="wl-td">
                            <label>{$smarty.const.ENTRY_LANDLINE}</label>{Html::input('text', 'platform_landline', $pInfo->platform_landline|default:null, ['class' => 'form-control default_contact_no_check'])}
                        </div>
                    </div>
                        </div>
                    <div class="w-line-row w-line-row-1 w-line-row-req">
                          <span style="color: #f2353c; margin: 22px 0 0; display: block;">{$smarty.const.ENTRY_REQUIRED_FIELDS}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="cbox-right">
            <div class="widget box box-no-shadow" style="padding-bottom: 30px;">
                <div class="widget-header widget-header-address"><h4>{$smarty.const.CATEGORY_ADDRESS}</h4></div>
                <div class="widget-content">
                    
                    <div class="w-line-row w-line-row-1 contact_switcher">
                        <div class="wl-td">
                            <label>{$smarty.const.ENTRY_SAME_AS_DEFAULT}</label>
                            {Html::checkbox('is_default_address', $pInfo->is_default_address, ['value'=>'1', 'class' => 'js_check_is_default_address'])}
                        </div>
                    </div>
<div class="no_default_address">
                    <div class="w-line-row w-line-row-1">
                        <div class="wl-td">
                            <label>{$smarty.const.ENTRY_COMPANY}<span class="fieldRequired">*</span></label>{Html::input('text', 'entry_company[]', $addresses->entry_company|default:null, ['class' => 'form-control default_address_no_check'])}
                        </div>
                    </div>
                    <div class="w-line-row w-line-row-2">
                        <div class="wl-td">
                            <label>{$smarty.const.ENTRY_BUSINESS}<span class="fieldRequired">*</span></label>{Html::input('text', 'entry_company_vat[]', $addresses->entry_company_vat|default:null, ['class' => 'form-control default_address_no_check'])}
                        </div>
                        <div class="wl-td">
                            <label>{$smarty.const.ENTRY_BUSINESS_REG_NUMBER}</label>{Html::input('text', 'entry_company_reg_number[]', $addresses->entry_company_reg_number|default:null, ['class' => 'form-control default_address_no_check'])}
                        </div>
                    </div>
            
                    
                    <div class="w-line-row w-line-row-2">
                        <div class="wl-td">
                            <label>{$smarty.const.ENTRY_POST_CODE}<span class="fieldRequired">*</span></label>{Html::input('text', 'entry_postcode[]', $addresses->entry_postcode|default:null, ['class' => 'form-control default_address_no_check'])}
                        </div>
                        <div class="wl-td">
                            <label>{$smarty.const.ENTRY_STREET_ADDRESS}<span class="fieldRequired">*</span></label>{Html::input('text', 'entry_street_address[]', $addresses->entry_street_address|default:null, ['class' => 'form-control default_address_no_check'])}
                        </div>
                    </div>
                    <div class="w-line-row w-line-row-2">
                        <div class="wl-td">
                            <label>{$smarty.const.ENTRY_SUBURB}</label>{Html::input('text', 'entry_suburb[]', $addresses->entry_suburb|default:null, ['class' => 'form-control default_address_no_check'])}
                        </div>
                        <div class="wl-td">
                            <label>{$smarty.const.ENTRY_CITY}<span class="fieldRequired">*</span></label>{Html::input('text', 'entry_city[]', $addresses->entry_city|default:null, ['class' => 'form-control default_address_no_check'])}
                        </div>
                    </div>
                    <div class="w-line-row w-line-row-2">
                        <div class="wl-td">
                            <label>{field_label const="ENTRY_STATE" configuration="ACCOUNT_STATE"}</label>
                            <div class="f_td_state">
                                {Html::input('text', 'entry_state[]', $addresses->entry_state|default:null, ['class' => 'form-control default_address_no_check', 'id' => "selectState"])}
                            </div>
                        </div>
                        <div class="wl-td">
                            <label>{$smarty.const.ENTRY_COUNTRY}<span class="fieldRequired">*</span></label>{Html::dropDownList('entry_country_id[]', $addresses->entry_country_id|default:null, \common\helpers\Country::new_get_countries('', false), ['class' => 'form-control default_address_no_check', 'id' => "selectCountry", 'required' => true])}
                        </div>
                    </div>

                    <div class="w-line-row w-line-row-2">
                        <div class="wl-td">
                            <label>{$smarty.const.TEXT_INFO_LATITUDE}:</label>
                            <div class="f_td_state">
                                {Html::input('text', 'lat[]', $addresses->lat|default:null, ['class' => 'form-control'])}
                            </div>
                        </div>
                        <div class="wl-td">
                            <label>{$smarty.const.TEXT_INFO_LANGITUTE}:</label>
                            {Html::input('text', 'lng[]', $addresses->lng|default:null, ['class' => 'form-control'])}
                        </div>
                    </div>
</div>                      
                    <!--<div class="w-line-row w-line-row-1">
                        <div class="wl-td">
                            <input type="checkbox" /> <b>Make default address</b>
                        </div>
                    </div>!-->
                </div>
                <div class="w-line-row w-line-row-1 w-line-row-req w-line-row-abs">
                          <span style="color: #f2353c; margin: 22px 0 0; display: block;">{$smarty.const.ENTRY_REQUIRED_FIELDS}</span>
                    </div>
            </div>
            {Html::input('hidden', 'platforms_address_book_id[]', $addresses->platforms_address_book_id|default:null)}

            {include $pass|cat: '/themes/basic/platforms/edit/organization.tpl'}


        </div>        
    </div>
        <div class="create-or-wrap after create-cus-wrap no_marketplace_or_virtual" {if $pInfo->is_marketplace == 1 || $pInfo->is_virtual == 1 }style="display: none;"{/if}>
        <div class="cbox-left">
            
    <div class="widget box box-no-shadow js_exclusion_rules" style="min-height:183px;{if !($pInfo->need_login|default:null)}display: none;{/if}">
        <div class="widget-header widget-header-theme"><h4>{$smarty.const.EXCLUSION_RULES}</h4></div>
        <div class="widget-content">
            <div id="exclusion_rules_list">
            {foreach $exclusion_rules.type as $rule_key => $rule_type}
            <div class="rule_int exclusion_rules">
                <div class="">
                    {Html::dropDownList('exclusion_rule_method[]', $exclusion_rules.method[$rule_key], ['AND' => 'AND', 'OR' => 'OR'], ['class' => 'form-control'])}
                </div>
                <div class="">
                    {Html::dropDownList('exclusion_rule_type[]', $rule_type, $exclusion_rule_type, ['class' => 'form-control'])}
                </div>
                <div class="">
                    {Html::input('text', 'exclusion_rule_value[]', $exclusion_rules.value[$rule_key], ['class' => 'form-control'])}
                </div> 
                <div class="">
                    <a href="javascript:void(0)" onclick="return removeExclusionRule(this);" class="btn">-</a>
                </div> 
            </div> 
            {/foreach}
            </div>
            <div class="buttons_hours">
                <a href="javascript:void(0)" onclick="return addExclusionRule();" class="btn">{$smarty.const.BUTTON_ADD_MORE}</a>
            </div>
        </div>
    </div>

    <div class="widget box box-no-shadow">
        <div class="widget-header widget-header-theme"><h4>{$smarty.const.GROUP_PLATFORM_URLS}</h4></div>
        <div class="widget-content">
            <table class="tl-grid js-platform-urls">
                <thead>
                <tr>
                    <th style="width:30px">{$smarty.const.HEAD_PLATFORM_URL_STATUS}</th>
                    <th>{$smarty.const.HEAD_PLATFORM_URL_TYPE}</th>
                    <th colspan="2">{$smarty.const.HEAD_PLATFORM_URL}</th>
                    <th style="width: 30px">&nbsp;</th>
                </tr>
                </thead>
                <tbody data-rows-count="{count($pInfo->platform_urls)}">
                {foreach from=$pInfo->platform_urls item=platform_url key=idx}
                    {assign var="row_index" value=$idx+1}
                <tr>
                    <td>{Html::checkbox('platform_urls['|cat:$row_index|cat:'][status]', $platform_url['status'], ['value'=>'1', 'class' => 'js-url_status'])}</td>
                    <td>{Html::dropDownList('platform_urls['|cat:$row_index|cat:'][url_type]', $platform_url['url_type'], $cdn_url_types, ['class' => 'form-control'])}</td>
                    <td>{Html::dropDownList('platform_urls['|cat:$row_index|cat:'][ssl_enabled]',$platform_url['ssl_enabled'],['0'=>'NoSSL','1'=>'SSL','2'=>'Full SSL'], ['class' => 'form-control'])}</td>
                    <td>
                        {Html::textInput('platform_urls['|cat:$row_index|cat:'][url]', $platform_url['url'], ['class' => 'form-control'])}
                        {Html::hiddenInput('platform_urls['|cat:$row_index|cat:'][platforms_url_id]', $platform_url['platforms_url_id'])}
                    </td>
                    <td><button type="button" class="btn js-remove-platform-url">-</button></td>
                </tr>
                {/foreach}
                </tbody>
                <tfoot style="display: none">
                <tr>
                    <td>{Html::checkbox('_unhide_platform_urls[%idx%][status]', 1, ['value'=>'1', 'class' => 'js-url_status_skel'])}</td>
                    <td>{Html::dropDownList('_unhide_platform_urls[%idx%][url_type]', 1, $cdn_url_types, ['value'=>'1', 'class' => 'form-control'])}</td>
                    <td>{Html::dropDownList('platform_urls[%idx%][ssl_enabled]',0,['0'=>'NoSSL','1'=>'SSL','2'=>'Full SSL'], ['class' => 'form-control'])}</td>
                    <td>
                        {Html::textInput('_unhide_platform_urls[%idx%][url]', '', ['class' => 'form-control'])}
                        {Html::hiddenInput('_unhide_platform_urls[%idx%][platforms_url_id]', '')}
                    </td>
                    <td><button type="button" class="btn js-remove-platform-url">-</button></td>
                </tr>
                </tfoot>
            </table>
            <input type="hidden" name="platform_urls_present" value="1">
            &nbsp;
            <div class="buttons_hours">
                <button type="button" class="btn js-add-platform-url">{$smarty.const.TEXT_ADD_MORE}</button>
            </div>
            <!--div class="w-line-row w-line-row-1">
                <div class="wl-td">
                    <label>{$smarty.const.ENTRY_IMAGES_CDN_MODE}</label>
                    {Html::radio('platform_images_cdn_status', ($pInfo->platform_images_cdn_status != 'non_ssl' && $pInfo->platform_images_cdn_status != 'ssl_supported'), ['value'=> 'off', 'class' => 'js_switch_images_cdn_status'])} {$smarty.const.LABEL_IMAGE_CDN_OFF}
                    {Html::radio('platform_images_cdn_status', ($pInfo->platform_images_cdn_status == 'non_ssl'), ['value'=> 'non_ssl', 'class' => 'js_switch_images_cdn_status'])} {$smarty.const.LABEL_IMAGE_CDN_NON_SSL}
                    {Html::radio('platform_images_cdn_status', ($pInfo->platform_images_cdn_status == 'ssl_supported'), ['value'=> 'ssl_supported', 'class' => 'js_switch_images_cdn_status'])} {$smarty.const.LABEL_IMAGE_CDN_SSL_SUPPORTED}
                </div>
            </div>
            <div class="w-line-row w-line-row-1 js-cdn_url_row">
                <div class="wl-td">
                    <label>{$smarty.const.ENTRY_IMAGES_CDN_URL}</label>
                    {Html::input('text','platform_images_cdn_url', $pInfo->platform_images_cdn_url|default:null, ['class' => 'form-control'])}
                </div>
            </div-->
        </div>
    </div>

    {foreach \common\helpers\Hooks::getList('platforms/edit', 'left-column') as $filename}
        {include file=$filename}
    {/foreach}

  </div>
  <div class="cbox-right">

        {if $price_settings}
      <div class="widget box box-no-shadow can_set_marketplace">
          <div class="widget-header widget-header-theme"><h4>{$smarty.const.TEXT_PRICE_SETTINGS}</h4></div>
          <div class="widget-content">
              {if $pInfo->is_default}
                 {$smarty.const.TEXT_USED_OWN_PRICES}
              {else}
                <div class="w-line-row w-line-row-1">
                {Html::checkBox('use_own_prices', $pInfo->platform_settings->use_own_prices, ['class'=> 'use_own_prices', 'label' =>$smarty.const.TEXT_USE_OWN_PRICES])}
                </div>
                <div class="use-owner-prices-box" {if $pInfo->platform_settings->use_own_prices}style="display:none"{/if}>
                    <label>{$smarty.const.TEXT_SELECT_PLATFROM_OWNER}</label>
                    {Html::dropDownList('use_owner_prices', $pInfo->platform_settings->use_owner_prices, $nvPlatforms, ['class' => 'form-control'])}
                </div>
              {/if}
          </div>
      </div>
      {/if}
      <div class="widget box box-no-shadow can_set_marketplace">
          <div class="widget-header widget-header-theme"><h4>{$smarty.const.TEXT_DESCRIPTIONS_SETTINGS}</h4></div>
          <div class="widget-content">
              {if $pInfo->is_default}
                 {$smarty.const.TEXT_USED_OWN_DESCRIPTIONS}
              {else}
                <div class="w-line-row w-line-row-1">
                {Html::checkBox('use_own_descriptions', $pInfo->platform_settings->use_own_descriptions, ['class'=> 'use_own_descriptions', 'label' =>$smarty.const.TEXT_USE_OWN_DESCRIPTIONS])}
                </div>
                <div class="use-owner-desc-box" {if $pInfo->platform_settings->use_own_descriptions}style="display:none"{/if}>
                    <label>{$smarty.const.TEXT_SELECT_PLATFROM_OWNER}</label>
                    {Html::dropDownList('use_owner_descriptions', $pInfo->platform_settings->use_owner_descriptions, $nvPlatforms, ['class' => 'form-control'])}
                </div>
              {/if}
          </div>
      </div>
      {include $pass|cat: '/themes/basic/platforms/edit/warehouses.tpl'}
      {foreach \common\helpers\Hooks::getList('platforms/edit', 'right-column') as $filename}
          {include file=$filename}
      {/foreach}
      </div>
  </div>
</div>
<div class="btn-bar">
    <div class="btn-left"><a href="javascript:void(0)" onclick="return backStatement();" class="btn btn-cancel-foot">{$smarty.const.IMAGE_CANCEL}</a></div>
    <div class="btn-right"><button class="btn btn-confirm">{$smarty.const.IMAGE_SAVE}</button></div>
</div>
{Html::input('hidden', 'id', $pInfo->platform_id)}
</form>

<div id="exclusion_rules_template" style="display: none;">
    <div class="rule_int exclusion_rules">
        <div class="">
            {Html::dropDownList('exclusion_rule_method[]', '', ['AND' => 'AND', 'OR' => 'OR'], ['class' => 'form-control'])}
        </div>
        <div class="">
            {Html::dropDownList('exclusion_rule_type[]', '', $exclusion_rule_type, ['class' => 'form-control'])}
        </div>
        <div class="">
            {Html::input('text', 'exclusion_rule_value[]', '', ['class' => 'form-control'])}
        </div> 
        <div class="">
            <a href="javascript:void(0)" onclick="return removeExclusionRule(this);" class="btn">-</a>
        </div> 
    </div>
</div>
<script>
function addExclusionRule() {
    $('#exclusion_rules_list').append($('#exclusion_rules_template').html());
    return false;
}
function removeExclusionRule(obj) {
    $(obj).parent('div').parent('div.exclusion_rules').remove();
    return false;
}
</script>

<script type="text/javascript" src="{$app->request->baseUrl}/plugins/fileupload/jquery.fileupload.js"></script>
<script>
$('#selectState').autocomplete({
    source: function(request, response) {
        $.ajax({
            url: "{$app->urlManager->createUrl('customers/states')}",
            dataType: "json",
            data: {
                term : request.term,
                country : $("#selectCountry{$keyvar}").val()
            },
            success: function(data) {
                response(data);
            }
        });
    },
    minLength: 0,
    autoFocus: true,
    delay: 0,
    appendTo: '.f_td_state',
    open: function (e, ui) {
      if ($(this).val().length > 0) {
        var acData = $(this).data('ui-autocomplete');
        acData.menu.element.find('a').each(function () {
          var me = $(this);
          var keywords = acData.term.split(' ').join('|');
          me.html(me.text().replace(new RegExp("(" + keywords + ")", "gi"), '<b>$1</b>'));
        });
      }
    },
    select: function(event, ui) {
        $('input[name="city"]').prop('disabled', true);
        if(ui.item.value != null){ 
            $('input[name="city"]').prop('disabled', false);
        }
    }
}).focus(function () {
  $(this).autocomplete("search");
});

function saveItem() {
    $.post("{$app->urlManager->createUrl('platforms/submit')}", $('#save_item_form').serialize(), function (data, status) {
        if (status == "success") {
            $('#platforms_management_data').html(data);
        } else {
            alert("Request error.");
        }
    }, "html");

    return false;
}
function backStatement() {
    window.history.back();
    return false;
}
var shop_statement = '';
{if $pInfo->is_default == 1}
shop_statement = 'default_shop';
{else}
    {if $pInfo->is_marketplace != 1 && $pInfo->is_virtual != 1}
    shop_statement = 'shop';
    {/if}
    {if $pInfo->is_marketplace == 1}
    shop_statement = 'marketplace';
    {/if}
    {if $pInfo->is_virtual == 1}
    shop_statement = 'virtual';
    {/if}
        
{/if}
var default_address_current_state = {if $pInfo->is_default_address == 1}true{else}false{/if};
var default_contact_current_state = {if $pInfo->is_default_contact == 1}true{else}false{/if};

function fn_default_contact_enable_switch (state) {
    if (state) {
        $('.no_default_contact').hide();
        $('.default_contact_no_check').prop('disabled', true);
    }else {
        $('.no_default_contact').show();
        $('.default_contact_no_check').prop('disabled', false);
    }
    default_contact_current_state = state;
}
function fn_default_address_enable_switch(state) {
    if (state) {
        $('.no_default_address').hide();
        $('.default_address_no_check').prop('disabled', true);
    }else {
        $('.no_default_address').show();
        $('.default_address_no_check').prop('disabled', false);
    }
    default_address_current_state = state;
}

function show_platform_boxes() {
    switch (shop_statement) {
        case 'default_shop':
            $('.marketplace_switcher').hide();
            $('.virtual_switcher').hide();
            $('.no_marketplace_or_virtual').show();
            $('.default_shop_selector').hide();
            $('.yes_virtual').hide();
            $('.contact_switcher').hide();
            $('.js_check_is_default_contact').bootstrapSwitch('state', false, true);
            fn_default_contact_enable_switch(false);
            $('.js_check_is_default_address').bootstrapSwitch('state', false, true);
            fn_default_address_enable_switch(false);
          break;
        case 'marketplace':
            $('.marketplace_switcher').show();
            $('.virtual_switcher').hide();
            $('.no_marketplace_or_virtual').hide();
            $('.default_shop_selector').show();
            $('.yes_virtual').hide();
            $('.contact_switcher').show();
            
            $('.js_check_is_default_contact').bootstrapSwitch('state', default_contact_current_state, true);
            fn_default_contact_enable_switch(default_contact_current_state);
            $('.js_check_is_default_address').bootstrapSwitch('state', default_address_current_state, true);
            fn_default_address_enable_switch(default_address_current_state);
          break;
        case 'virtual':
            $('.marketplace_switcher').hide();
            $('.virtual_switcher').show();
            $('.no_marketplace_or_virtual').hide();
            $('.default_shop_selector').hide();
            $('.yes_virtual').show();
            $('.contact_switcher').show();
            
            $('.js_check_is_default_contact').bootstrapSwitch('state', default_contact_current_state, true);
            fn_default_contact_enable_switch(default_contact_current_state);
            $('.js_check_is_default_address').bootstrapSwitch('state', default_address_current_state, true);
            fn_default_address_enable_switch(default_address_current_state);
          break;
        case 'shop':
        default:
            $('.marketplace_switcher').show();
            $('.virtual_switcher').show();
            $('.no_marketplace_or_virtual').show();
            $('.default_shop_selector').show();
            $('.yes_virtual').hide();
            $('.contact_switcher').show();

            $('.js_check_is_default_contact').bootstrapSwitch('state', default_contact_current_state, true);
            fn_default_contact_enable_switch(default_contact_current_state);
            $('.js_check_is_default_address').bootstrapSwitch('state', default_address_current_state, true);
            fn_default_address_enable_switch(default_address_current_state);

          break;
    }
}
$(document).ready(function(){


    $('select[name=sattelit_id]').change(function(){
        if ($(this).val() > 0){
            $('input[name=platform_url]').prop('required', false);
        } else {
            $('input[name=platform_url]').prop('required', true);
        }
    })

    $("select[data-role=multiselect]").multipleSelect();

    var platformEmailUpdated = function(){
        var email = $(this).val();
        $('.js-override-platform-email').each(function(){ $(this).attr('placeholder',email) });
    };
    $('#txtPlatformEmail').on('keyup', platformEmailUpdated);
    platformEmailUpdated.apply($('#txtPlatformEmail').get(0));

    $(window).resize();

    $('.js_check_status').bootstrapSwitch({
        onText: "{$smarty.const.SW_ON}",
        offText: "{$smarty.const.SW_OFF}",
        handleWidth: '20px',
        labelWidth: '24px'
    });

    $('.js_switch_images_cdn_status').on('click',function(){
        if ( this.value=='off' ) {
            $('.js-cdn_url_row').hide();
        }else{
            $('.js-cdn_url_row').show();
        }
    });
    $('.js_switch_images_cdn_status:checked').trigger('click');
    
    $('.js_check_default_platform').bootstrapSwitch({
        onText: "{$smarty.const.SW_ON}",
        offText: "{$smarty.const.SW_OFF}",
        handleWidth: '20px',
        labelWidth: '24px'
    });
    var fn_set_shop_switch = function(state) {
        if (state) {
            shop_statement = 'default_shop';
        } else if (shop_statement == 'default_shop') {
            shop_statement = 'shop';
        }
        show_platform_boxes();
    }
    $('.js_check_default_platform').on('click switchChange.bootstrapSwitch',function(){
        fn_set_shop_switch(this.checked);
    });
    /*$('.js_check_default_platform').each(function() {
        fn_set_shop_switch.apply(this,[this.checked]);
    });*/
    
    
    var fn_ssl_enable_switch = function(state) {
        if (state) {
            $('.js_check_ssl_enabled_true').show();
        }else {
            $('.js_check_ssl_enabled_true').hide();
        }
    }
    $('.js_check_ssl_enabled').on('change',function(){
        fn_ssl_enable_switch( ($('.js_check_ssl_enabled:checked').val() > 0)  );
    });
    $('.js_check_ssl_enabled').each(function() {
        fn_ssl_enable_switch.apply(this,[ ($('.js_check_ssl_enabled:checked').val() > 0) ]);
    });
    $('.js_check_need_login').bootstrapSwitch({
        onText: "{$smarty.const.SW_ON}",
        offText: "{$smarty.const.SW_OFF}",
        handleWidth: '20px',
        labelWidth: '24px'
    });
    $('.js_check_need_login[name="need_login"]').on('click switchChange.bootstrapSwitch',function(){
        if (this.checked) {
            $('.js_exclusion_rules').show();
        } else {
            $('.js_exclusion_rules').hide();
        }
    });
    
    $('.js_check_use_social_login').bootstrapSwitch({
        onText: "{$smarty.const.SW_ON}",
        offText: "{$smarty.const.SW_OFF}",
        handleWidth: '20px',
        labelWidth: '24px'
    });
    
    $('.js_check_is_marketplace').bootstrapSwitch({
        onText: "{$smarty.const.SW_ON}",
        offText: "{$smarty.const.SW_OFF}",
        handleWidth: '20px',
        labelWidth: '24px'
    });
    var fn_marketplace_enable_switch = function(state) {
        if (state) {
            shop_statement = 'marketplace';
        } else if (shop_statement != 'default_shop') {
            shop_statement = 'shop';
        }
        show_platform_boxes();
    }
    $('.js_check_is_marketplace').on('click switchChange.bootstrapSwitch',function(){
        fn_marketplace_enable_switch(this.checked);
    });
    /*$('.js_check_is_marketplace').each(function() {
        fn_marketplace_enable_switch.apply(this,[this.checked], true);
    });*/
    
    
    
    $('.js_check_is_virtual').bootstrapSwitch({
        onText: "{$smarty.const.SW_ON}",
        offText: "{$smarty.const.SW_OFF}",
        handleWidth: '20px',
        labelWidth: '24px'
    });
    
    var buildLink = function(platform_id){
        var ulrs = {json_encode($sattelites_url)};
        if (ulrs[platform_id]){
            $('.virtual_link').attr('href', ulrs[platform_id]+'/?code=' + $('.platform_code').val());
            $('.virtual_link').html(ulrs[platform_id]+'/?code=' + $('.platform_code').val());
        }        
    }
    
    buildLink.call(this, $('select[name=sattelit_id]').val());
    
    $('select[name=sattelit_id]').change(function(){
        buildLink.call(this, $(this).val());
    })
    
    $('input[name=platform_code]').keyup(function(){
        buildLink.call(this, $('select[name=sattelit_id]').val());
    })
    
    $('#lnCodeGenerate').on('click',function(){
        $.getJSON('{\Yii::$app->urlManager->createUrl('platforms/generate-key')}',function( data ) {
            if (data.platform_code) {
                $('.platform_code').val(data.platform_code);
                buildLink.call(this, $('select[name=sattelit_id]').val());
            }
        });
    });
    
    $('.use_own_prices').bootstrapSwitch({
        onText: "{$smarty.const.SW_ON}",
        offText: "{$smarty.const.SW_OFF}",
        handleWidth: '20px',
        labelWidth: '24px',
    });
    var fn_set_prices_switch = function(state) {
        if (state) {
            $('.use-owner-prices-box').hide();
        }else {
            $('.use-owner-prices-box').show();
        }
    }
    $('.use_own_prices').on('switchChange.bootstrapSwitch',function(){
        fn_set_prices_switch(this.checked);
    });
    
    $('.use_own_descriptions').bootstrapSwitch({
        onText: "{$smarty.const.SW_ON}",
        offText: "{$smarty.const.SW_OFF}",
        handleWidth: '20px',
        labelWidth: '24px',
    });
    var fn_set_desc_switch = function(state) {
        if (state) {
            $('.use-owner-desc-box').hide();
        }else {
            $('.use-owner-desc-box').show();
        }
    }
    $('.use_own_descriptions').on('switchChange.bootstrapSwitch',function(){
        fn_set_desc_switch(this.checked);
    });
    
    var fn_virtual_enable_switch = function(state) {
        if (state) {
            shop_statement = 'virtual';
        } else if (shop_statement != 'default_shop') {
            shop_statement = 'shop';
        }
        show_platform_boxes();
    }

    $('.js_check_is_virtual').on('click switchChange.bootstrapSwitch',function(){
        fn_virtual_enable_switch(this.checked);
    });
    
    $('.js_check_is_default_contact').bootstrapSwitch({
        onText: "{$smarty.const.SW_ON}",
        offText: "{$smarty.const.SW_OFF}",
        handleWidth: '20px',
        labelWidth: '24px'
    });
    
    $('.js_check_is_default_contact').on('click switchChange.bootstrapSwitch',function(){
        fn_default_contact_enable_switch(this.checked);
    });
    $('.js_check_is_default_contact').each(function() {
        fn_default_contact_enable_switch.apply(this,[this.checked]);
    });
    
    $('.js_check_is_default_address').bootstrapSwitch({
        onText: "{$smarty.const.SW_ON}",
        offText: "{$smarty.const.SW_OFF}",
        handleWidth: '20px',
        labelWidth: '24px'
    });
    
    $('.js_check_is_default_address').on('click switchChange.bootstrapSwitch',function(){
        fn_default_address_enable_switch(this.checked);
    });
    $('.js_check_is_default_address').each(function() {
        fn_default_address_enable_switch.apply(this,[this.checked]);
    });


    $('.js-url_status').bootstrapSwitch({
        onText: "{$smarty.const.SW_ON}",
        offText: "{$smarty.const.SW_OFF}",
        handleWidth: '20px',
        labelWidth: '24px'
    });
    $('.js-platform-urls').on('add_row',function(){
        var skelHtml = $(this).find('tfoot').html();
        var $body = $(this).find('tbody');
        var counter = parseInt($body.attr('data-rows-count'),10)+1;
        $body.attr('data-rows-count',counter);
        skelHtml = skelHtml.replace(/_unhide_/g,'',skelHtml);
        skelHtml = skelHtml.replace(/%idx%/g, counter,skelHtml);
        $body.append(skelHtml);
        $body.find('.js-url_status_skel').removeClass('js-url_status_skel').addClass('js-url_status').bootstrapSwitch({
            onText: "{$smarty.const.SW_ON}",
            offText: "{$smarty.const.SW_OFF}",
            handleWidth: '20px',
            labelWidth: '24px'
        });
    });
    $('.js-platform-urls').on('click', '.js-remove-platform-url',function(event){
        $(event.target).parents('tr').remove();
    });

    $('.js-add-platform-url').on('click',function(){
        $('.js-platform-urls').trigger('add_row');
    });
    
    show_platform_boxes();
});
</script>


<!-- /Page Content -->
