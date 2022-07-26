{use class="Yii"}
{use class="backend\assets\DesignAsset"}
{use class="frontend\design\Info"}
{DesignAsset::register($this)|void}

<div class="page-elements">
  {include 'menu.tpl'}

  <div class="info-view-wrap"{if strpos($theme_name, '-mobile')} style="width: 500px"{/if}>
      <div class="info-view"></div>
      <div class="info-view-right-resize"></div>
  </div>

  <div class="btn-bar btn-bar-edp-page after">
    <div class="btn-left">
      <span data-href="{$link_cancel}" class="btn btn-save-boxes">{$smarty.const.IMAGE_CANCEL}</span>
    </div>
    <div class="btn-right">
      <span class="btn btn-preview">{$smarty.const.ICON_PREVIEW}</span>
      <span class="btn btn-edit" style="display: none">{$smarty.const.IMAGE_EDIT}</span>
      <span data-href="{$link_save}" class="btn btn-confirm btn-save-boxes">{$smarty.const.IMAGE_SAVE}</span>
    </div>
  </div>

</div>

<script type="text/javascript" src="{$app->request->baseUrl}/plugins/html2canvas.js"></script>
<script type="text/javascript">

  (function($){

    $(function(){
      $('.btn-save-boxes').on('click', function(){
        $.get($(this).data('href'), { theme_name: '{$theme_name}'}, function(d){
          alertMessage(d);
          setTimeout(function(){
            $(window).trigger('reload-frame');

            $('body').append('<iframe src="{$app->request->baseUrl}/../?theme_name={$theme_name}" width="100%" height="0" frameborder="no" id="home-page"></iframe>');

            var home_page = $('#home-page');
            home_page.on('load', function(){
                html2canvas(home_page.contents().find('body').get(0), {
                  background: '#ffffff',
                  onrendered: function(canvas) {
                    $.post('upload/screenshot', { theme_name: '{$theme_name}', image: canvas.toDataURL('image/png')});
                    home_page.remove()
                  }
                })
            });

            saveEmailScreenshots();
              var timeDilay = 0;
            $('.scrtabs-tab-container li[data-href]').each(function(){
                var url = $(this).data('href');
                setTimeout(function(){
                    $.get(url)
                }, timeDilay)
                timeDilay += 100;
            })

          }, 500)
        })
      });

      var url = '';

      var cooked_url = localStorage.getItem('page-url') || '';

      var cookie_url_match = cooked_url.match(/theme_name=([a-z0-9\-_]+)/);
      if (
          !cookie_url_match || (cookie_url_match && cookie_url_match[1] != '{$theme_name}')
      ){

          if (entryData && entryData.groups.backendOrder && entryData.pages){

              for(page in entryData.pages) {
                  if (entryData.pages[page].group === 'backendOrder') {
                      url = entryData.mainUrl + '/../orders?theme_name=' + entryData.theme_name + '&page_name=' + entryData.pages[page].name;
                      break
                  }
              }

          } else if (entryData && entryData.pages && entryData.pages.home) {
              url = entryData.mainUrl + '/../' + entryData.pages.home.action + '?theme_name=' + entryData.theme_name
          }

          localStorage.setItem('page-url', url);
          localStorage.setItem('page-breadcrumbs', '{$theme_name}');
      } else {
        url = localStorage.getItem('page-url');
      }
      if (!url || url.length < 10) {
          url = '../?theme_name={$theme_name}'
      }

      $('.info-view').infoView({
        page_url: url,
        theme_name: '{$theme_name}',
      });

      var redo_buttons = $('.redo-buttons');
      redo_buttons.on('click', '.btn-undo', function(){
        var event = $(this).data('event');
        $(redo_buttons).hide();
        $.get('design/undo', { 'theme_name': '{$theme_name}'}, function(){
          if (event == 'addPage' ){
            location.href = location.href
          }
          $(window).trigger('reload-frame');

        })
      });
      redo_buttons.on('click', '.btn-redo', function(){
        var event = $(this).data('event');
        $(redo_buttons).hide();
        $.get('design/redo', { 'theme_name': '{$theme_name}', 'steps_id': $(this).data('id')}, function(){
          if (event == 'addPage'){
            location.href = location.href
          }
          $(window).trigger('reload-frame');
        })
      });
      $.get('design/redo-buttons', { 'theme_name': '{$theme_name}'}, function(data){
        redo_buttons.html(data)
      });
      $(window).on('reload-frame', function(){
        $.get('design/redo-buttons', { 'theme_name': '{$theme_name}'}, function(data){
          redo_buttons.html(data);
          $(redo_buttons).show();
        })
      })

    })
  })(jQuery);



    function saveEmailScreenshots(){
        $('.page-link-email').each(function(){

            var email = $(this);
            var page_name = email.data('href');
            var email_page = $('<iframe src="' + page_name + '" width="850" height="0" frameborder="no"></iframe>');
            $('body').append(email_page);

            email_page.on('load', function(){
                html2canvas(email_page.contents().find('body').get(0), {
                    background: '#ffffff',
                    onrendered: function(canvas) {
                        $.post('upload/screenshot', {
                            theme_name: '{$theme_name}',
                            image: canvas.toDataURL('image/png'),
                            file_name: 'img/emails/' + email.data('ref')
                        });
                        email_page.remove()
                    }
                })
            });
        })
    };

</script>



<link href="{$app->view->theme->baseUrl}/css/design.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="{$app->view->theme->baseUrl}/js/design.js"></script>
<script>
    design.init()
</script>