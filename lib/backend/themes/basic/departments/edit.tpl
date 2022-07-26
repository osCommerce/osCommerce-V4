{use class="yii\helpers\Html"}
<form name="departments" id="edit_department" action="{$app->urlManager->createUrl('departments/update')}" onSubmit="return check_form();" method="post">
{tep_draw_hidden_field('dID', $dInfo->departments_id)}
<div class="tabbable tabbable-custom">
  <ul class="nav nav-tabs">
    <li class="active"><a data-toggle="tab" href="#tab_1_1">{$smarty.const.CATEGORY_WEBSITE}</a></li>
    <li class=""><a data-toggle="tab" href="#tab_1_2">{$smarty.const.CATEGORY_CONTACTS}</a></li>
    <li class=""><a data-toggle="tab" href="#tab_1_3">{$smarty.const.CATEGORY_OPTIONS}</a></li>
    <li class=""><a data-toggle="tab" href="#tab_1_4">{$smarty.const.CATEGORY_FTP}</a></li>
    <li class=""><a data-toggle="tab" href="#tab_1_5">{$smarty.const.CATEGORY_DB}</a></li>
    <li class=""><a data-toggle="tab" href="#tab_1_api">{$smarty.const.CATEGORY_API}</a></li>
  </ul>
  <div class="tab-content">
    <div id="tab_1_1" class="tab-pane active">
      <fieldset class="main">
        <legend>{$smarty.const.CATEGORY_WEBSITE}</legend>
        <table align="center" border="0" cellspacing="2" cellpadding="2" class="formArea tabl_dep" width="100%">
          <tr>
              <td class="main" width="20%"><label>{$smarty.const.ENTRY_ACTIVE}</label></td>
            <td class="main">{Html::radioList('departments_status', (int)$dInfo->departments_status, [1 => TEXT_ACTIVE, 0 => TEXT_NOT_ACTIVE])}</td>
          </tr>
          <tr>
            <td class="main"><label>{$smarty.const.ENTRY_STORE_NAME}</label></td>
            <td class="main">{Html::textInput('departments_store_name', $dInfo->departments_store_name, ['maxlength'=>'128', 'size'=>'32', 'required'=>true, 'class'=>'form-control'])} <span class="fieldRequired">*</span></td>
          </tr>
          <tr>
            <td class="main"><label>{$smarty.const.ENTRY_HTTP_SERVER}</label></td>
            <td class="main">{Html::textInput('departments_http_server', $dInfo->departments_http_server, ['maxlength'=>'128', 'size'=>'32', 'required'=>true, 'class'=>'form-control'])} <span class="fieldRequired">*</span></td>
          </tr>
          <tr>
            <td class="main"><label>{$smarty.const.ENTRY_HTTPS_SERVER}</label></td>
            <td class="main">{Html::textInput('departments_https_server', $dInfo->departments_https_server, ['maxlength'=>'128', 'size'=>'32', 'class'=>'form-control'])}</td>
          </tr>
          <tr>
            <td class="main"><label>{$smarty.const.ENTRY_ENABLE_SSL}</label></td>
            <td class="main">{Html::radioList('departments_enable_ssl', (int)$dInfo->departments_enable_ssl, [1 => TEXT_YES, 0 => TEXT_NO])}</td>
          </tr>
          <tr>
            <td class="main"><label>{$smarty.const.ENTRY_HTTP_CATALOG}</label></td>
            <td class="main">{Html::textInput('departments_http_catalog', $dInfo->departments_http_catalog, ['maxlength'=>'80', 'size'=>'32', 'required'=>true, 'class'=>'form-control'])} <span class="fieldRequired">*</span></td>
          </tr>
          <tr>
            <td class="main"><label>{$smarty.const.ENTRY_HTTPS_CATALOG}</label></td>
            <td class="main">{Html::textInput('departments_https_catalog', $dInfo->departments_https_catalog, ['maxlength'=>'80', 'size'=>'32', 'class'=>'form-control'])}</td>
          </tr>
<!-- {*
          <tr>
            <td class="main"><label>{$smarty.const.ENTRY_DESIGN_TEMPLATE_NAME}</label></td>
            <td class="main">{Html::dropDownList('departments_design_template_name', $dInfo->departments_design_template_name, $dInfo->templates_array, ['class'=>'form-control'])}
              {Html::hiddenInput('old_design_template_name', $dInfo->departments_design_template_name)}</td>
          </tr>
          <tr>
            <td class="main">{$smarty.const.ENTRY_TEMPLATE_MONSTER_TEMPLATE_ID}</td>
            <td class="main">{Html::textInput('departments_template_monster_template_id', $dInfo->departments_template_monster_template_id, ['maxlength'=>'64', 'size'=>'32'])}</td>
          </tr>
          <tr>
            <td valign="top" class="main">{$smarty.const.ENTRY_CUSTOM_DESIGN_REQUIREMENTS}</td>
            <td class="main">{Html::textarea('departments_custom_design_requirements', $dInfo->departments_custom_design_requirements)}</td>
          </tr>
*} -->
          <tr>
            <td class="main"><label>{$smarty.const.ENTRY_PACKAGE}</label></td>
            <td class="main">{Html::dropDownList('packages_id', $dInfo->packages_id, $dInfo->packages_array, ['class'=>'form-control'])}</td>
          </tr>
        </table>
      </fieldset>
    </div>
    <div id="tab_1_2" class="tab-pane">
      <fieldset class="main">
        <legend>{$smarty.const.CATEGORY_CONTACTS}</legend>
        <table align="center" border="0" cellspacing="2" cellpadding="2" class="formArea tabl_dep" width="100%">
          <tr>
              <td class="main" width="20%"><label>{$smarty.const.ENTRY_FIRST_NAME}</label></td>
            <td class="main">{Html::textInput('departments_firstname', $dInfo->departments_firstname, ['maxlength'=>'32', 'size'=>'32', 'required'=>true, 'class'=>'form-control'])} <span class="fieldRequired">*</span></td>
          </tr>
          <tr>
            <td class="main"><label>{$smarty.const.ENTRY_LAST_NAME}</label></td>
            <td class="main">{Html::textInput('departments_lastname', $dInfo->departments_lastname, ['maxlength'=>'32', 'size'=>'32', 'required'=>true, 'class'=>'form-control'])} <span class="fieldRequired">*</span></td>
          </tr>
          <tr>
              <td class="main"><label>{$smarty.const.ENTRY_EMAIL_ADDRESS}</label></td>
            <td class="main">{Html::textInput('departments_email_address', $dInfo->departments_email_address, ['maxlength'=>'96', 'size'=>'32', 'required'=>true, 'class'=>'form-control'])} <span class="fieldRequired">*</span></td>
          </tr>
          <tr>
            <td class="main"><label>{$smarty.const.ENTRY_TELEPHONE_NUMBER}</label></td>
            <td class="main">{Html::textInput('departments_telephone', $dInfo->departments_telephone, ['maxlength'=>'64', 'size'=>'32', 'required'=>true, 'class'=>'form-control'])} <span class="fieldRequired">*</span></td>
          </tr>
          <tr>
            <td class="main"><label>{$smarty.const.ENTRY_STREET_ADDRESS}</label></td>
            <td class="main">{Html::textInput('departments_street_address', $dInfo->departments_street_address, ['maxlength'=>'64', 'size'=>'32', 'required'=>true, 'class'=>'form-control'])} <span class="fieldRequired">*</span></td>
          </tr>
          {if ACCOUNT_SUBURB eq 'true'}
            <tr>
              <td class="main"><label>{$smarty.const.ENTRY_SUBURB}</label></td>
              <td class="main">{Html::textInput('departments_suburb', $dInfo->departments_suburb, ['maxlength'=>'32', 'size'=>'32', 'class'=>'form-control'])}</td>
            </tr>
          {/if}
          <tr>
            <td class="main"><label>{$smarty.const.ENTRY_POST_CODE}</label></td>
            <td class="main">{Html::textInput('departments_postcode', $dInfo->departments_postcode, ['maxlength'=>'80', 'size'=>'32', 'required'=>true, 'class'=>'form-control'])} <span class="fieldRequired">*</span></td>
          </tr>
          <tr>
            <td class="main"><label>{$smarty.const.ENTRY_CITY}</label></td>
            <td class="main">{Html::textInput('departments_city', $dInfo->departments_city, ['maxlength'=>'80', 'size'=>'32', 'required'=>true, 'class'=>'form-control'])} <span class="fieldRequired">*</span></td>
          </tr>
          {if ACCOUNT_STATE eq 'true'}
            <tr>
              <td class="main"><label>{$smarty.const.ENTRY_STATE}</label></td>
              <td class="main">{Html::textInput('departments_state', $dInfo->departments_state, ['maxlength'=>'32', 'size'=>'32', 'required'=>true, 'class'=>'form-control'])} <span class="fieldRequired">*</span></td>
            </tr>
          {/if}
          <tr>
              <td class="main"><label>{$smarty.const.ENTRY_COUNTRY}</label></td>
            <td class="main">{tep_draw_pull_down_menu('departments_country_id', \common\helpers\Country::get_countries(), $dInfo->departments_country_id, 'class="form-control"')} <span class="fieldRequired">*</span></td>
          </tr>
        </table>
      </fieldset>
    </div>
    <div id="tab_1_3" class="tab-pane">
{if {$app->controller->view->showSAP}}
      <fieldset class="main">
        <legend>{$smarty.const.CATEGORY_SAP_OPTIONS}</legend>
        <table align="center" border="0" cellspacing="2" cellpadding="2" class="formArea tabl_dep js-project-codes" width="100%">
          <tbody>
          {foreach $dInfo->project_codes as $project_code}
          <tr>
            <td class="main" width="20%"><label>Project code:</label></td>
            <td class="main">{Html::textInput('project_code[]', $project_code, ['maxlength'=>'32', 'size'=>'32', 'class'=>'form-control'])}</td>
            <td class="main" width="8%"><button class="btn js-removeRow" type="button">-</button></td>
          </tr>
          {/foreach}
          </tbody>
          <tfoot style="display: none">
          <tr>
            <td class="main" width="20%"><label>Project code:</label></td>
            <td class="main">{Html::textInput('project_code[]', '', ['maxlength'=>'32', 'size'=>'32', 'class'=>'form-control'])}</td>
            <td class="main" width="8%"><button class="btn js-removeRow" type="button">-</button></td>
          </tr>
          </tfoot>
          <!--<tr>
              <td class="main"><label>Database:</label></td>
              <td class="main">{*tep_draw_pull_down_menu('sap_database_id', \common\helpers\Sap::getList(), $dInfo->sap_database_id, 'class="form-control"')*}</td>
          </tr>!-->
        </table>
        <div>
          <button class="btn btn-add js-addPCRow" type="button">{$smarty.const.TEXT_ADD_MORE}</button>
        </div>
      </fieldset>
{/if}
    </div>
    <div id="tab_1_4" class="tab-pane">
      <fieldset class="main">
        <legend>{$smarty.const.CATEGORY_FTP}</legend>
        <table align="center" border="0" cellspacing="2" cellpadding="2" class="formArea tabl_dep" width="100%">
          <tr>
            <td class="main" width="20%">&nbsp;</td>
            <td class="main">{Html::radioList('departments_ftp_type', $dInfo->departments_ftp_type, ['ftp' => $smarty.const.TEXT_FTP, 'sftp' => $smarty.const.TEXT_SFTP])}</td>
          </tr>
          <tr>
              <td class="main"><label>{$smarty.const.ENTRY_FTP_HOST}</label></td>
            <td class="main">{Html::textInput('departments_ftp_host', $dInfo->departments_ftp_host, ['maxlength'=>'64', 'size'=>'32', 'class'=>'form-control'])}</td>
          </tr>
          <tr>
              <td class="main"><label>{$smarty.const.ENTRY_FTP_PORT}<label></td>
            <td class="main">{Html::textInput('departments_ftp_port', $dInfo->departments_ftp_port, ['maxlength'=>'32', 'size'=>'16', 'class'=>'form-control'])}</td>
          </tr>
          <tr>
              <td class="main"><label>{$smarty.const.ENTRY_FTP_PATH}</label></td>
            <td class="main">{Html::textInput('departments_ftp_path', $dInfo->departments_ftp_path, ['maxlength'=>'128', 'size'=>'32', 'class'=>'form-control'])}</td>
          </tr>
          <tr>
              <td class="main"><label>{$smarty.const.ENTRY_FTP_USERNAME}</label></td>
            <td class="main">{Html::textInput('departments_ftp_username', $dInfo->departments_ftp_username, ['maxlength'=>'64', 'size'=>'32', 'class'=>'form-control'])}</td>
          </tr>
          <tr>
              <td class="main"><label>{$smarty.const.ENTRY_FTP_PASSWORD}</label></td>
            <td class="main">{Html::textInput('departments_ftp_password', $dInfo->departments_ftp_password, ['maxlength'=>'64', 'size'=>'32', 'class'=>'form-control'])}</td>
          </tr>
          <tr>
              <td class="main"><label>{$smarty.const.ENTRY_FTP_PASV}</label></td>
            <td class="main">{Html::checkbox('departments_ftp_pasv', $dInfo->departments_ftp_pasv)}</td>
          </tr>
        </table>
      </fieldset>
    </div>
    <div id="tab_1_5" class="tab-pane">
      <fieldset class="main">
        <legend>{$smarty.const.CATEGORY_DB}</legend>
        <table align="center" border="0" cellspacing="2" cellpadding="2" class="formArea tabl_dep" width="100%">
          <tr>
              <td class="main" width="20%"><label>{$smarty.const.ENTRY_DB_SERVER_HOST}</label></td>
            <td class="main">{Html::textInput('departments_db_server_host', $dInfo->departments_db_server_host, ['maxlength'=>'64', 'size'=>'32', 'class'=>'form-control'])}(eg, localhost - should not be empty for productive servers)</td>
          </tr>
          <tr>
              <td class="main"><label>{$smarty.const.ENTRY_DB_SERVER_USERNAME}</label></td>
            <td class="main">{Html::textInput('departments_db_server_username', $dInfo->departments_db_server_username, ['maxlength'=>'64', 'size'=>'32', 'class'=>'form-control'])}</td>
          </tr>
          <tr>
              <td class="main"><label>{$smarty.const.ENTRY_DB_SERVER_PASSWORD}</label></td>
            <td class="main">{Html::textInput('departments_db_server_password', $dInfo->departments_db_server_password, ['maxlength'=>'64', 'size'=>'32', 'class'=>'form-control'])}</td>
          </tr>
          <tr>
              <td class="main"><label>{$smarty.const.ENTRY_DB_DATABASE}</label></td>
            <td class="main">{Html::textInput('departments_db_database', $dInfo->departments_db_database, ['maxlength'=>'64', 'size'=>'32', 'class'=>'form-control'])}</td>
          </tr>
          <tr>
              <td class="main"><label>{$smarty.const.ENTRY_USE_PCONNECT}</label></td>
            <td class="main">{Html::textInput('departments_db_use_pconnect', $dInfo->departments_db_use_pconnect, ['maxlength'=>'64', 'size'=>'32', 'class'=>'form-control'])} (false)</td>
          </tr>
          <tr>
              <td class="main"><label>{$smarty.const.ENTRY_STORE_SESSIONS}</label></td>
            <td class="main">{Html::textInput('departments_db_store_sessions', $dInfo->departments_db_store_sessions, ['maxlength'=>'64', 'size'=>'32', 'class'=>'form-control'])} (leave empty '' for default handler or set to 'mysql')</td>
          </tr>
        </table>
      </fieldset>
    </div>
    <div id="tab_1_api" class="tab-pane">
      <fieldset class="main">
        <legend>{$smarty.const.CATEGORY_API}</legend>
        <table align="center" border="0" cellspacing="2" cellpadding="2" class="formArea tabl_dep" width="100%">
          <tr>
            <td class="main" width="20%"><label>{$smarty.const.TEXT_API_DEPARTMENT_URL}</label></td>
            <td class="main">
                <div class="input-group">
                {Html::textInput('api_url', $dInfo->api['department-service-url'], ['maxlength'=>'64', 'size'=>'32', 'class'=>'form-control', 'readonly'=>'readonly', 'id'=>'txtSiteApiUrl'])}
                    <div class="input-group-addon js-clipboard-copy" data-clipboard-target="#txtSiteApiUrl"><i class="icon-copy"></i></div>
                </div>
            </td>
          </tr>
          <tr>
            <td class="main" width="20%"><label>{$smarty.const.TEXT_API_KEY_DEPARTMENT}</label></td>
            <td class="main">
                <div class="input-group">
                    {Html::textInput('api_key', $dInfo->api_key, ['maxlength'=>'64', 'size'=>'32', 'class'=>'form-control','id'=>'txtSiteApiKey'])}
                    <div class="input-group-addon" id="lnSiteApiKeyGenerate" title="{$smarty.const.TEXT_GENERATE|escape:'html'}"><i class="icon-refresh"></i></div>
                    <div class="input-group-addon js-clipboard-copy" data-clipboard-target="#txtSiteApiKey"><i class="icon-copy"></i></div>
                </div>
            </td>
          </tr>
          <tr>
            <td class="main" width="20%"><label>{$smarty.const.TEXT_API_CATEGORIES_CREATE}</label></td>
            <td class="main">{Html::checkbox('api_categories_allow_create', !!$dInfo->api_categories_allow_create, ['value'=>'1', 'class'=>'check_on_off form-control'])}</td>
          </tr>
          <tr>
            <td class="main" width="20%"><label>{$smarty.const.TEXT_API_CATEGORIES_UPDATE}</label></td>
            <td class="main">{Html::checkbox('api_categories_allow_update', !!$dInfo->api_categories_allow_update, ['value'=>'1', 'class'=>'check_on_off form-control'])}</td>
          </tr>
          <tr>
            <td class="main" width="20%"><label>{$smarty.const.TEXT_API_PRODUCTS_CREATE}</label></td>
            <td class="main">{Html::checkbox('api_products_allow_create', !!$dInfo->api_products_allow_create, ['value'=>'1', 'class'=>'check_on_off form-control'])}</td>
          </tr>
          <tr>
            <td class="main" width="20%"><label>{$smarty.const.TEXT_API_PRODUCTS_UPDATE}</label></td>
            <td class="main">{Html::checkbox('api_products_allow_update', !!$dInfo->api_products_allow_update, ['value'=>'1', 'class'=>'check_on_off form-control'])}</td>
          </tr>
          <tr>
            <td class="main" width="20%"><label>{$smarty.const.TEXT_API_PRODUCTS_REMOVE_OWNED}</label></td>
            <td class="main">{Html::checkbox('api_products_allow_remove_owned', !!$dInfo->api_products_allow_remove_owned, ['value'=>'1', 'class'=>'check_on_off form-control'])}</td>
          </tr>
          <tr>
            <td class="main" width="20%"><label>{$smarty.const.TEXT_SOAP_PRODUCT_CUSTOMIZE_FLAGS}</label></td>
            <td class="main">{Html::checkbox('api_products_update_custom_flags', !!$dInfo->api_products_update_custom_flags, ['value'=>'1', 'class'=>'default_switcher js-soap_custom_flags','data-rel'=>'js-soap-custom-flags_rel'])}</td>
          </tr>
          <tr class="js-soap-custom-flags_rel">
            <td class="main" width="20%">&nbsp;</td>
            <td class="main">
                <table class="table tabl-res table-striped table-hover table-responsive table-bordered table-switch-on-off double-grid">
                    <thead>
                    <tr>
                        <th>{$smarty.const.TEXT_SOAP_PRODUCT_CUSTOM_HEAD_KEY}</th>
                        <th width="10%">{$smarty.const.TEXT_SOAP_PRODUCT_CUSTOM_HEAD_SERVER}</th>
                        <th width="10%">{$smarty.const.TEXT_SOAP_PRODUCT_CUSTOM_HEAD_SERVER_OWN}</th>
                    </tr>
                    </thead>
                    <tbody>
                    {foreach $dInfo->apiProductFlags as $flagInfo}
                        <tr>
                            <td>{$flagInfo['label']}</td>
                            <td>{if $flagInfo['server'] && !$flagInfo['server_disable']}{Html::checkbox('api_products_update_custom['|cat:$flagInfo['server']|cat:']', !!$dInfo->api_products_update_custom[$flagInfo['server']], ['value'=>'1', 'class' => 'default_switcher'])}{else}&nbsp;{/if}</td>
                            <td>{if $flagInfo['server_own']}{Html::checkbox('api_products_update_custom['|cat:$flagInfo['server_own']|cat:']', !!$dInfo->api_products_update_custom[$flagInfo['server_own']], ['value'=>'1', 'class' => 'default_switcher'])}{else}&nbsp;{/if}</td>
                        </tr>
                    {/foreach}
                    </tbody>
                </table>
            </td>
          </tr>
          {if $dInfo->api['shareDepartmentsList'] && count($dInfo->api['shareDepartmentsList']['items'])>0}
            <tr>
                <td class="main" valign="top"><label>{$smarty.const.TEXT_API_CREATE_SHARE_ASSIGN}</label></td>
                <td class="main mmsel">
                    <label ><input name="api[createShareAll]" {if $dInfo->api['createShareAll']=='all'}checked{/if} value="all" type="radio">{$smarty.const.TEXT_ALL}</label>
                    <label ><input name="api[createShareAll]" {if $dInfo->api['createShareAll']!='all'}checked{/if} value="selected" type="radio">{$smarty.const.IMAGE_SELECT}</label>
                    <div class="js-optSelectShareDepartments">
                      {Html::dropDownList('api[createShare]',$dInfo->api['shareDepartmentsList']['selected'],$dInfo->api['shareDepartmentsList']['items'],$dInfo->api['shareDepartmentsList']['options'])}
                    </div>
                </td>
            </tr>
          {/if}
          {if is_array($dInfo->api['client_statuses']) && count($dInfo->api['client_statuses'])>0}
          <tr>
            <td class="main" valign="top"><label>{$smarty.const.TEXT_API_SYNCHRONIZATION_ORDER_STATUS_MAPPING}</label></td>
            <td class="main">
              <table class="js-status-mapping">
                <tr>
                  <th>{$smarty.const.TEXT_API_SYNCHRONIZATION_ORDER_STATUS_MAPPING_CLIENT}</th>
                  <th>{$smarty.const.TEXT_API_SYNCHRONIZATION_ORDER_STATUS_MAPPING_SERVER}</th>
                </tr>
                {foreach $dInfo->api['client_statuses'] as $_idx=>$order_status}
                    {if strpos($order_status.id,'group')===0}
                      <tr>
                        <td><b>{$order_status.text}</b></td>
                        <td>&nbsp;</td>
                      </tr>
                    {else}
                      <tr>
                        <td>{$order_status.text}</td>
                        <td>{Html::dropDownList('api[client_status_map]['|cat:$order_status._id|cat:']',$dInfo->api['order_status_mapped'][$order_status._id],$dInfo->api['local_statuses'],['class'=>'form-control js-status-map'])}</td>
                      </tr>
                    {/if}
                    {if !isset($dInfo->api['client_statuses'][$_idx+1]) or strpos($dInfo->api['client_statuses'][$_idx+1]['id'],'group')===0}
                      <tr class="js-create-on-client-row">
                        <td><u>{$smarty.const.TEXT_API_SYNCHRONIZATION_ORDER_STATUS_CREATE_ON_CLIENT}</u></td>
                        <td>{Html::dropDownList('api[create_on_client]['|cat:$order_status.group_id|cat:'][]',$dInfo->api['client_create_status_map'][$order_status.group_id][0],$dInfo->api['local_statuses_wo_create'],['class'=>'form-control js-status-on-client js-status-map'])}</td>
                      </tr>
                      {if isset($dInfo->api['client_create_status_map'][$order_status.group_id]) && is_array($dInfo->api['client_create_status_map'][$order_status.group_id]) && count($dInfo->api['client_create_status_map'][$order_status.group_id])>0}
                          {for $__idx=1;$__idx<count($dInfo->api['client_create_status_map'][$order_status.group_id]); $__idx++}
                            <tr class="js-create-on-client-row">
                              <td><u>{$smarty.const.TEXT_API_SYNCHRONIZATION_ORDER_STATUS_CREATE_ON_CLIENT}</u></td>
                              <td>{Html::dropDownList('api[create_on_client]['|cat:$order_status.group_id|cat:'][]',$dInfo->api['client_create_status_map'][$order_status.group_id][$__idx],$dInfo->api['local_statuses_wo_create'],['class'=>'form-control js-status-on-client js-status-map'])}</td>
                            </tr>
                          {/for}
                      {/if}
                    {/if}
              {/foreach}
              </table>
            </td>
          </tr>
          {/if}
          {if 1}
              <tr>
                  <td class="main" width="20%"><label>{$smarty.const.TEXT_OUTGOING_PRICE_FORMULA}</label></td>
                  <td class="main">
                      <div class="input-group">
                          {Html::textInput('api_outgoing_price_formula_text', $dInfo->api_outgoing_price_formula_text, ['maxlength'=>'64', 'size'=>'32', 'class'=>'form-control', 'readonly'=>'readonly', 'id'=>'txtSiteApiPriceFormulaText'])}
                          {Html::hiddenInput('api_outgoing_price_formula', $dInfo->api_outgoing_price_formula, ['id'=>'txtSiteApiPriceFormula'])}
                          <div class="input-group-addon js-price-formula" data-formula-rel="#txtSiteApiPriceFormula" data-formula-allow-params=""><i class="icon-money"></i></div>
                      </div>
                  </td>
              </tr>
              <tr>
                  <td class="main"><label>{$smarty.const.TEXT_OUTGOING_PRICE_DISCOUNT}</label></td>
                  <td class="main">
                      {Html::textInput('api_outgoing_price_discount', $dInfo->api_outgoing_price_discount, ['maxlength'=>'64', 'size'=>'32', 'class'=>'form-control'])}
                  </td>
              </tr>
              <tr>
                  <td class="main"><label>{$smarty.const.TEXT_OUTGOING_PRICE_SURCHARGE}</label></td>
                  <td class="main">
                      {Html::textInput('api_outgoing_price_surcharge', $dInfo->api_outgoing_price_surcharge, ['maxlength'=>'64', 'size'=>'32', 'class'=>'form-control'])}
                  </td>
              </tr>
              <tr>
                  <td class="main"><label>{$smarty.const.TEXT_OUTGOING_PRICE_MARGIN}</label></td>
                  <td class="main">
                      {Html::textInput('api_outgoing_price_margin', $dInfo->api_outgoing_price_margin, ['maxlength'=>'64', 'size'=>'32', 'class'=>'form-control'])}
                  </td>
              </tr>
          {/if}
        </table>
      </fieldset>
    </div>
  </div>
</div>
<div class="btn-bar">
  <div class="btn-left"><a href="{$app->urlManager->createUrl('departments/index')}?dID={$dInfo->departments_id}" class="btn btn-cancel-foot">{$smarty.const.IMAGE_CANCEL}</a></div>
  <div class="btn-right"><button class="btn btn-confirm">{$smarty.const.IMAGE_UPDATE}</button></div>
</div>
</form>
<script language="javascript"><!--

  function check_form() {
    var error = 0;
    var error_message = "{$smarty.const.JS_ERROR}";

    var departments_store_name = document.departments.departments_store_name.value;
    var departments_http_server = document.departments.departments_http_server.value;
    var departments_http_catalog = document.departments.departments_http_catalog.value;

    var departments_firstname = document.departments.departments_firstname.value;
    var departments_lastname = document.departments.departments_lastname.value;
    var departments_email_address = document.departments.departments_email_address.value;
    var departments_telephone = document.departments.departments_telephone.value;
    var departments_street_address = document.departments.departments_street_address.value;
    var departments_postcode = document.departments.departments_postcode.value;
    var departments_city = document.departments.departments_city.value;

    if (departments_store_name = "" || departments_store_name.length < 2) {
      error_message = error_message + "{$smarty.const.JS_STORE_NAME|escape:'javascript'}";
      error = 1;
    }

    if (departments_http_server = "" || departments_http_server.length < 3) {
      error_message = error_message + "{$smarty.const.JS_HTTP_SERVER|escape:'javascript'}";
      error = 1;
    }

    if (departments_http_catalog = "" || departments_http_catalog.length < 1) {
      error_message = error_message + "{$smarty.const.JS_HTTP_CATALOG|escape:'javascript'}";
      error = 1;
    }

    if (departments_firstname = "" || departments_firstname.length < {$smarty.const.ENTRY_FIRST_NAME_MIN_LENGTH}) {
      error_message = error_message + "{$smarty.const.JS_FIRST_NAME|escape:'javascript'}";
      error = 1;
    }

    if (departments_lastname = "" || departments_lastname.length < {$smarty.const.ENTRY_LAST_NAME_MIN_LENGTH}) {
      error_message = error_message + "{$smarty.const.JS_LAST_NAME|escape:'javascript'}";
      error = 1;
    }

    if (departments_email_address = "" || departments_email_address.length < {$smarty.const.ENTRY_EMAIL_ADDRESS_MIN_LENGTH}) {
      error_message = error_message + "{$smarty.const.JS_EMAIL_ADDRESS|escape:'javascript'}";
      error = 1;
    }

    if (departments_telephone = "" || departments_telephone.length < {$smarty.const.ENTRY_TELEPHONE_MIN_LENGTH}) {
      error_message = error_message + "{$smarty.const.JS_TELEPHONE|escape:'javascript'}";
      error = 1;
    }

    if (departments_street_address == "" || departments_street_address.length < {$smarty.const.ENTRY_STREET_ADDRESS_MIN_LENGTH}) {
      error_message = error_message + "{$smarty.const.JS_ADDRESS|escape:'javascript'}";
      error = 1;
    }

    if (departments_postcode == "" || departments_postcode.length < {$smarty.const.ENTRY_POSTCODE_MIN_LENGTH}) {
      error_message = error_message + "{$smarty.const.JS_POST_CODE|escape:'javascript'}";
      error = 1;
    }

    if (departments_city == "" || departments_city.length < {$smarty.const.ENTRY_CITY_MIN_LENGTH}) {
      error_message = error_message + "{$smarty.const.JS_CITY|escape:'javascript'}";
      error = 1;
    }

  {if ACCOUNT_STATE eq 'true'}
    if (document.departments.elements['departments_state'].type != "hidden") {
      if (document.departments.departments_state.value == '' || document.departments.departments_state.value.length < {$smarty.const.ENTRY_STATE_MIN_LENGTH}) {
        error_message = error_message + "{$smarty.const.JS_STATE|escape:'javascript'}";
        error = 1;
      }
    }
  {/if}

    if (document.departments.departments_country_id.value == 0) {
      error_message = error_message + "{$smarty.const.JS_COUNTRY|escape:'javascript'}";
      error = 1;
    }

    if (error == 1) {
      alert(error_message);
      return false;
    } else {
      $.post("{$app->urlManager->createUrl('departments/update')}", $('#edit_department').serialize(), function(data, status) {
        if (status == "success") {
          if (data == 'OK') {
            window.location.href = "{$app->urlManager->createUrl('departments/index')}?dID={$dInfo->departments_id}";
          } else {
            $("#messageStack").html('<div class="alert alert-warning fade in"><i data-dismiss="alert" class="icon-remove close"></i>' + data + '</div>');
          }
        } else {
          alert("Request error.");
        }
      },"html");
      return false;
    }
  }
    $(document).ready(function(){
      $('#lnSiteApiKeyGenerate').on('click',function(){
        $.getJSON('{\Yii::$app->urlManager->createUrl('api/generate-key')}',function( data ) {
            if (data.api_key) $('#txtSiteApiKey').val(data.api_key);
        });
      });
      $('.js-project-codes').on('click','.js-removeRow', function(event){
          $(event.target).parents('tr').remove();
      });
      $('.js-addPCRow').on('click', function(){
          var skelHtml = $('.js-project-codes tfoot').html();
          $('.js-project-codes tbody').append(skelHtml);
          $('.js-project-codes tbody input[type="text"]').last().focus();
      });

      $('.js-status-mapping').on('change','.js-status-map',function(event){
          var $target = $(event.target);
          var target_value = $target.val() || '';
          if (target_value!='' && target_value!='0') {
              $('.js-status-map').not($target).each(function () {
                  if ($(this).val() == target_value) {
                      $(this).val('').trigger('change');
                  }
              });
          }
          if ( $target.hasClass('js-status-on-client') ){
              var $collection = $('.js-status-on-client').filter('[name="'+$target.attr('name')+'"]');
              if (target_value=='' || target_value=='0'){
                  if ( $collection.length>1 ) {
                      $target.parents('.js-create-on-client-row').remove();
                  }
              }else{
                  var hasEmpty = false;
                  $collection.each( function(){
                      var val = $(this).val()||'';
                      if (val==='' || val==='0'){ hasEmpty = true; }
                  } );
                  if ( !hasEmpty ) {
                      var $selectRow = $target.parents('.js-create-on-client-row');
                      var $newTr = $selectRow.clone();
                      $newTr.find('select').val('');
                      $selectRow.after($newTr);
                  }
              }
          }else{
              if (target_value.indexOf('new_') === 0) {
                  var matchId = $target.attr('name').match(/\[(\d+)\]$/);
                  $.post('{\Yii::$app->urlManager->createUrl(['departments/create-locally-api-client-status', 'dID'=>$dInfo->departments_id])}', {
                      'clientStatusId': matchId[1],
                      'localStatusGroupId': $target.val().replace('new_in_group_', '')
                  }, function (result) {
                      if (result.dropDownList) {
                          var newSelectVariants = $(result.dropDownList).html();
                          $('.js-status-map').each(function () {
                              var currentValue = $(this).val();
                              if ($target.attr('name') == $(this).attr('name')) {
                                  currentValue = '' + result.status_id;
                              }
                              $(this).html(newSelectVariants);
                              $(this).val(currentValue).trigger('change');
                          });
                      }
                  });
                  return;
              }
          }
      });
      // -- add blank select after create on client
      var $additionalI = $('.js-status-on-client').filter(function(i, el){
          var val = $(el).val()||'';
          return !(val==='' || val==='0');
      });

      var __checkCreated = { };
      $($additionalI.get().reverse()).each(function(){
          if (__checkCreated[this.name]) return;
          var $selectRow = $(this).parents('.js-create-on-client-row');
          var $newTr = $selectRow.clone();
          $newTr.find('select').val('');
          $selectRow.after($newTr);
          __checkCreated[this.name] = this.name;
      });
      // -- add blank select after create on client

        $(".check_on_off").bootstrapSwitch({
            onText: "{$smarty.const.SW_ON}",
            offText: "{$smarty.const.SW_OFF}"
        });

        $('.js-soap_custom_flags').each(function(){
            if (!this.checked) $('.'+$(this).attr('data-rel')).hide();
        });

        $('.default_switcher').bootstrapSwitch({
            onText: "{$smarty.const.SW_ON}",
            offText: "{$smarty.const.SW_OFF}",
            handleWidth: '20px',
            labelWidth: '24px',
            onSwitchChange: function () {
                if($(this).hasClass('js-soap_custom_flags')){
                    if($(this).is(':checked')){
                        $('.'+$(this).attr('data-rel')).show();
                    }else{
                        $('.'+$(this).attr('data-rel')).hide();
                    }
                }
            }
        });
    })
//--></script>
<script src="plugins/clipboard-js/clipboard.min.js"></script>
<link href="plugins/multiple-select/multiple-select.css" rel="stylesheet" type="text/css" />
<script src="plugins/multiple-select/multiple-select.js"></script>
<script type="text/javascript">
    $(document).ready( function(){
        var clipboard = new ClipboardJS('.js-clipboard-copy');
        clipboard.on('success', function(e) {
            e.clearSelection();
            var $tooltipTarget = $(e.trigger);
            $tooltipTarget.tooltip('show');
            setTimeout(function(){
                $tooltipTarget.tooltip('hide');
            },2000);
        });
        $('.js-clipboard-copy').tooltip({
            title:'{$smarty.const.TEXT_COPIED_TO_CLIPBOARD|escape:'javascript'}',
            placement:'left',
            trigger:'manual'
        });

        $('.js-price-formula').on('click', function(){
            var field = $(this).data('formula-rel');
            var allowed_params = $(this).data('formula-allow-params')||'';

            bootbox.dialog({ message: '<iframe src="{$app->urlManager->createUrl(['popups/price-formula-editor','s'=>(float)microtime()])}&formula_input='+encodeURIComponent(field)+'&allowed_params='+encodeURIComponent(allowed_params)+'" width="900px" height="420px" style="border:0"/>' });
            bootbox.setDefaults( { size:'large', onEscape:true, backdrop:true });
        });

        window.priceFormulaRetrieve = function (inputSelector){
            var jsonString = $(inputSelector).val();
            if ( jsonString ) {
                return JSON.parse(jsonString);
            }
            return { };
        };

        window.priceFormulaUpdate = function (inputSelector, formulaObject ) {
            $(inputSelector).val( JSON.stringify(formulaObject) );
            $('#txtSiteApiPriceFormulaText').val($.trim(formulaObject.text));
            bootbox.hideAll();
        };

        $("select.assign-multi-checkbox").multipleSelect({
            multiple: true,
            filter: true,
            selectAll:false,
            allSelected:'',
            minimumCountSelected: 10
        });
        var onRelSelectShare = function(show)
        {
            if (show){
                $('.js-optSelectShareDepartments').show();
            }else{
                $('.js-optSelectShareDepartments').hide();
            }
        };
        $('input[name="api[createShareAll]"]').on('change',function(){
            onRelSelectShare(this.value=='selected' && $(this).is(':checked'));
        });
        onRelSelectShare($('input[name="api[createShareAll]"]').filter('[value="selected"]').is(':checked'));
    } );
</script>