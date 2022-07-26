<!--=== Page Header ===-->
<div class="page-header">
  <div class="page-title">
    <h3>{$app->controller->view->headingTitle}</h3>
  </div>
</div>
<!-- /Page Header -->

<div id="voucher_management_data">
  <form id="send_voucher_form" name="send_voucher" method="post" action="{$send_coupon_action}" onsubmit="return confirmSend(this);">
    <input type="hidden" name="confirm_mul" value="0">
    <table cellspacing="0" cellpadding="0" width="100%">
      <tr>
        <td class="label_name">{$smarty.const.TEXT_CUSTOMER}</td>
        <td class="label_value">{tep_draw_pull_down_menu('customers_email_address', $customers_variants, $customers_selected,'class="form-control" required="required"')}</td>
      </tr>
      <tr>
        <td class="label_name">{$smarty.const.TEXT_SUBJECT}</td>
        <td class="label_value">{tep_draw_input_field('email_subject',$email_subject,'class="form-control"')}</td>
      </tr>
      <tr>
        <td class="label_name">{$smarty.const.TEXT_MESSAGE}</td>
        <td class="label_value">
          {if $smarty.const.EMAIL_USE_HTML eq 'true'}
            {tep_draw_textarea_field('email_content','',45,15,$email_text,'class="ckeditor form-control"')}
          {else}
            {tep_draw_textarea_field('email_content','',45,15,$email_text,'class="form-control"')}
          {/if}
        </td>
      </tr>
    </table>
    <div class="btn-bar edit-btn-bar">
      <div class="btn-left"><a href="javascript:void(0)" class="btn btn-cancel-foot" onclick="return backStatement()">{$smarty.const.IMAGE_CANCEL}</a></div>
      <div class="btn-right"><button class="btn btn-primary">{$smarty.const.IMAGE_SEND}</button></div>
    </div>
  </form>
</div>
<script type="text/javascript">
    function confirmSend(form) {
      var  $el = $(form.customers_email_address);
      if ($el) {
        if ($el.val() == '***' || $el.val() == '**D' ) {
          var res = window.confirm('{$smarty.const.CONFIRM_SEND_TO_MULTIPLE_EMAILS|escape:'html'}');
          if (res){
            $(form.confirm_mul).val(1);
          } else {
            $(form.confirm_mul).val(0);
          }
        }
      }
      return true;
    }
    function backStatement() {
        window.history.back();
        return false;
    }
    $(document).ready(function(){
        $('#send_voucher_form').popUp({
            box: "<div class='popup-box-wrap'><div class='around-pop-up'></div><div class='popup-box'><div class='popup-heading cat-head'>{$smarty.const.TEXT_SEND_COUPON_POPUP}</div><div class='pop-up-content'><div class='preloader'></div></div></div></div>",
        });
    });
</script>