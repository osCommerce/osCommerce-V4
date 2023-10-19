{use class="Yii"}
<form action="{$app->request->baseUrl}/design/box-save" method="post" id="box-save">
  <input type="hidden" name="id" value="{$id}"/>
  <div class="popup-heading">
    {$smarty.const.TEXT_SHOPPING_CART}
  </div>
  <div class="popup-content box-img">


    <div class="tabbable tabbable-custom">
      <ul class="nav nav-tabs">

        <li class="active" data-bs-toggle="tab" data-bs-target="#cart"><a>{$smarty.const.TEXT_SHOPPING_CART}</a></li>
        <li data-bs-toggle="tab" data-bs-target="#style"><a>{$smarty.const.HEADING_STYLE}</a></li>
        <li data-bs-toggle="tab" data-bs-target="#align"><a>{$smarty.const.HEADING_WIDGET_ALIGN}</a></li>
        <li data-bs-toggle="tab" data-bs-target="#visibility"><a>{$smarty.const.TEXT_VISIBILITY_ON_PAGES}</a></li>

      </ul>
      <div class="tab-content">


        <div class="tab-pane active" id="cart">

          <p><label><input type="checkbox" name="setting[0][total]"{if $settings[0].total} checked{/if}/> {$smarty.const.SHOW_TOTAL_PRICE}</label></p>
          <p><label><input type="checkbox" name="setting[0][items]"{if $settings[0].items} checked{/if}/> {$smarty.const.TEXT_SHOW_ITEMS}</label></p>

          <div class="setting-row">
            <label for="">{$smarty.const.TEXT_SHOW_PRODUCTS}</label>
            <select name="setting[0][show_products]" id="" class="form-control">
              <option value=""{if $settings[0].show_products == ''} selected{/if}>{$smarty.const.OPTION_NONE}</option>
              <option value="always"{if $settings[0].show_products == 'always'} selected{/if}>{$smarty.const.TEXT_ALWAYS}</option>
              <option value="dropdown"{if $settings[0].show_products == 'dropdown'} selected{/if}>{$smarty.const.IN_DROPDOWN_BU_HOVER}</option>
            </select>
          </div>


          {include 'include/ajax.tpl'}

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