{use class="Yii"}
<form action="{$app->request->baseUrl}/design/box-save" method="post" id="box-save">
  <input type="hidden" name="id" value="{$id}"/>
  <div class="popup-heading">
    {$smarty.const.TEXT_EDIT_LOGO}
  </div>
  <div class="popup-content box-img">




    <div class="tabbable tabbable-custom">
      <ul class="nav nav-tabs">

        <li class="active" data-bs-toggle="tab" data-bs-target="#type"><a>{$smarty.const.TEXT_IMAGE_}</a></li>
        <li data-bs-toggle="tab" data-bs-target="#style"><a>{$smarty.const.HEADING_STYLE}</a></li>
        <li data-bs-toggle="tab" data-bs-target="#align"><a>{$smarty.const.HEADING_WIDGET_ALIGN}</a></li>
        <li data-bs-toggle="tab" data-bs-target="#visibility"><a>{$smarty.const.TEXT_VISIBILITY_ON_PAGES}</a></li>

      </ul>
      <div class="tab-content">
        <div class="tab-pane active menu-list" id="type">

          <div class="setting-row">
            <label for="">{$smarty.const.TEXT_USE_LOGO_FROM}:</label>
            <select name="setting[0][logo_from]" id="" class="form-control choose-logo-from">
              <option value=""{if $settings[0].logo_from == ''} selected{/if}>{$smarty.const.TEXT_WIDGET_SETTINGS}</option>
              <option value="theme"{if $settings[0].logo_from == 'theme'} selected{/if}>{$smarty.const.THEME_SETTINGS}</option>
              <option value="platform"{if $settings[0].logo_from == 'platform'} selected{/if}>{$smarty.const.BOX_HEADING_FRONENDS}</option>
            </select>
          </div>

          <div class="tabbable tabbable-custom logo-image">
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

                </div>
              {/foreach}
              <script type="text/javascript">
                $('.upload').uploads();
              </script>
            </div>
          </div>

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
<script type="text/javascript">

  $('.choose-logo-from').on('change', chooseLogoFrom)
  chooseLogoFrom();

  function chooseLogoFrom () {
      if (!$('.choose-logo-from').val()) {
          $('.logo-image').show();
      } else {
          $('.logo-image').hide();
      }
  }

</script>