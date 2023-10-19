{use class="Yii"}
<form action="{Yii::getAlias('@web')}/design/box-save" method="post" id="box-save">
  <input type="hidden" name="id" value="{$id}"/>
  <div class="popup-heading">
    {$smarty.const.TAB_IMAGES}
  </div>
  <div class="popup-content">




    <div class="tabbable tabbable-custom">
      <ul class="nav nav-tabs">

        <li class="active" data-bs-toggle="tab" data-bs-target="#type"><a>{$smarty.const.HEADING_TYPE}</a></li>
        <li data-bs-toggle="tab" data-bs-target="#style"><a>{$smarty.const.HEADING_STYLE}</a></li>
        <li data-bs-toggle="tab" data-bs-target="#align"><a>{$smarty.const.HEADING_WIDGET_ALIGN}</a></li>
        <li data-bs-toggle="tab" data-bs-target="#visibility"><a>{$smarty.const.TEXT_VISIBILITY_ON_PAGES}</a></li>

      </ul>
      <div class="tab-content">
        <div class="tab-pane active menu-list" id="type">





          <div class="setting-row">
            <label for="">{$smarty.const.TEXT_SHOW}</label>
            <select name="setting[0][by_language]" id="" class="form-control">
              <option value=""{if $settings[0].by_language == ''} selected{/if}>{$smarty.const.TEXT_ALL_VIDEOS}</option>
              <option value="1"{if $settings[0].by_language == '1'} selected{/if}>{$smarty.const.VIDEO_MATCH_LANGUAGE}</option>
            </select>
          </div>

          <div class="setting-row">
            <label for="">{$smarty.const.TEXT_POSITION}</label>
            <select name="setting[0][align_position]" id="" class="form-control">
              <option value=""{if $settings[0].align_position == ''} selected{/if}>{$smarty.const.TEXT_VERTICAL}</option>
              <option value="horizontal"{if $settings[0].align_position == 'horizontal'} selected{/if}>{$smarty.const.TEXT_HORIZONTAL}</option>
            </select>
          </div>


          <div class="setting-row">
            <label for="">{$smarty.const.TEXT_WIDTH}</label>
            <input type="number" name="setting[0][video_width]" value="{$settings[0].video_width}" class="form-control" /><span class="px">px</span>
          </div>
          <div class="setting-row">
            <label for="">{$smarty.const.TEXT_HEIGHT}</label>
            <input type="number" name="setting[0][video_height]" value="{$settings[0].video_height}" class="form-control" /><span class="px">px</span>
          </div>

          <p><label><input type="checkbox" name="setting[0][rel]"{if $settings[0].rel} checked{/if}/> {$smarty.const.TEXT_RELATED_VIDEO}</label></p>
          <p><label><input type="checkbox" name="setting[0][controls]"{if $settings[0].controls} checked{/if}/> {$smarty.const.TEXT_CONTROL_PANEL}</label></p>
          <p><label><input type="checkbox" name="setting[0][showinfo]"{if $settings[0].showinfo} checked{/if}/> {$smarty.const.TEXT_PLAYER_FUNCTION}</label></p>




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