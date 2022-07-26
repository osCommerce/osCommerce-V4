
<div class="tabbable tabbable-custom">
  <style>.platform-tabs-switcher,  .platform-lang-tabs-switcher { margin: 0 0 10px 0; }</style>

  {assign var=platform_languages value = $app->controller->view->platform_languages}
  {if count($app->controller->view->platforms) > 1}
            <div class="platform-tabs-switcher"><label>{strtoupper($smarty.const.TEXT_ALL_PLATFORMS)}&nbsp;</label><input type="checkbox" class="p-tabs-switcher" {if $app->controller->view->sph}checked{/if}></div>
            <ul class="nav nav-tabs platform-tabs platform-tabs-names">
                {foreach $app->controller->view->platforms as $platform}
                    {if ($app->controller->view->sph && !$platform->status) || $platform->status}
                    <li{if $platform->platform_id == $app->controller->view->def_platform_id} class="active"{/if}><a href="#tab_1_4_{$platform->platform_id}" class="flag-span" data-status="{$platform->status}" data-toggle="tab" data-id="{$platform->platform_id}"><span>{$platform->platform_name}</span>
                    <i class="platform-name-actions icon-copy"></i>
                    </a></li>
                    {/if}
                {/foreach}
            </ul>

            <div class="tab-content platform-name-contents">
            {foreach $app->controller->view->platforms as $platform}
                <div id="tab_1_4_{$platform->platform_id}" class="tab-pane {if $platform->platform_id == $app->controller->view->def_platform_id}active{/if}" data-platform-id="{$platform->platform_id}">
                    <div class="tabbable tabbable-custom">
                    {call tabDesc platform_id = $platform->platform_id is_marketplace = $platform->is_marketplace}
                    </div>
                </div>
            {/foreach}
            </div>
  {else} 
    {call tabDesc platform_id = $app->controller->view->def_platform_id}
  {/if}  
</div>
<script>
    $('.platform-tabs').scrollingTabs();
    
    startAllCKE = function(){
        if ($('.ck-editor').size() > 30){
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
    
    copyData = function($frm, $to, $fLangauge, $tLanguage){
        let boxFrom = $('.platform-name-contents div[data-platform-id='+$frm+']');
        let boxTo = $('.platform-name-contents div[data-platform-id='+$to+']');
        if (boxTo && boxFrom){
            let itemsFrom, itemsTo;
            if ($fLangauge){
                itemsFrom = $('input[name*="pDescription['+$frm+']['+$fLangauge+']"], textarea[name*="pDescription['+$frm+']['+$fLangauge+']"]', boxFrom);
                if ($tLanguage){
                    itemsTo = $('input[name*="pDescription['+$to+']['+$tLanguage+']"], textarea[name*="pDescription['+$to+']['+$tLanguage+']"]', boxTo);
                } else {
                    itemsTo = $('input[name*="pDescription['+$to+']['+$fLangauge+']"], textarea[name*="pDescription['+$to+']['+$fLangauge+']"]', boxTo);
                }
                
            } else {
                itemsFrom = $('input[name*="pDescription['+$frm+']"], textarea[name*="pDescription['+$frm+']"]', boxFrom);
                itemsTo = $('input[name*="pDescription['+$to+']"], textarea[name*="pDescription['+$to+']"]', boxTo);
            }
            let _id, _nid;
            $.each(itemsFrom, function(i, e){
                let toItemName = e.name;
                if ($fLangauge && !$tLanguage){
                    toItemName = toItemName.replace("pDescription["+$frm+"]["+$fLangauge+"]", "pDescription["+$to+"]["+$fLangauge+"]");
                } else if ($fLangauge && $tLanguage) {
                    toItemName = toItemName.replace("pDescription["+$frm+"]["+$fLangauge+"]", "pDescription["+$to+"]["+$tLanguage+"]");
                } else {
                    toItemName = toItemName.replace("pDescription["+$frm+"]", "pDescription["+$to+"]");
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
            title: "{$smarty.const.TEXT_PLATFORM_MESSAGE} " + $('.platform-tabs-names a[data-id='+except+'] span').text(),
            message: "<div><label>{$smarty.const.PULL_DOWN_DEFAULT}</label><br><select class='form-control action-choice'>"+getOptions(except).join("")+"</select>",
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
    
    $(document).ready(function(){
        startAllCKE();
    })
        
    let a = location.href.split('?');
    if (a[1]){
        let b = a[1].split('&'), use = false;
        if (b.length){
            
            b.forEach(function(e, i){ if (e.indexOf('shp=') !== -1 || e.indexOf('shpl[') !== -1) { b.splice(i,1); use = true;}  } )
        }
        if (use){
            a[1] = b.join('&');
            history.replaceState(null, null, a.join('?')+location.hash);
        }
    }
    
    $('.pl-tabs-switcher').bootstrapSwitch(
        {
            onSwitchChange: function (element, arguments) {
                window.location.replace(window.location.protocol+'//'+window.location.host+window.location.pathname+(window.location.search.length?window.location.search+'&':'')+'shpl['+$(element.target).data('id')+']='+(arguments?'1':'0')+window.location.hash);
                return true;  
            },
			onText: "{$smarty.const.SW_ON}",
            offText: "{$smarty.const.SW_OFF}",
            handleWidth: '20px',
            labelWidth: '24px',
        }
    );
    
    $('.p-tabs-switcher').bootstrapSwitch(
        {
            onSwitchChange: function (element, arguments) {
                window.location.replace(window.location.protocol+'//'+window.location.host+window.location.pathname+(window.location.search.length?window.location.search+'&':'')+'shp='+(arguments?'1':'0')+window.location.hash);
                return true;  
            },
			onText: "{$smarty.const.SW_ON}",
            offText: "{$smarty.const.SW_OFF}",
            handleWidth: '20px',
            labelWidth: '24px',
        }
    );
    
</script>

{function tabDesc}
    {if !(isset($is_marketplace) && $is_marketplace)}
    <div class="platform-lang-tabs-switcher"><label>{strtoupper($smarty.const.TEXT_ALL_LANGUAGES)}&nbsp;</label><input type="checkbox" data-id="{$platform_id}" class="pl-tabs-switcher" {if isset($app->controller->view->sphl[$platform_id]) && $app->controller->view->sphl[$platform_id]}checked{/if}></div>
    {/if}
    {if is_array($platform_languages[$platform_id]) && count($platform_languages[$platform_id]) > 1}
    <ul class="nav nav-tabs pl-nav-tabs-{$platform_id}">
      {foreach $platform_languages[$platform_id] as $lKey => $lItem}
        <li{if $lKey == 0} class="active"{/if}><a href="#tab_1_4_{$platform_id}_{$lItem['id']}" class="flag-span" data-toggle="tab">{$lItem['image']}<span>{$lItem['name']}</span>
        <i class="platform-language-name-actions icon-copy" data-platform-id="{$platform_id}" data-language-id="{$lItem['id']}"></i>
        </a></li>
      {/foreach}
    </ul>
  {/if}
  <div class="tab-content {if count($languages) < 2}tab-content-no-lang{/if}">
    {foreach $platform_languages[$platform_id] as $lKey => $lItem}
      <div class="tab-pane{if $lKey == 0} active{/if}" id="tab_1_4_{$platform_id}_{$lItem['id']}" data-language-id="{$lKey}">
        <div class="edp-line">
          <label>{$smarty.const.TEXT_PRODUCTS_NAME}</label>
          <input type="text" name="pDescription[{$platform_id}][{$lItem['id']}][products_name]" value="{if isset($pDescription[$platform_id][$lKey]->products_name)}{$pDescription[$platform_id][$lKey]->products_name|escape}{/if}" class="form-control form-control-small" />
        </div>
        <div class="edp-line">
          <label>{$smarty.const.TEXT_PRODUCTS_INTERNAL_NAME}</label>
          <input type="text" name="pDescription[{$platform_id}][{$lItem['id']}][products_internal_name]" value="{if isset($pDescription[$platform_id][$lKey]->products_internal_name)}{$pDescription[$platform_id][$lKey]->products_internal_name|escape}{/if}" class="form-control form-control-small" />
        </div>
        <div class="edp-line">
           <label>{$smarty.const.TEXT_DESCRIPTION_LINKS}</label>
           {\backend\design\LocalLinksButtons::widget(['editor' => 'txt_products_description_'|cat:$platform_id|cat:'_'|cat:$lItem['id'], 'platform_id' => 0, 'languages_id' => $lItem['id']])}
           <div class="info_desc_links">
              {$smarty.const.TEXT_INFO_DESC_LINKS}
           </div>
        </div>
        <div class="edp-line">
          <label>{$smarty.const.TEXT_PRODUCTS_DESCRIPTION}</label>
          <textarea name="pDescription[{$platform_id}][{$lItem['id']}][products_description]" class="form-control ck-editor text-dox-02" id="txt_products_description_{$platform_id}_{$lItem['id']}" wrap="soft" cols="70" rows="15">{if isset($pDescription[$platform_id][$lKey]->products_description)}{$pDescription[$platform_id][$lKey]->products_description}{/if}</textarea>
        </div>
        <div class="edp-line">
          <label>{$smarty.const.TEXT_DESCRIPTION_LINKS}</label>
          {\backend\design\LocalLinksButtons::widget(['editor' => 'txt_products_description_short_'|cat:$platform_id|cat:'_'|cat:$lItem['id'], 'platform_id' => 0, 'languages_id' => $lItem['id']])}
          <div class="info_desc_links">
              {$smarty.const.TEXT_INFO_DESC_LINKS}
          </div>
        </div>
        <div class="edp-line short-desc">
          <label>{$smarty.const.TEXT_PRODUCTS_DESCRIPTION_SHORT}</label>
          <textarea name="pDescription[{$platform_id}][{$lItem['id']}][products_description_short]" class="form-control ck-editor text-dox-01" id="txt_products_description_short_{$platform_id}_{$lItem['id']}" wrap="soft" cols="70" rows="15">{if isset($pDescription[$platform_id][$lKey]['products_description_short'])}{$pDescription[$platform_id][$lKey]['products_description_short']}{/if}</textarea>
        </div>
        <div class="edp-line">
          <label>{$smarty.const.TEXT_KEYWORDS}</label>
          <textarea name="pDescription[{$platform_id}][{$lItem['id']}][products_head_keywords_tag]" class="form-control form-control-small" wrap="soft" cols="70" rows="5">{if isset($pDescription[$platform_id][$lKey]->products_head_keywords_tag)}{$pDescription[$platform_id][$lKey]->products_head_keywords_tag}{/if}</textarea>
        </div>
      </div>
    {/foreach}
  </div>
{/function}
