{use class="Yii"}
  <div class="popup-heading">
    {$smarty.const.TEXT_EDIT_TEXT}
  </div>
  <div class="popup-content box-img">
    <div class="tabbable tabbable-custom">
      <ul class="nav nav-tabs">
        <li class="active" data-bs-toggle="tab" data-bs-target="#text"><a>{$smarty.const.TEXT_TEXT}</a></li>
        <li data-bs-toggle="tab" data-bs-target="#icons"><a>{$smarty.const.TEXT_IMAGE_}</a></li>
      </ul>
      <div class="tab-content">
        <div class="tab-pane active menu-list" id="text">


          <div class="tabbable tabbable-custom">
            <ul class="nav nav-tabs">
              {foreach $languages as $lang}
                {foreach $currentMenu as $menu_lang}
                    {if $lang.id == $menu_lang.language_id}
                  <li{if $menu_lang.language_id == $languages_id} class="active"{/if} data-bs-toggle="tab" data-bs-target="#item_{$menu_lang.language_id}"><a>{$lang.logo} {$lang.name}</a></li>
                    {/if}
                {/foreach}
              {/foreach}
            </ul>

            <div class="tab-content">
              {foreach $currentMenu as $menu}
                <div class="tab-pane{if $menu.language_id == $languages_id} active{/if}" id="item_{$menu.language_id}" data-language="{$menu.language_id}">
					<textarea name="menu[{$menu.language_id}][{$menu.translation_key}]" style="width: 100%;">{$menu.translation_value}</textarea>
                </div>
              {/foreach}
            </div>
          </div>
        </div>
        <div class="tab-pane" id="icons">
          <div class="icons-menu">
			{$iconfont = array('glass','music','search','envelope-alt','heart','star','star-empty','user','film','th-large','th','th-list','ok','remove','zoom-in','zoom-out','power-off','signal','gear','trash','home','file-alt','time','road','download-alt','download','upload','inbox','play-circle','rotate-right','refresh','list-alt','lock','flag','headphones','volume-off','volume-down','volume-up','qrcode','barcode','tag','tags','book','bookmark','print','camera','font','bold','italic','text-height','text-width','align-left','align-center','align-right','align-justify','list','indent-left','indent-right','facetime-video','picture','pencil','map-marker','adjust','tint','edit','share','check','move','step-backward','fast-backward','backward','play','pause','stop','forward','fast-forward','step-forward','eject','chevron-left','chevron-right','plus-sign','minus-sign','remove-sign','ok-sign','question-sign','info-sign','screenshot','remove-circle','ok-circle','ban-circle','arrow-left','arrow-right','arrow-up','arrow-down','mail-forward','resize-full','resize-small','plus','minus','asterisk','exclamation-sign','gift','leaf','fire','eye-open','eye-close','warning-sign','plane','calendar','random','comment','magnet','chevron-up','chevron-down','retweet','shopping-cart','folder-close','folder-open','resize-vertical','resize-horizontal','bar-chart','twitter-sign','facebook-sign','camera-retro','key','gears','comments','thumbs-up-alt','thumbs-down-alt','star-half','heart-empty','signout','linkedin-sign','pushpin','external-link','signin','trophy','github-sign','upload-alt','lemon','phone','unchecked','bookmark-empty','phone-sign','twitter','facebook','github','unlock','credit-card','rss','hdd','bullhorn','bell','certificate','hand-right','hand-left','hand-up','hand-down','circle-arrow-left','circle-arrow-right','circle-arrow-up','circle-arrow-down','globe','wrench','tasks','filter','briefcase','fullscreen','group','link','cloud','beaker','cut','copy','paperclip','save','sign-blank','reorder','list-ul','list-ol','strikethrough','underline','table','magic','truck','pinterest','pinterest-sign','google-plus-sign','google-plus','money','caret-down','caret-up','caret-left','caret-right','columns','sort','sort-down','sort-up','envelope','linkedin','rotate-left','legal','dashboard','comment-alt','comments-alt','bolt','sitemap','umbrella','paste','lightbulb','exchange','cloud-download','cloud-upload','user-md','stethoscope','suitcase','bell-alt','coffee','food','file-text-alt','building','hospital','ambulance','medkit','fighter-jet','beer','h-sign','plus-sign-alt','double-angle-left','double-angle-right','double-angle-up','double-angle-down','angle-left','angle-right','angle-up','angle-up','angle-down','angle-down','desktop','laptop','tablet','mobile-phone','circle-blank','quote-left','quote-right','spinner','circle','mail-reply','github-alt','folder-close-alt','folder-open-alt','expand-alt','collapse-alt','smile','frown','meh','gamepad','keyboard','flag-alt','flag-checkered','terminal','code','reply-all','mail-reply-all','star-half-full','location-arrow','crop','code-fork','unlink','question','info','exclamation','superscript','subscript','eraser','puzzle-piece','microphone','microphone-off','shield','calendar-empty','fire-extinguisher','rocket','maxcdn','chevron-sign-left','chevron-sign-right','chevron-sign-up','chevron-sign-down','html5','css3','anchor','unlock-alt','bullseye','ellipsis-horizontal','ellipsis-vertical','rss-sign','play-sign','ticket','minus-sign-alt','check-minus','level-up','level-down','check-sign','edit-sign','external-link-sign','share-sign','compass','collapse','collapse-top','expand','euro','gbp','dollar','rupee','yen','renminbi','won','bitcoin','file','file-text','sort-by-alphabet','sort-by-alphabet-alt','sort-by-attributes','sort-by-attributes-alt','sort-by-order','sort-by-order-alt','thumbs-up','thumbs-down','youtube-sign','youtube','xing','xing-sign','youtube-play','dropbox','stackexchange','instagram','flickr','adn','bitbucket','bitbucket-sign','tumblr','tumblr-sign','long-arrow-down','long-arrow-up','long-arrow-left','long-arrow-right','apple','windows','android','linux','dribbble','skype','foursquare','trello','female','male','gittip','sun','moon','archive','bug','vk','weibo','renren','administrator','catalog','seo_cms','faqdesk','modules','marketing','customers','reports','administrator','catalog','faqdesk','modules','marketing','customers','reports','settings','area-chart','clock-o','calendar-o','hand-paper-o','user-plus','cubes','design_controls','seo','checked','close','info-circle','file-o','app-store')}
			  {foreach $iconfont as $iconf}
				  <label><input name="imageIcons" type="radio"{if $icons[0]['filename'] == $iconf} checked{/if} value="{$iconf}"><i class="icon-{$iconf}"></i></label>
			{/foreach}

		  </div>
        </div>
      </div>
    </div>


  </div>
  <div class="popup-buttons">
    <span class="btn btn-primary btn-save" onclick="return save();">{$smarty.const.IMAGE_SAVE}</span>
    <span class="btn btn-cancel" onclick="return backStatement();">{$smarty.const.IMAGE_CANCEL}</span>
  </div>
<script>
    function backStatement() {
        window.history.back();
        return false;
    }
    function save() {
        var post_data = {};
        $('.tab-content textarea').each(function(){
            post_data[$(this).attr('name')] = $(this).val();
        })
        post_data['icon'] = $('#icons input:checked').val();
        post_data['id'] = {$id};
        var ps = JSON.stringify(post_data);
        var _this = $(this);

        $.post("{Yii::$app->urlManager->createUrl('admin-menu/save-popup')}", { "post_data" : ps } , function(data){
           // window.location.reload();
			$('#'+{$id}+' > .item-handle > .searchable').text(data.val);
            $('.pop-up-close').trigger('click');
        },'json');
        return false;
    }
</script>