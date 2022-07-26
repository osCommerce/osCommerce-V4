<!--=== Page Header ===-->
<div class="page-header">
  <div class="page-title">
    <h3>{$app->controller->view->headingTitle}</h3>
  </div>
</div>
<!-- /Page Header -->
{if isset($message_stack_output) && $message_stack_output}{$message_stack_output}{/if}
<div class="row">
  <div class="col-md-12">
    <div class="widget box">
      <div class="widget-header">
        <h4><i class="icon-reorder"></i><span id="easypopulate_management_title">{$smarty.const.HEADING_TITLE}</span></h4>
        <div class="toolbar no-padding">
          <div class="btn-group">
            <span id="easypopulate_management_collapse" class="btn btn-xs widget-collapse"><i class="icon-angle-down"></i></span>
          </div>
        </div>
      </div>
      <div class="widget-content fields_style dis_module">
        <div class="scroll-table-workaround">

{tep_draw_form('exact_online', 'exact_online/update')}
  <div class="main" style="width:900px">
    <table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr style="display:none;">
        <td class="smallText">{$smarty.const.TEXT_EXACT_BASE_URL}</td>
        <td class="smallText">{tep_draw_input_field('EXACT_BASE_URL', EXACT_BASE_URL, 'readonly')}</td>
      </tr>
      <tr style="display:none;">
        <td class="smallText">{$smarty.const.TEXT_EXACT_CLIENT_ID}</td>
        <td class="smallText">{tep_draw_input_field('EXACT_CLIENT_ID', EXACT_CLIENT_ID, 'readonly')}</td>
      </tr>
      <tr style="display:none;">
        <td class="smallText">{$smarty.const.TEXT_EXACT_CLIENT_SECRET}</td>
        <td class="smallText">{tep_draw_input_field('EXACT_CLIENT_SECRET', EXACT_CLIENT_SECRET, 'readonly')}</td>
      </tr>
<!-- {*
      <tr>
        <td class="smallText">{$smarty.const.TEXT_EXACT_RETURN_URL}</td>
        <td class="smallText">{str_replace(SID, '', tep_href_link('exact_online/oauth'))}</td>
      </tr>
*} -->
      <tr>
        <td class="smallText">{$smarty.const.TEXT_EXACT_CURRENT_DIVISION}</td>
        <td class="smallText">{tep_draw_pull_down_menu('EXACT_CURRENT_DIVISION', [], EXACT_CURRENT_DIVISION,'disabled')}</td>
      </tr>
      <tr>
        <td class="smallText">{$smarty.const.TEXT_EXACT_DESCRIPTION_FIELD}</td>
        <td class="smallText">{tep_draw_pull_down_menu('EXACT_DESCRIPTION_FIELD', [], EXACT_DESCRIPTION_FIELD,'disabled')}</td>
      </tr>
      <tr>
        <td class="smallText">{$smarty.const.TEXT_EXACT_ORDERNUMBER_SHIFT}</td>
        <td class="smallText">{tep_draw_input_field('EXACT_ORDERNUMBER_SHIFT', EXACT_ORDERNUMBER_SHIFT,'disabled')}</td>
      </tr>
      <tr>
        <td class="smallText">{$smarty.const.TEXT_EXACT_0_VAT_CODE}</td>
        <td class="smallText">{tep_draw_input_field('EXACT_0_VAT_CODE', EXACT_0_VAT_CODE,'disabled')}</td>
      </tr>
      <tr>
        <td class="smallText">{$smarty.const.TEXT_EXACT_ORDER_STATUSES_SYNCED}</td>
        <td class="smallText">
      </tr>
      <tr>
        <td colspan="2">{tep_draw_separator('pixel_trans.gif', '1', '10')}</td>
      </tr>
      <tr>
        <td width="25%" class="smallText"><b>{$smarty.const.TEXT_CONNECTOR_STATUS}</b></td>
        <td class="smallText"><label {if (EXACT_CONNECTOR_STATUS == 'True')}style="font-weight:bold;color:#0C0"{/if}>{tep_draw_radio_field('EXACT_CONNECTOR_STATUS', 'True', EXACT_CONNECTOR_STATUS == 'True', '', 'id="s1" disabled')}&nbsp;{$smarty.const.TEXT_EXACT_ON}</label>&nbsp;&nbsp;<label {if (EXACT_CONNECTOR_STATUS == 'False')}style="font-weight:bold;color:#C00"{/if}>{tep_draw_radio_field('EXACT_CONNECTOR_STATUS', 'False', EXACT_CONNECTOR_STATUS == 'False', '', 'id="s0"')}&nbsp;{$smarty.const.TEXT_EXACT_OFF}</label></td>
      </tr>
    </table>
    <div class="btn-bar edit-btn-bar">
      <div class="btn-left"><a href="javascript:void(0)" class="btn btn-primary" disabled>{$smarty.const.TEXT_EXACT_PROCESS_AUTHORIZATION}</a></div>
      <div class="btn-right"><button class="btn btn-primary" disabled>{$smarty.const.IMAGE_UPDATE}</button></div>
    </div>
  </div>
</form>

        </div>
      </div>
    </div>
  </div>
</div>
