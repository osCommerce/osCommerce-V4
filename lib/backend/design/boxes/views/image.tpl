{use class="Yii"}
<form action="{$app->request->baseUrl}/design/box-save" method="post" id="box-save">
  <input type="hidden" name="id" value="{$id}"/>
  <div class="popup-heading">
    {$smarty.const.TEXT_IMAGE}
  </div>
  <div class="popup-content box-img">


    <div class="tabbable tabbable-custom">
      <ul class="nav nav-tabs">

        <li class="active" data-bs-toggle="tab" data-bs-target="#upload"><a>{$smarty.const.IMAGE_UPLOAD}</a></li>
        <li data-bs-toggle="tab" data-bs-target="#style"><a>{$smarty.const.HEADING_STYLE}</a></li>
        <li data-bs-toggle="tab" data-bs-target="#align"><a>{$smarty.const.HEADING_WIDGET_ALIGN}</a></li>
        <li data-bs-toggle="tab" data-bs-target="#visibility"><a>{$smarty.const.TEXT_VISIBILITY_ON_PAGES}</a></li>

      </ul>
      <div class="tab-content">


        <div class="tab-pane active" id="upload">


          <div class="tabbable tabbable-custom">
            <ul class="nav nav-tabs">

              {foreach $languages as $language}
                <li{if $language.id == $languages_id} class="active"{/if} data-bs-toggle="tab" data-bs-target="#{$item.id}_{$language.id}"><a>{$language.logo} {$language.name}</a></li>
              {/foreach}

            </ul>
            <div class="tab-content">
              {foreach $languages as $language}
                <div class="tab-pane{if $language.id == $languages_id} active{/if}" id="{$item.id}_{$language.id}" data-language="{$language.id}">

                  {\backend\design\Image::widget([
                  'name' => 'setting['|cat:$language.id|cat:'][logo]',
                  'value' => $settings[$language.id].logo,
                  'upload' => 'setting['|cat:$language.id|cat:'][logo_upload]',
                  'acceptedFiles' => 'image/*',
                  'type' => 'image'
                  ])}

                  <div class="setting-row" style="margin-top: 20px">
                    <label for="">{$smarty.const.TEXT_LINK}</label>
                    <input type="text" name="setting[{$language.id}][img_link]" value="{$settings[$language.id].img_link}" class="form-control" style="width: 243px" />
                  </div>

                  <div class="setting-row">
                    <label for="">{$smarty.const.ALT_ATTRIBUTE}</label>
                    <input type="text" name="setting[{$language.id}][alt]" value="{$settings[$language.id].alt}" class="form-control" style="width: 243px" />
                  </div>

                  <div class="setting-row">
                    <label for="">{$smarty.const.TITLE_ATTRIBUTE}</label>
                    <input type="text" name="setting[{$language.id}][title]" value="{$settings[$language.id].title}" class="form-control" style="width: 243px" />
                  </div>

                  <div class="setting-row">
                    <label for=""><input type="checkbox" name="setting[{$language.id}][target_blank]" {if $settings[$language.id].target_blank} checked {/if} /> Open link in new tab</label>

                  </div>

                  <div class="setting-row">
                    <label for=""><input type="checkbox" name="setting[{$language.id}][no_follow]" {if $settings[$language.id].no_follow} checked {/if} /> No follow</label>

                  </div>

                </div>
              {/foreach}
              <script type="text/javascript">
                $('.upload').uploads();
              </script>
            </div>
          </div>




            {include 'include/lazy_load.tpl'}


          {include 'include/ajax.tpl'}

        </div>
        <div class="tab-pane" id="style">
          {include 'include/style.tpl'}
        </div>
        <div class="tab-pane" id="align">
          {include 'include/align.tpl'}
        </div>
        <div class="tab-pane" id="visibility">
          {include 'include/visibility.tpl'}
        </div>

      </div>
    </div>



  </div>
  <div class="popup-buttons">
    <button type="submit" class="btn btn-primary btn-save">{$smarty.const.IMAGE_SAVE}</button>
    <span class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</span>
  </div>
</form>