{use class="Yii"}
<form action="{Yii::getAlias('@web')}/design/box-save" method="post" id="box-save">
  <input type="hidden" name="id" value="{$id}"/>
  <div class="popup-heading">
    {$smarty.const.BOX_CATALOG_PROPERTIES}
  </div>
  <div class="popup-content">




    <div class="tabbable tabbable-custom">
      <ul class="nav nav-tabs">

        <li class="active" data-bs-toggle="tab" data-bs-target="#type"><a>{$smarty.const.BOX_CATALOG_PROPERTIES}</a></li>
        <li data-bs-toggle="tab" data-bs-target="#style"><a>{$smarty.const.HEADING_STYLE}</a></li>
        <li data-bs-toggle="tab" data-bs-target="#align"><a>{$smarty.const.HEADING_WIDGET_ALIGN}</a></li>
        <li data-bs-toggle="tab" data-bs-target="#visibility"><a>{$smarty.const.TEXT_VISIBILITY_ON_PAGES}</a></li>

      </ul>
      <div class="tab-content">
        <div class="tab-pane active menu-list" id="type">






          <div class="setting-row">
            <label for="">Category</label>
            <select name="setting[0][category]" id="" class="form-control">
              <option value=""{if $settings[0].category == ''} selected{/if}>{$smarty.const.TEXT_BTN_NO}</option>
              <option value="1"{if $settings[0].category == '1'} selected{/if}>{$smarty.const.TEXT_BTN_YES}</option>
            </select>
          </div>
          <div class="setting-row">
            <label for="">{$smarty.const.TEXT_SHOW_MANUFACTURER}</label>
            <select name="setting[0][show_manufacturer]" id="" class="form-control">
              <option value=""{if $settings[0].show_manufacturer == ''} selected{/if}>{$smarty.const.TEXT_BTN_YES}</option>
              <option value="no"{if $settings[0].show_manufacturer == 'no'} selected{/if}>{$smarty.const.TEXT_BTN_NO}</option>
            </select>
          </div>
          <div class="setting-row">
            <label for="">{$smarty.const.TEXT_SHOW_MODEL}</label>
            <select name="setting[0][show_sku]" id="" class="form-control">
              <option value=""{if $settings[0].show_sku == ''} selected{/if}>{$smarty.const.TEXT_BTN_YES}</option>
              <option value="no"{if $settings[0].show_sku == 'no'} selected{/if}>{$smarty.const.TEXT_BTN_NO}</option>
            </select>
          </div>
          <div class="setting-row">
            <label for="">{$smarty.const.TEXT_SHOW_EAN}</label>
            <select name="setting[0][show_ean]" id="" class="form-control">
              <option value=""{if $settings[0].show_ean == ''} selected{/if}>{$smarty.const.TEXT_BTN_YES}</option>
              <option value="no"{if $settings[0].show_ean == 'no'} selected{/if}>{$smarty.const.TEXT_BTN_NO}</option>
            </select>
          </div>
          <div class="setting-row">
            <label for="">{$smarty.const.TEXT_SHOW_ISBN}</label>
            <select name="setting[0][show_isbn]" id="" class="form-control">
              <option value=""{if $settings[0].show_isbn == ''} selected{/if}>{$smarty.const.TEXT_BTN_YES}</option>
              <option value="no"{if $settings[0].show_isbn == 'no'} selected{/if}>{$smarty.const.TEXT_BTN_NO}</option>
            </select>
          </div>
          <div class="setting-row">
            <label for="">{$smarty.const.TEXT_SHOW_ASIN}</label>
            <select name="setting[0][show_asin]" id="" class="form-control">
              <option value=""{if $settings[0].show_asin == ''} selected{/if}>{$smarty.const.TEXT_BTN_YES}</option>
              <option value="no"{if $settings[0].show_asin == 'no'} selected{/if}>{$smarty.const.TEXT_BTN_NO}</option>
            </select>
          </div>
          <div class="setting-row">
            <label for="">{$smarty.const.TEXT_UPC}</label>
            <select name="setting[0][show_upc]" id="" class="form-control">
              <option value=""{if $settings[0].show_upc == ''} selected{/if}>{$smarty.const.TEXT_BTN_YES}</option>
              <option value="no"{if $settings[0].show_upc == 'no'} selected{/if}>{$smarty.const.TEXT_BTN_NO}</option>
            </select>
          </div>
          <div class="setting-row">
            <label for="">Main property</label>
            <select name="setting[0][main_property]" id="" class="form-control">
              <option value=""{if $settings[0].main_property == ''} selected{/if}>{$smarty.const.TEXT_BTN_YES}</option>
              <option value="no"{if $settings[0].main_property == 'no'} selected{/if}>{$smarty.const.TEXT_BTN_NO}</option>
            </select>
          </div>
          <div class="setting-row">
            <label for="">Clickable property filters</label>
            <select name="setting[0][clickable_property_filters]" id="" class="form-control">
              <option value=""{if $settings[0].clickable_property_filters == ''} selected{/if}>{$smarty.const.TEXT_BTN_YES} (current category)</option>
              <option value="all"{if $settings[0].clickable_property_filters == 'all'} selected{/if}>{$smarty.const.TEXT_BTN_YES} (all products range)</option>
              <option value="no"{if $settings[0].clickable_property_filters == 'no'} selected{/if}>{$smarty.const.TEXT_BTN_NO}</option>
            </select>
          </div>

          <div class="setting-row">
            <label for="">H tag for name</label>
            <select name="setting[0][name_h]" id="" class="form-control">
              <option value=""{if $settings[0].name_h == ''} selected{/if}>{$smarty.const.TEXT_DEFAULT}</option>
              <option value="h2"{if $settings[0].name_h == 'h2'} selected{/if}>h2</option>
              <option value="h3"{if $settings[0].name_h == 'h3'} selected{/if}>h3</option>
              <option value="h4"{if $settings[0].name_h == 'h4'} selected{/if}>h4</option>
              <option value="h5"{if $settings[0].name_h == 'h5'} selected{/if}>h5</option>
              <option value="h6"{if $settings[0].name_h == 'h6'} selected{/if}>h6</option>
            </select>
          </div>
          <div class="setting-row">
            <label for="">H tag for value</label>
            <select name="setting[0][value_h]" id="" class="form-control">
              <option value=""{if $settings[0].value_h == ''} selected{/if}>{$smarty.const.TEXT_DEFAULT}</option>
              <option value="h2"{if $settings[0].value_h == 'h2'} selected{/if}>h2</option>
              <option value="h3"{if $settings[0].value_h == 'h3'} selected{/if}>h3</option>
              <option value="h4"{if $settings[0].value_h == 'h4'} selected{/if}>h4</option>
              <option value="h5"{if $settings[0].value_h == 'h5'} selected{/if}>h5</option>
              <option value="h6"{if $settings[0].value_h == 'h6'} selected{/if}>h6</option>
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