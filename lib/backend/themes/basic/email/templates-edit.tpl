{use class="yii\helpers\Html"}
<!--=== Page Header ===-->
<div class="page-header">
  <div class="page-title">
    <h3>{$app->controller->view->headingTitle}</h3>
  </div>
</div>
<!-- /Page Header -->


<div id="email_management_edit">
  <form id="save_email_form" name="new_email" onSubmit="return saveEmail();">
    <div class="">

        <table cellspacing="0" cellpadding="0" width="100%">
            <tr>
                <td class="label_name">{$smarty.const.TABLE_TEXT_NAME}</td>
                <td class="label_value"><h4>{$email_templates_key}</h4></td>
            </tr>
            <tr>
                <td class="label_name">{$smarty.const.TEMPLATE_TYPE}</td>
                <td class="label_value">{Html::dropDownList('type_id',$type_id, $types,['class'=>'form-control', 'style' => 'width: 300px'])}</td>
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


            <table cellspacing="0" cellpadding="0" width="100%">
                <tr>
                <td class="label_name">{$smarty.const.DESIGN_TEMPLATE}</td>
                <td class="label_value">

                  <select name="design_template[{$platform['id']}]" class="form-control design_templates" style="width: 300px">
                      {foreach $designTemplates[$platform['id']]['design_templates'] as $item}

                        <option value="{$item.name}"{if $item.active} selected{/if} data-theme_name="{$item.theme_name}">{$item.title}</option>
                      {/foreach}
                  </select>

                  <div class="design-template-preview">
                    <div class="design-template-preview-holder">111</div>
                  </div>
                </td>
              </tr>
            </table>

            <div class="tabbable tabbable-custom">
              <ul class="nav nav-tabs">
                <li class="active" data-bs-toggle="tab" data-bs-target="#tab_{$platform['id']}_2"><a>{$smarty.const.TEXT_EMAIL_TEMPLATE_HTML}</a></li>
                <li data-bs-toggle="tab" data-bs-target="#tab_{$platform['id']}_3"><a>{$smarty.const.TEXT_EMAIL_TEMPLATE_TEXT}</a></li>
              </ul>
              <div class="tab-content">
                <div class="tab-pane active topTabPane tabbable-custom" id="tab_{$platform['id']}_2">
                    {if count($languages) > 1}
                  <ul class="nav nav-tabs under_tabs_ul">
                    {foreach $languages as $lKey => $lItem}
                      <li{if $lKey == 0} class="active"{/if} data-bs-toggle="tab" data-bs-target="#tab_{$platform['id']}_html_{$lItem['code']}"><a>{$lItem['logo']}<span>{$lItem['name']}</span></a></li>
                    {/foreach}
                  </ul>
                  {/if}
                  <div class="tab-content {if count($languages) < 2}tab-content-no-lang{/if}">
                    {foreach $cDescriptionHtml[$platform['id']] as $mKey => $mItem}
                      <div class="tab-pane{if $mKey == 0} active{/if}" id="tab_{$platform['id']}_html_{$mItem['code']}">
                        <table cellspacing="0" cellpadding="0" width="100%">
                          <tr>
                            <td class="label_name">{$smarty.const.TEXT_EMAIL_TEMPLATES_SUBJECT}</td>
                            <td class="label_value email_templates_subject">{$mItem['email_templates_subject']}</td>
                          </tr>
                          <tr>
                            <td class="label_name">{$smarty.const.TEXT_TEMPLATES_KEYS}</td>
                            <td class="label_value"><a href="{$app->urlManager->createUrl(['email/templates-keys', 'id_ckeditor' => $mItem['c_link'], 'email_templates_key' => $email_templates_key])}" class="btn popupLinks">{$smarty.const.TEXT_TEMPLATES_KEYS_BUTTON}</a></td>
                          </tr>
                          <tr>
                            <td valign="top" class="label_name">{$smarty.const.TEXT_EMAIL_TEMPLATES_BODY}</td>
                            <td class="label_value email_templates_body">{$mItem['email_templates_body']}</td>
                          </tr>
                        </table>
                      </div>
                    {/foreach}
                  </div>

                </div>
                <div class="tab-pane topTabPane tabbable-custom" id="tab_{$platform['id']}_3">

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
                            <td class="label_name">{$smarty.const.TEXT_EMAIL_TEMPLATES_SUBJECT}</td>
                            <td class="label_value email_templates_subject">{$mItem['email_templates_subject']}</td>
                          </tr>
                          <tr>
                            <td class="label_name">{$smarty.const.TEXT_TEMPLATES_KEYS}</td>
                            <td class="label_value"><a href="{$app->urlManager->createUrl('email/templates-keys')}?id_ckeditor={$mItem['c_link']}" class="btn popupLinks">{$smarty.const.TEXT_TEMPLATES_KEYS_BUTTON}</a></td>
                          </tr>
                          <tr>
                            <td valign="top" class="label_name">{$smarty.const.TEXT_EMAIL_TEMPLATES_BODY}</td>
                            <td class="label_value email_templates_body">{$mItem['email_templates_body']}</td>
                          </tr>
                        </table>
                      </div>
                    {/foreach}
                  </div>

                </div>
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
        <div class="btn-right"><button class="btn btn-confirm">{$smarty.const.IMAGE_SAVE}</button></div>
      </div>
    </div>
    {tep_draw_hidden_field( 'email_templates_id', $email_templates_id )}
    {tep_draw_hidden_field( 'platform_id', $selected_platform_id )}
  </form>
</div>

<script type="text/javascript">
  $(function(){

      $('.design_templates')
          .each(showPreview)
          .on('change', showPreview);

      var previewDesign;

      function showPreview(){
          var val = $(this).val();
          if (!val) val = 'email';
          var previewBox = $(this).closest('.label_value').find('.design-template-preview-holder');
          var theme_name = $('option:first', this).data('theme_name');
          previewBox.html('<img src="../themes/' + theme_name + '/img/emails/' + val + '.png">');

          previewBox.attr('data-theme_name', theme_name);
          previewBox.data('theme_name', theme_name);
          previewBox.attr('data-page', val);
          previewBox.data('page', val);

      }

      var ckeditorEvents = true;

      $('.design-template-preview-holder').on('click', function(){
          console.log('click');

          var theme_name = $(this).data('theme_name');
          var page = $(this).data('page');

          let win = window.open('', "win", "width=900,height=800");

          $.get('../email-template?theme_name=' + theme_name + '&page_name=' + page, function(response){

              let html = emailHtml(response);
              win.document.body.innerHTML = '';
              win.document.write(html);

              $('body').off('changedEmail').on('changedEmail', function(){
                  let html = emailHtml(response);
                  win.document.body.innerHTML = '';
                  win.document.write(html);
              })
          });

          if (ckeditorEvents) {
              ckeditorEvents = false;
              for (var instance in CKEDITOR.instances) {
                  (function () {
                      var inst = instance;
                      CKEDITOR.instances[inst].on('change', function () {
                          console.log(inst);
                          CKEDITOR.instances[inst].updateElement();
                          $('body').trigger('changedEmail');
                      });
                  })()
              }
              $('.design_templates').on('change', function(){
                  $('.design-template-preview-holder:visible').trigger('click')
              });
              $('.nav-tabs a').on('click', function(){
                  setTimeout(function(){
                      $('.design-template-preview-holder:visible').trigger('click')
                  }, 0)
              })
          }


      });

      $('.email_templates_subject input').on('change keyup', function(){
          $('body').trigger('changedEmail')
      });
      $('.email_templates_body textarea').on('change keyup', function(){
          $('body').trigger('changedEmail')
      });



      function emailHtml(html){
          var subject = $('.email_templates_subject:visible input').val();
          html = html.replace('##EMAIL_TITLE##', subject);

          var content = $('.email_templates_body:visible textarea').val();
          html = html.replace('##EMAIL_TEXT##', content);

          return html;
      }
  });


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
  function saveEmail() {
    ckeplugin();
    $.post("{$app->urlManager->createUrl('email/templates-save')}", $('#save_email_form').serialize(), function (data, status) {
      if (status == "success") {
        $('#email_management_edit').html(data);
        CKEDITOR.replaceAll('ckeditor');
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
  $(window).on('load', function(){
    $('.popupLinks').popUp({		
        box: "<div class='popup-box-wrap'><div class='around-pop-up'></div><div class='popup-box'><div class='popup-heading cat-head'>{$smarty.const.TEXT_TEMPLATES_KEYS}</div><div class='pop-up-close'></div><div class='pop-up-content'><div class='preloader'></div></div></div></div>"		
    });
  })
</script>
