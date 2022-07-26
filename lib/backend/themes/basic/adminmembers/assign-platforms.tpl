{use class="common\helpers\Html"}
<!--=== Page Content ===-->
<div id="assign_admin_platform_data">
<!--===Customers List ===-->
<form name="save_item_form" id="save_item_form" onSubmit="return saveItem();">
<div class="box-wrap">
    <div class="create-or-wrap after create-cus-wrap">
        <div class="widget box box-no-shadow" style="margin-bottom: 0;">
            <div class="widget-header widget-header-review">
                <h4>{$smarty.const.BOX_HEADING_FRONENDS}</h4>
            </div>
            <div class="widget-content">
                <div class="after">
                    {foreach $platforms as $platform_info}
                      <div class="row_fields after">
                        <label>{Html::checkbox('platform_id[]', isset($assigned_platforms[$platform_info['id']]), ['class' => 'platforms-check', 'value' => $platform_info['id']])}
                         <span>{$platform_info['text']}</span></label>
                      </div>
                    {/foreach}
                      <div class="row_fields after">
                        <label>{Html::checkbox('pall', false, ['class' => '', 'value' => 0, 'id' =>'p_all'])}
                         <span>{$smarty.const.TEXT_CHECK_ALL}</span></label>
                      </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="btn-bar">
    <div class="btn-left"><a href="javascript:void(0)" onclick="return backStatement();" class="btn btn-cancel-foot">{$smarty.const.IMAGE_CANCEL}</a></div>
    <div class="btn-right"><button class="btn btn-confirm">{$smarty.const.IMAGE_SAVE}</button></div>
</div>
{Html::input('hidden', 'admin_id', $admin_id)}
{Html::input('hidden', 'action', 'permissions')}
</form>
<script>
function saveItem() {
    $.post("{$app->urlManager->createUrl('adminmembers/adminsubmit')}", $('#save_item_form').serialize(), function (data, status) {
        if (status == "success") {
            window.location.replace("{Yii::$app->urlManager->createUrl(['adminmembers', 'admin_id' => $admin_id])}");
            //window.location.replace("{Yii::$app->urlManager->createUrl(['adminmembers/adminedit', 'admin_id' => $admin_id])}");
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
$(document).ready(function(){
  $('#p_all').on('click', function() {
    $('input.platforms-check').prop('checked', this.checked);
    $('.platforms-check').uniform();
  })
});

</script>

</div>
<!-- /Page Content -->
