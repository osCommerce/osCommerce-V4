{use class="yii\helpers\Html"}
<style type="text/css">
    #access_management_data ol {
        list-style: none;
    }
    .dd-nodrag {
        display: flex;
        align-items: center;
        padding-left: 10px;
    }
    input[type="checkbox"] {
        margin-right: 10px;
    }
</style>
<div id="access_management_data">
<form name="save_item_form" id="save_item_form" onSubmit="return saveItem();">
    <div class="box-wrap">
        <div class="create-or-wrap after create-cus-wrap">
            <div class="widget box box-no-shadow">
                <div class="widget-header widget-header-review">
                    <h4>{$actionName}</h4>
                    <div class="toolbar no-padding">
                        <div class="btn-group">
                            <span class="btn btn-xs widget-collapse"><i class="icon-angle-down"></i></span>
                        </div>
                    </div>
                </div>
                <div class="widget-content">
                    <div class="wl-td">
                        <div class="btn-right">
                            <a href="javascript:void(0)" onclick="return checkAll();" class="btn">{$smarty.const.TEXT_CHECK_ALL}</a>&nbsp;<a href="javascript:void(0)" onclick="return uncheckAll();" class="btn">{$smarty.const.TEXT_UNCHECK_ALL}</a>
                        </div>
                        <br><br>
                    </div>
                    <div class="wl-td">
                        <label>{$smarty.const.ENTRY_ACCESS_LEVELS_NAME}<span class="fieldRequired">*</span></label>{Html::input('text', 'access_levels_name', $accessInfo->access_levels_name|default:null, ['class' => 'form-control', 'required' => true])}
                    </div>
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

            <div class="widget box box-no-shadow">
                <div class="widget-header widget-header-review">
                    <h4>Design templates</h4>
                    <div class="toolbar no-padding">
                        <div class="btn-group">
                            <span class="btn btn-xs widget-collapse"><i class="icon-angle-down"></i></span>
                        </div>
                    </div>
                </div>
                <div class="widget-content">

                    <div class="row">
                        <div class="col-md-6">
                            {foreach $templatesList as $page}
                                <div class="setting-row">
                                    <label>{$page.title}</label>
                                    {Html::dropDownList('pages['|cat:$page.name|cat:']', $page.selectedTemplate, $page.templates, ['class' => 'form-control'])}
                                </div>
                            {/foreach}
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
    {Html::input('hidden', 'item_id', $item_id)}
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
    return false;
    $.post("{$app->urlManager->createUrl('adminfiles/recalc-acl')}", $('#save_item_form').serialize(), function (data, status) {
        if (status == "success") {
            $('#acl_box').html(data);
        } else {
            alert("Request error.");
        }
    }, "html");

    return false;
}
function saveItem() {
    $.post("{$app->urlManager->createUrl('adminfiles/submit')}", $('#save_item_form').serialize(), function (data, status) {
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

function checkAll() {
    $('input').prop('checked', true);
    return false;
}

function uncheckAll() {
    $('input').prop('checked', false);
    return false;
}

$(document).ready(function(){ 
    
    $('#acl_box').nestable();
    
    $('.item-close').on('click', function(){
            if ($(this).hasClass('closed')){
              $(this).removeClass('closed');
              $(this).parent('div').parent('li').children('.dd-placeholder').show()
            } else {
              $(this).addClass('closed');
              $(this).parent('div').parent('li').children('.dd-placeholder').hide()
            }

    });
      
    $('.edit-menu').popUp({
        'box_class':'editMenu'
    });

    $('.create_item_popup').popUp();

    $('.btn-import').each(function() {
        $(this).dropzone({
          url: '{Yii::$app->urlManager->createUrl(['adminfiles/import-acl', 'item_id' => $item_id])}',
          success: function(){
            location.reload();
          }
        })
    });

});
</script>
</div>