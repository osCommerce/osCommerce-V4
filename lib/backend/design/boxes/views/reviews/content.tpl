{use class="Yii"}
<form action="{Yii::getAlias('@web')}/design/box-save" method="post" id="box-save">
  <input type="hidden" name="id" value="{$id}"/>
  <div class="popup-heading">
      {$smarty.const.TABLE_TEXT_NAME}
  </div>
  <div class="popup-content">




    <div class="tabbable tabbable-custom">
      <ul class="nav nav-tabs">

        <li class="active" data-bs-toggle="tab" data-bs-target="#type"><a>{$smarty.const.TABLE_TEXT_NAME}</a></li>
        <li data-bs-toggle="tab" data-bs-target="#style"><a>{$smarty.const.HEADING_STYLE}</a></li>
        <li data-bs-toggle="tab" data-bs-target="#align"><a>{$smarty.const.HEADING_WIDGET_ALIGN}</a></li>
        <li data-bs-toggle="tab" data-bs-target="#visibility"><a>{$smarty.const.TEXT_VISIBILITY_ON_PAGES}</a></li>

      </ul>
      <div class="tab-content">
        <div class="tab-pane active menu-list" id="type">





          <div class="setting-row">
            <label for="">Pagination</label>
            <select name="setting[0][pagination]" id="" class="form-control">
              <option value=""{if $settings[0].pagination == ''} selected{/if}>{$smarty.const.TEXT_BTN_NO}</option>
              <option value="top"{if $settings[0].pagination == 'top'} selected{/if}>top</option>
              <option value="bottom"{if $settings[0].pagination == 'bottom'} selected{/if}>bottom</option>
              <option value="top-bottom"{if $settings[0].pagination == 'top-bottom'} selected{/if}>top-bottom</option>
            </select>

          </div>






        </div>
        <div class="tab-pane" id="style">
            {include '../include/style.tpl'}
        </div>
        <div class="tab-pane" id="align">
            {include '../include/align.tpl'}
        </div>
        <div class="tab-pane" id="visibility">
            {include '../include/visibility.tpl'}
        </div>

      </div>
    </div>


  </div>
  <div class="popup-buttons">
    <button type="submit" class="btn btn-primary btn-save">{$smarty.const.IMAGE_SAVE}</button>
    <span class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</span>
  </div>
</form>




