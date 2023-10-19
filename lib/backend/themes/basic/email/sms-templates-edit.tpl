{use class="yii\helpers\Html"}
<!--=== Page Header ===-->
<div class="page-header">
    <div class="page-title">
        <h3>{$app->controller->view->headingTitle}</h3>
    </div>
</div>
<!-- /Page Header -->
<div id="sms_management_edit">
    <form id="save_sms_form" name="new_email" onSubmit="return saveSms();">
        <div class="">
            <table cellspacing="0" cellpadding="0" width="100%">
                <tr>
                    <td class="label_name">{$smarty.const.TABLE_TEXT_NAME}</td>
                    <td class="label_value"><h4>{$sms_templates_key}</h4></td>
                </tr>
                <tr>
                    <td class="label_name">{$smarty.const.TEMPLATE_TYPE}</td>
                    <td class="label_value">{Html::dropDownList('sms_templates_type_id', $sms_templates_type_id, $types,['class'=>'form-control', 'style' => 'width: 300px'])}</td>
                </tr>
            </table>
            {if $isMultiPlatforms}
                <div class="tabbable tabbable-custom">
                    <ul class="nav nav-tabs">
                        {foreach $platforms as $platform}
                            <li class="{if $platform['id']==$default_platform_id}active {/if}" data-bs-toggle="tab" data-bs-target="#platform_{$platform['id']}">
                                <a>{$platform['text']}</a>
                            </li>
                        {/foreach}
                    </ul>
                    <div class="tab-content">
                    {/if}
                    {foreach $platforms as $platform}
                        <div class="tab-pane{if $platform['id']==$default_platform_id} active {/if} topTabPane tabbable-custom" id="platform_{$platform['id']}">
                            <div class="tabbable tabbable-custom">
                                <ul class="nav nav-tabs under_tabs_ul">
                                    {foreach $languages as $lKey => $lItem}
                                        <li{if $lKey == 0} class="active"{/if} data-bs-toggle="tab" data-bs-target="#tab_{$platform['id']}_text_{$lItem['code']}"><a>{$lItem['logo']}<span>{$lItem['name']}</span></a></li>
                                    {/foreach}
                                </ul>
                                <div class="tab-content">
                                    {foreach $cDescriptionText[$platform['id']] as $mKey => $mItem}
                                        <div class="tab-pane{if $mKey == 0} active{/if}" id="tab_{$platform['id']}_text_{$mItem['code']}">
                                            <table cellspacing="0" cellpadding="0" width="100%">
                                                <tr>
                                                    <td class="label_name">{$smarty.const.TEXT_TEMPLATES_KEYS}</td>
                                                    <td class="label_value"><a href="{$app->urlManager->createUrl('email/templates-keys')}?id_ckeditor={$mItem['c_link']}" class="btn popupLinks">{$smarty.const.TEXT_TEMPLATES_KEYS_BUTTON}</a></td>
                                                </tr>
                                                <tr>
                                                    <td valign="top" class="label_name">{$smarty.const.TEXT_SMS_TEMPLATE_BODY}</td>
                                                    <td class="label_value">{$mItem['sms_templates_body']}</td>
                                                </tr>
                                            </table>
                                        </div>
                                    {/foreach}
                                </div>
                            </div>
                        </div>
                    {/foreach}
                    {if $isMultiPlatforms}
                    </div>
                </div>
            {/if}
            <div class="btn-bar edit-btn-bar">
                <div class="btn-left"><a href="javascript:void(0)" class="btn btn-cancel-foot" onclick="return backStatement()">{$smarty.const.IMAGE_BACK}</a></div>
                <div class="btn-right"><button class="btn btn-primary">{$smarty.const.IMAGE_SAVE}</button></div>
            </div>
        </div>
        {tep_draw_hidden_field( 'sms_templates_id', $sms_templates_id )}
    </form>
</div>

<script type="text/javascript">
    function insertAtCaret(areaId, text) {
        var txtarea = document.getElementById(areaId);
        var scrollPos = txtarea.scrollTop;
        var caretPos = txtarea.selectionStart;
        var front = (txtarea.value).substring(0, caretPos);
        var back = (txtarea.value).substring(txtarea.selectionEnd, txtarea.value.length);
        txtarea.value = front + text + back;
        caretPos = caretPos + text.length;
        txtarea.selectionStart = caretPos;
        txtarea.selectionEnd = caretPos;
        txtarea.focus();
        txtarea.scrollTop = scrollPos;
    }

    function saveSms() {
        $.post("{$app->urlManager->createUrl('email/sms-templates-save')}", $('#save_sms_form').serialize(), function (data, status) {
            if (status == "success") {
                $('#sms_management_edit').html(data);
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

    $(window).load(function () {
        $('.popupLinks').popUp({
            box: "<div class='popup-box-wrap'><div class='around-pop-up'></div><div class='popup-box'><div class='popup-heading cat-head'>{$smarty.const.TEXT_TEMPLATES_KEYS}</div><div class='pop-up-close'></div><div class='pop-up-content'><div class='preloader'></div></div></div></div>"
        });
    });
</script>