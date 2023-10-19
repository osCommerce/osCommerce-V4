{use class="Yii"}
<form action="{Yii::getAlias('@web')}/design/box-save" method="post" id="box-save">
  <input type="hidden" name="id" value="{$id}"/>
  <div class="popup-heading">
    {$smarty.const.TEXT_FILTERS}
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
            <label for="">{$smarty.const.TEXT_POSITION}</label>
            <select name="setting[0][align_position]" id="" class="form-control">
              <option value=""{if $settings[0].align_position == ''} selected{/if}>{$smarty.const.TEXT_VERTICAL}</option>
              <option value="horizontal"{if $settings[0].align_position == 'horizontal'} selected{/if}>{$smarty.const.TEXT_HORIZONTAL}</option>
            </select>
          </div>

          <div class="setting-row">
            <label for="">{$smarty.const.DEFAULT_FILTER_BOXES}</label>
            <select name="setting[0][open_filter]" id="" class="form-control">
              <option value=""{if $settings[0].open_filter == ''} selected{/if}>{$smarty.const.TEXT_OPENED}</option>
              <option value="closed"{if $settings[0].open_filter == 'closed'} selected{/if}>{$smarty.const.TEXT_CLOSED}</option>
            </select>
          </div>

          <div class="setting-row">
            <label for="">{$smarty.const.VISIBLE_OPTIONS}</label>
            <input name="setting[0][visible_options]" value="{$settings[0].visible_options}" placeholder="7" id="" class="form-control">
          </div>

          <div class="setting-row">
            <label for="">{$smarty.const.HIDE_PROPERTY_WITH_UNIQUE_OPTION}</label>
            <select name="setting[0][hide_unique]" id="" class="form-control">
              <option value=""{if $settings[0].hide_unique == ''} selected{/if}>{$smarty.const.TEXT_NO}</option>
              <option value="1"{if $settings[0].hide_unique == '1'} selected{/if}>{$smarty.const.TEXT_YES}</option>
            </select>
          </div>

          <div class="setting-row">
            <label for="">{$smarty.const.TEXT_LOAD_BOX}</label>
            <select name="setting[0][ajax_load]" id="" class="form-control">
              <option value=""{if $settings[0].ajax_load == ''} selected{/if}>{$smarty.const.TEXT_WITH_CONTENT}</option>
              <option value="1"{if $settings[0].ajax_load == '1'} selected{/if}>{$smarty.const.TEXT_BY_AJAX}</option>
            </select>
          </div>






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