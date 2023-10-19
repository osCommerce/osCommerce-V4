{use class="yii\helpers\Html"}
<form name="departments" id="edit_department" action="{$app->urlManager->createUrl('departments/update')}" onSubmit="return check_form();" method="post">
{tep_draw_hidden_field('dID', $dInfo->departments_id)}
<div class="tabbable tabbable-custom">
  <ul class="nav nav-tabs">
    <li class="active" data-bs-toggle="tab" data-bs-target="#tab_1_1"><a>{$smarty.const.CATEGORY_WEBSITE}</a></li>
    <li class="" data-bs-toggle="tab" data-bs-target="#tab_1_2"><a>{$smarty.const.CATEGORY_CONTACTS}</a></li>
    <li class="" data-bs-toggle="tab" data-bs-target="#tab_1_3"><a>{$smarty.const.CATEGORY_OPTIONS}</a></li>
    <li class="" data-bs-toggle="tab" data-bs-target="#tab_1_4"><a>{$smarty.const.CATEGORY_FTP}</a></li>
    <li class="" data-bs-toggle="tab" data-bs-target="#tab_1_5"><a>{$smarty.const.CATEGORY_DB}</a></li>
    {if \common\helpers\Acl::checkExtensionAllowed('SoapServer', 'allowed')}
      <li class="" data-bs-toggle="tab" data-bs-target="#tab_1_api"><a>{$smarty.const.CATEGORY_API}</a></li>
    {/if}
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
              <td class="main"><label>Alias</label></td>
              <td class="main">{str_replace(';', '<br>', $dInfo->alias)}</td>
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
    {if $extSoapServer = \common\helpers\Acl::checkExtensionAllowed('SoapServer', 'allowed')}
    <div id="tab_1_api" class="tab-pane">
        {$extSoapServer::onDepartmentSettingsTab($dInfo)}
    </div>
    {/if}
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

      $('.js-project-codes').on('click','.js-removeRow', function(event){
          $(event.target).parents('tr').remove();
      });
      $('.js-addPCRow').on('click', function(){
          var skelHtml = $('.js-project-codes tfoot').html();
          $('.js-project-codes tbody').append(skelHtml);
          $('.js-project-codes tbody input[type="text"]').last().focus();
      });

        $(".check_on_off").bootstrapSwitch({
            onText: "{$smarty.const.SW_ON}",
            offText: "{$smarty.const.SW_OFF}"
        });


    })
//--></script>
