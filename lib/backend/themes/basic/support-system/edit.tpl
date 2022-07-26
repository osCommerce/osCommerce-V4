{use class="\yii\helpers\Html"}
<!--=== Page Header ===-->
<div class="page-header">
    <div class="page-title">
        <h3>{$app->controller->view->headingTitle}1</h3>
    </div>
</div>
<style>
.box-or-prod-wrap { padding: 0 20px 20px 30px; }
</style>
<!-- /Page Header -->
<form action="{Yii::$app->urlManager->createUrl('support-system/submit')}" method="post" enctype="multipart/form-data" name="product_edit" id="save_product_form" onSubmit="return saveProduct();">
<div class="row">
    <div class="col-md-5 box-or-prod-wrap">
        <label class="products_name">{if $topic->product}{$topic->product->description->products_name}{/if}</label>
        {Html::hiddenInput('products_id', $topic->product->products_id)}
        {if !$topic->product->products_id}
            <div><label>{$smarty.const.TEXT_SELECT_PRODUCT_PROMPT}</label></div>
        {/if}
        <a href="{Yii::$app->urlManager->createUrl(['support-system/edit-catalog', 'products_id' => $topic->product->products_id])}" class="js-open-tree-popup btn">
        {if $topic->product->products_id}
            {$smarty.const.TEXT_CHANGE_PRODUCT}
        {else}
            {$smarty.const.TEXT_SELECT_PRODUCT}
        {/if}
        </a>
    </div>
    {Html::hiddenInput('topic_id', $topic->topic_id)}
</div>
<div class="row">
    <div class="col-md-12">
        <div class="tabbable tabbable-custom tabbable-ep">
            {if count($app->controller->view->platforms) > 1}
                <ul class="nav nav-tabs platform-tabs platform-tabs-names">
                    {foreach $app->controller->view->platforms as $platform}
                        <li{if $platform->platform_id == $app->controller->view->def_platform_id} class="active"{/if}><a href="#tab_{$platform->platform_id}" class="flag-span" data-toggle="tab" data-id="{$platform->platform_id}"><span>{$platform->platform_name}</span>
                        <i class="platform-name-actions icon-copy"></i>
                        </a></li>
                    {/foreach}
                </ul>
                <div class="tab-content platform-name-contents">
                {foreach $app->controller->view->platforms as $platform}
                    <div id="tab_{$platform->platform_id}" class="tab-pane {if $platform->platform_id == $app->controller->view->def_platform_id}active{/if}" data-platform-id="{$platform->platform_id}" data-owner-id="{$platform->platform_id}">
                        <div class="tabbable tabbable-custom">
                        {call tabVirt platform_id = $platform->platform_id}
                        </div>
                    </div>
                {/foreach}
                </div>
            {else}
                {call tabVirt platform_id = $app->controller->view->def_platform_id}
            {/if}            
        </div>
    </div>
</div>
{function tabVirt}
    {if count($languages) > 1}
        <ul class="nav nav-tabs">
            {foreach $languages as $lKey => $lItem}
                <li{if $lKey == 0} class="active"{/if}><a href="#tab_{$platform_id}_{$lItem['id']}" class="flag-span" data-toggle="tab">{$lItem['image']}<span>{$lItem['name']}</span>
                <i class="platform-language-name-actions icon-copy" data-platform-id="{$platform_id}" data-language-id="{$lItem['id']}"></i>
                </a></li>
                        {/foreach}
        </ul>
        {/if}
        <div class="tab-content tab-content-virtual {if count($languages) < 2}tab-content-no-lang{/if}">
            {foreach $languages  as $lKey => $lItem}
                <div class="tab-pane{if $lKey == 0} active{/if}" id="tab_{$platform_id}_{$lItem['id']}" style="padding-bottom: 10px;">
                    <div class="edp-line">
                      <label>{$smarty.const.TEXT_INFO_SEO_PAGE_NAME}</label>
                      {Html::textInput('topic['|cat:$platform_id|cat:']['|cat:$lItem['id']|cat:'][info_seo_name]', $topic->comDesc[$platform_id|cat:'_'|cat:$lItem['id']]->info_seo_name, ['class' => "form-control form-control-small"])}                      
                    </div>
                    <div class="edp-line">
                      <label>Title</label>
                      {Html::textInput('topic['|cat:$platform_id|cat:']['|cat:$lItem['id']|cat:'][info_title]', $topic->comDesc[$platform_id|cat:'_'|cat:$lItem['id']]->info_title, ['class' => "form-control form-control-small"])}                      
                    </div>
                    <div class="edp-line">
                      <label>Text</label>
                      {Html::textarea('topic['|cat:$platform_id|cat:']['|cat:$lItem['id']|cat:'][info_text]', $topic->comDesc[$platform_id|cat:'_'|cat:$lItem['id']]->info_text, ['id'=> 'info_text_'|cat:$platform_id|cat:$lItem['id'], 'class' => "form-control ck-editor"])}
                    </div>
                    <div class="edp-line">
                      <label>Video</label>
                      {Html::textInput('topic['|cat:$platform_id|cat:']['|cat:$lItem['id']|cat:'][info_video]', $topic->comDesc[$platform_id|cat:'_'|cat:$lItem['id']]->info_video, ['class' => "form-control"])}
                    </div>
                    <div class="edp-line">
                      <label>Meta Page title</label>
                      {Html::textInput('topic['|cat:$platform_id|cat:']['|cat:$lItem['id']|cat:'][info_meta_title]', $topic->comDesc[$platform_id|cat:'_'|cat:$lItem['id']]->info_meta_title, ['class' => "form-control"])}
                    </div>
                    <div class="edp-line">
                      <label>Meta Page title</label>
                      {Html::textArea('topic['|cat:$platform_id|cat:']['|cat:$lItem['id']|cat:'][info_meta_description]', $topic->comDesc[$platform_id|cat:'_'|cat:$lItem['id']]->info_meta_description, ['class' => "form-control"])}
                    </div>
                </div>
            {/foreach}
        </div>
{/function}

<div class="btn-bar btn-bar-edp-page after" style="padding: 0;">
    <div class="btn-left">
        <a href="javascript:void(0)" onclick="return backStatement()" class="btn btn-cancel-foot">{$smarty.const.IMAGE_BACK}</a>
    </div>
    <div class="btn-right">
        <button class="btn btn-confirm">{$smarty.const.IMAGE_SAVE}</button>
    </div>
</div>
</form>
<script>
function backStatement() {
    window.history.back();
    return false;
}
function add_row_price(obj_id, new_obj_html) {
  var div = document.createElement('div');
  div.innerHTML = new_obj_html;
  document.getElementById(obj_id).appendChild(div);
}
function delete_row_price($obj) {
  $obj.parentNode.parentNode.removeChild($obj.parentNode);
}
function saveProduct() {
    if ($('input[name=products_id]').val() > 0){
        setTimeout(function(){
            $.post("{Yii::$app->urlManager->createUrl('support-system/submit')}", $('#save_product_form').serialize(), function(data, status){
                if (status == "success") {
                    if (data.hasOwnProperty('message')){
                        alertMessage("<br><center>"+data.message+"</center><br>");
                        if (data.hasOwnProperty('reload') || data.hasOwnProperty('redirect')){
                            setTimeout(function(){ 
                                if (data.reload){
                                    window.location.reload();
                                } else if (data.redirect){
                                    window.location.href = data.redirect;
                                }                                
                            }, 200);
                        }
                    }            
                } else {
                    alert("Request error.");
                }
            },"json");
        }, 100);
        
    } else {
        alertMessage('<br><center>{$smarty.const.TEXT_SELECT_PRODUCT_PROMPT|escape:javascript}</centr><br><br>');
    }
    
    return false;
}

$(document).ready(function(){
    $('.js-open-tree-popup').popUp(); 
    
    startAllCKE = function(){
        if ($('.ck-editor').size() > 130){
            $.each($('.ck-editor'), function(i, e){
                CKEDITOR.inline($(e).attr('id'));
            })
        } else {
            $.each($('.ck-editor'), function(i, e){
                CKEDITOR.replace($(e).attr('id'));
            })        
        }
    }
    
    getOptions = function(except){
        let options = ['<option value="0">{$smarty.const.IMAGE_COPY_TO|escape:javascript} All</option>'];
        $('.platform-tabs-names li').each(function (i, e){
            if ($("a", e).data('id') != except){
                options.push('<option value="'+ $("a", e).data('id') +'">{$smarty.const.IMAGE_COPY_TO|escape:javascript} '+ $("span", e).html() +'</option>');
            }
        })
        return options;
    }
    
    startAllCKE();
    
    copyData = function($frm, $to, $fLangauge, $tLanguage){
        let boxFrom = $('.platform-name-contents div[data-platform-id='+$frm+']');
        let boxTo = $('.platform-name-contents div[data-platform-id='+$to+']');
        if (boxTo && boxFrom){
            let itemsFrom, itemsTo;
            if ($fLangauge){
                itemsFrom = $('input[name*="topic['+$frm+']['+$fLangauge+']"], textarea[name*="topic['+$frm+']['+$fLangauge+']"]', boxFrom);
                if ($tLanguage){
                    itemsTo = $('input[name*="topic['+$to+']['+$tLanguage+']"], textarea[name*="topic['+$to+']['+$tLanguage+']"]', boxTo);
                } else {
                    itemsTo = $('input[name*="topic['+$to+']['+$fLangauge+']"], textarea[name*="topic['+$to+']['+$fLangauge+']"]', boxTo);
                }
                
            } else {
                itemsFrom = $('input[name*="topic['+$frm+']"], textarea[name*="topic['+$frm+']"]', boxFrom);
                itemsTo = $('input[name*="topic['+$to+']"], textarea[name*="topic['+$to+']"]', boxTo);
            }
            let _id, _nid;
            $.each(itemsFrom, function(i, e){
                let toItemName = e.name;
                if ($fLangauge && !$tLanguage){
                    toItemName = toItemName.replace("topic["+$frm+"]["+$fLangauge+"]", "topic["+$to+"]["+$fLangauge+"]");
                } else if ($fLangauge && $tLanguage) {
                    toItemName = toItemName.replace("topic["+$frm+"]["+$fLangauge+"]", "topic["+$to+"]["+$tLanguage+"]");
                } else {
                    toItemName = toItemName.replace("topic["+$frm+"]", "topic["+$to+"]");
                }                
                if ($('[name="'+toItemName+'"]').is('input')){
                    $('[name="'+toItemName+'"]').val(e.value);
                } else {
                    $('[name="'+toItemName+'"]').text(e.value);
                    _id = $('[name="'+toItemName+'"]').attr('id');
                    if (_id && $('#'+_id).hasClass('ck-editor')){
                        _nid = $(e).attr('id');
                        CKEDITOR.instances[_id].setData(CKEDITOR.instances[_nid].getData());
                    }
                }
            })
        }
    }
        
    $('.platform-name-actions').click(function(e){
        let except = $(this).closest('li').find('a').data('id');
        let action = 0;
        
        bootbox.dialog({
            title: "{$smarty.const.TEXT_PLATFORM_MESSAGE|escape:javascript} " + $('.platform-tabs-names a[data-id='+except+'] span').text(),
            message: "<div><label>{$smarty.const.PULL_DOWN_DEFAULT|escape:javascript}</label><br><select class='form-control action-choice'>"+getOptions(except).join("")+"</select>",
            buttons: {
                cancel: {
                    label: "Cancel",
                    className: 'btn btn-cancel',
                    callback: function(){
                        
                    }
                },
                ok: {
                    label: "Confirm",
                    className: 'btn btn-confirm',
                    callback: function(event){
                        if (!action) { //copy to all
                            $.each($('.platform-name-contents').children(), function(i, e){
                                let to = $(e).data('platform-id');
                                if (to != except){
                                    copyData(except, to);
                                }
                            });
                        } else {
                            //copy this to some
                            copyData(except, action);
                        }
                    }
                }
            },
            callback: function (result) {
                
            }
        });        
        
        $('body').on('change', '.action-choice', function(){
            action = $(this).val();
        })
    })
    
    var allLanguages = [];
    {foreach $languages as $lang} allLanguages.push({ 'id':{$lang['id']}, 'name':"{$lang['name']}" }); {/foreach}
    getLanguages= function(except){
        let options = [];
        allLanguages.forEach(function(e){ 
            if(e.id != except){
                options.push('<input type="checkbox" value="'+ e.id +'" class="selected-pl">&nbsp; '+ e.name);
            }
        });        
        return options.join("<br>");
    }
    
    $('.platform-language-name-actions').click(function(e){
        let except = $(this).data('platform-id');
        let currentL = $(this).data('language-id');;
        let laction = 0;
        bootbox.dialog({
            title: "{$smarty.const.TEXT_PLATFORM_LANGUAGE_MESSAGE|escape:javascript} " + $('.platform-tabs-names a[data-id='+except+'] span').text() + " " + $(this).prev().text(),
            message: "<div><label>{$smarty.const.PULL_DOWN_DEFAULT|escape:javascript}</label><br><select class='form-control action-lang-choice'>"+getOptions(except).join("")+"</select><br>+{$smarty.const.IMAGE_COPY_TO|escape:javascript}<br>"+getLanguages(currentL),
            buttons: {
                cancel: {
                    label: "Cancel",
                    className: 'btn btn-cancel',
                    callback: function(){
                        
                    }
                },
                ok: {
                    label: "Confirm",
                    className: 'btn btn-confirm',
                    callback: function(event){
                        let also = [];
                        $.each($('.selected-pl:checked'), function(i, e){ also.push(e.value);  })
                        
                        if (!laction) { //copy to all
                            $.each($('.platform-name-contents').children(), function(i, e){
                                let to = $(e).data('platform-id');
                                if (to != except){
                                    copyData(except, to, currentL);
                                }
                            });
                            if (also.length){
                                also.forEach(function(toL){ 
                                    $.each($('.platform-name-contents').children(), function(i, e){
                                        let to = $(e).data('platform-id');
                                        if (to != except){
                                            copyData(except, to, currentL, toL);
                                        }
                                    });
                                
                                })
                            }
                        } else {
                            //copy this to some
                            copyData(except, laction, currentL);
                            if (also.length){
                                also.forEach(function(e){ copyData(except, laction, currentL, e); })
                            }
                        }
                    }
                }
            },
            callback: function (result) {
                
            }
        });  

        $('body').on('change', '.action-lang-choice', function(){
            laction = $(this).val();
        })
    })
})

</script>
<link href="{$app->request->baseUrl}/plugins/fancytree/skin-bootstrap/ui.fancytree.min.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="{$app->request->baseUrl}/plugins/fancytree/jquery.fancytree-all.min.js"></script>
