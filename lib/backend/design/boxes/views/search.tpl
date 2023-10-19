{use class="Yii"}
<form action="{$app->request->baseUrl}/design/box-save" method="post" id="box-save">
  <input type="hidden" name="id" value="{$id}"/>
  <div class="popup-heading">
    {$smarty.const.TEXT_SHOPPING_CART}
  </div>
  <div class="popup-content box-img">


    <div class="tabbable tabbable-custom">
      <ul class="nav nav-tabs">

        <li class="active" data-bs-toggle="tab" data-bs-target="#cart"><a>{$smarty.const.TEXT_MAIN_DETAILS}</a></li>
        <li data-bs-toggle="tab" data-bs-target="#style"><a>{$smarty.const.HEADING_STYLE}</a></li>
        <li data-bs-toggle="tab" data-bs-target="#align"><a>{$smarty.const.HEADING_WIDGET_ALIGN}</a></li>
        <li data-bs-toggle="tab" data-bs-target="#visibility"><a>{$smarty.const.TEXT_VISIBILITY_ON_PAGES}</a></li>

      </ul>
      <div class="tab-content">


        <div class="tab-pane active" id="cart">

          <div class="setting-row">
            <label for="">{$smarty.const.SHOW_SEARCH_HISTORY}</label>
            <select name="setting[0][search_history]" id="" class="form-control" style="width: 100px">
              <option value=""{if $settings[0].search_history == ''} selected{/if}>{$smarty.const.TEXT_NO}</option>
              <option value="1"{if $settings[0].search_history == '1'} selected{/if}>{$smarty.const.TEXT_YES}</option>
            </select>
          </div>

          <div class="setting-row">
            <label for="">{$smarty.const.REMEMBER_HISTORY_ITEMS}</label>
            <input type="number" name="setting[0][history_items]" class="form-control" value="{$settings[0].history_items}"/>
          </div>

          <input type="hidden" name="uploads" value="1"/>

        </div>
        <div class="tab-pane" id="style">
          {$responsive_settings = ['only-icon.tpl']}
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