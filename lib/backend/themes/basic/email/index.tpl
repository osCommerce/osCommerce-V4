<!--=== Page Header ===-->
<div class="page-header">
    <div class="page-title">
        <h3>{$app->controller->view->headingTitle}</h3>
    </div>
</div>
<!-- /Page Header -->

<form id="send_form" name="send_form" onSubmit="return sendForm();">
    
    <div>
        <label>{$smarty.const.TEXT_CUSTOMER}</label>
        {tep_draw_pull_down_menu('customers_email_address', $customers, '', 'class="form-control"')}
    </div>
    
    <div>
        <label>{$smarty.const.TEXT_FROM}</label>
        {tep_draw_input_field('from', EMAIL_FROM, 'class="form-control"')}
    </div>
    
    <div>
        <label>{$smarty.const.TEXT_SUBJECT}</label>
        {tep_draw_input_field('subject', '', 'class="form-control"')}
    </div>
    
    <div>
        <label>{$smarty.const.TEXT_MESSAGE}</label>
        {tep_draw_textarea_field('message', 'soft', '70', '15', '', 'class="ckeditor form-control"')}
    </div>
    
    <div class="btn-wr">
        <button class="btn btn-primary">{$smarty.const.IMAGE_SEND_EMAIL}</button>
    </div>
</form>
<script type="text/javascript">
function sendForm() {
    return false
}
</script>