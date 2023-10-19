{use class="Yii"}
<form action="{$app->request->baseUrl}/design/box-save" method="post" id="box-save">
  <input type="hidden" name="id" value="{$id}"/>
  <div class="popup-heading">
    {$smarty.const.TEXT_VIDEO}
  </div>
  <div class="popup-content box-img">




    <div class="tabbable tabbable-custom">
      <ul class="nav nav-tabs">

        <li class="active" data-bs-toggle="tab" data-bs-target="#type"><a>{$smarty.const.TEXT_VIDEO}</a></li>
        <li data-bs-toggle="tab" data-bs-target="#style"><a>{$smarty.const.HEADING_STYLE}</a></li>
        <li data-bs-toggle="tab" data-bs-target="#align"><a>{$smarty.const.HEADING_WIDGET_ALIGN}</a></li>
        <li data-bs-toggle="tab" data-bs-target="#visibility"><a>{$smarty.const.TEXT_VISIBILITY_ON_PAGES}</a></li>

      </ul>
      <div class="tab-content">
        <div class="tab-pane active menu-list" id="type">


          <div class="tabbable tabbable-custom">
            <div class="nav nav-tabs">

              {foreach $languages as $language}
                <div{if $language.id == $languages_id} class="active"{/if} data-bs-toggle="tab" data-bs-target="#{$item.id}_{$language.id}"><a>{$language.video} {$language.name}</a></div>
              {/foreach}

            </div>
            <div class="tab-content">
              {foreach $languages as $language}
                <div class="tab-pane{if $language.id == $languages_id} active{/if}" id="{$item.id}_{$language.id}" data-language="{$language.id}">

                  <h4>{$smarty.const.TEXT_VIDEO}</h4>

                    {\backend\design\Image::widget([
                      'name' => 'setting['|cat:$language.id|cat:'][video]',
                      'value' => $settings[$language.id].video,
                      'upload' => 'setting['|cat:$language.id|cat:'][video_upload]',
                      'acceptedFiles' => 'video/*',
                      'type' => 'video'
                    ])}





                  <h4 style="clear: both; margin-top: 30px">{$smarty.const.TEXT_POSTER} <small>{$smarty.const.TEXT_POSTER_DESCRIBE}(an image to be shown while the video is downloading, or until the user hits the play button)</small></h4>

                  {\backend\design\Image::widget([
                    'name' => 'setting['|cat:$language.id|cat:'][poster]',
                    'value' => $settings[$language.id].poster,
                    'upload' => 'setting['|cat:$language.id|cat:'][poster_upload]',
                    'acceptedFiles' => 'image/*',
                    'type' => 'image'
                  ])}

                </div>
              {/foreach}
            </div>
          </div>

          <div class="">
            <h3>{$smarty.const.TEXT_SETTINGS}</h3>
            <div class="setting-row">
              <label for="">{$smarty.const.TEXT_WIDTH}</label>
              <input type="number" name="setting[0][width_v]" value="{$settings[0].width_v}" class="form-control" /><span class="px">px</span>
            </div>
            <div class="setting-row">
              <label for="">{$smarty.const.TEXT_HEIGHT}</label>
              <input type="number" name="setting[0][height_v]" value="{$settings[0].height_v}" class="form-control" /><span class="px">px</span>
            </div>

            <p><label><input type="checkbox" name="setting[0][autoplay]"{if $settings[0].autoplay} checked{/if}/> {$smarty.const.TEXT_AUTOPLAY}</label></p>
            <p><label><input type="checkbox" name="setting[0][controls]"{if $settings[0].controls} checked{/if}/> {$smarty.const.TEXT_CONTROLS}</label></p>
            <p><label><input type="checkbox" name="setting[0][loop]"{if $settings[0].loop} checked{/if}/> {$smarty.const.TEXT_LOOP}</label></p>
            <p><label><input type="checkbox" name="setting[0][muted]"{if $settings[0].muted} checked{/if}/> {$smarty.const.TEXT_MUTED}</label></p>
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