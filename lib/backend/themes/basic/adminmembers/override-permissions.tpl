{use class="yii\helpers\Html"}

<div id="access_management_data">
<form name="save_item_form" id="save_item_form" onSubmit="return saveItem();">
    <div class="box-wrap">
        <div class="create-or-wrap after create-cus-wrap">
            <div class="widget box box-no-shadow" style="margin-bottom: 0;">
                <div class="widget-header widget-header-review">
                    <h4>{$smarty.const.TEXT_OVERRIDE_PERMISSIONS}</h4>
                </div>
                <div class="widget-content">
                    
<h4>{$smarty.const.ENTRY_ACCESS_CONTROL_LIST}</h4>
<div class="dd" id="acl_box">
{function name=renderAclTreeFull level=0}
<ol class="dd-list">
{foreach $items as $item}
<li class="dd-item" data-id="{$item.id}">
    <div class="dd-nodrag">
        <label class="checkbox"><input type="checkbox" name="persmissions[]" value="{$item.id}" onchange="recalcAcl(this);" {if $item.selected == 1} checked{/if}><span>{$item.text}</span></label>
    </div>
{if count($item.child) > 0}
{call name=renderAclTreeFull items=$item.child level=$level+1}
{/if}
</li>
{/foreach}
</ol>
{/function}
{call renderAclTreeFull items=$aclTree}
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
</form>
<script>
function recalcAcl(obj) {
    if ($(obj).is(':checked')) {
            $(obj).parents('li.dd-item').each(function() {
                $(this).children('div.dd-nodrag').children('label').children('input').prop('checked', true);
            });
    } else {
        // uncheck childrens
        $(obj).parent('label').parent('div.dd-nodrag').parent('li.dd-item').children('ol').children().each(function() {
            $(this).children('div.dd-nodrag').children('label').children('input').prop('checked', false).trigger('change');
        });
    }
    //return false;
    $.post("{$app->urlManager->createUrl('adminmembers/recalc-acl')}", $('#save_item_form').serialize(), function (data, status) {
        if (status == "success") {
            $('#acl_box').html(data);
        } else {
            alert("Request error.");
        }
    }, "html");

    return false;
}
function saveItem() {
    $.post("{$app->urlManager->createUrl('adminmembers/submit-permissions')}", $('#save_item_form').serialize(), function (data, status) {
        if (status == "success") {
            $('#access_management_data').html(data);
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
    
    //$('#acl_box').nestable();
    
});
</script>
</div>