{use class="Yii"}
<form action="{Yii::getAlias('@web')}/design/box-save" method="post" id="box-save">
  <input type="hidden" name="id" value="{$id}"/>
  <div class="popup-heading">
      {$smarty.const.TEXT_PRODUCT_ELEMENT}
  </div>
  <div class="popup-content">




    <div class="tabbable tabbable-custom">
      <ul class="nav nav-tabs">

        <li class="active" data-bs-toggle="tab" data-bs-target="#type"><a>{$smarty.const.TEXT_PRODUCT_ELEMENT}</a></li>
        <li data-bs-toggle="tab" data-bs-target="#style"><a>{$smarty.const.HEADING_STYLE}</a></li>
        <li data-bs-toggle="tab" data-bs-target="#align"><a>{$smarty.const.HEADING_WIDGET_ALIGN}</a></li>
        <li data-bs-toggle="tab" data-bs-target="#visibility"><a>{$smarty.const.TEXT_VISIBILITY_ON_PAGES}</a></li>

      </ul>
      <div class="tab-content">
        <div class="tab-pane active menu-list" id="type">




          <div class="setting-row">
            <label for="">{$smarty.const.TEXT_PRODUCT_ELEMENT}</label>
            <select name="setting[0][element]" id="" class="form-control">
              <option value=""{if $settings[0].element == ''} selected{/if}></option>
              <option value="name"{if $settings[0].element == 'name'} selected{/if}>{$smarty.const.TEXT_NAME}</option>
              <option value="image"{if $settings[0].element == 'image'} selected{/if}>{$smarty.const.TEXT_IMAGE_SMALL}</option>
              <option value="image_med"{if $settings[0].element == 'image_med'} selected{/if}>{$smarty.const.TEXT_IMAGE_MEDIUM}</option>
              <option value="image_lrg"{if $settings[0].element == 'image_lrg'} selected{/if}>{$smarty.const.TEXT_IMAGE_LARGE}</option>
              <option value="description"{if $settings[0].element == 'description'} selected{/if}>{$smarty.const.TEXT_PRODUCTS_DESCRIPTION}</option>
              <option value="short_description"{if $settings[0].element == 'short_description'} selected{/if}>{$smarty.const.TEXT_PRODUCTS_DESCRIPTION_SHORT}</option>
              <option value="price"{if $settings[0].element == 'price'} selected{/if}>{$smarty.const.TEXT_PRODUCTS_PRICE_INFO}</option>
              <option value="model"{if $settings[0].element == 'model'} selected{/if}>{$smarty.const.TEXT_MODEL}</option>
              <option value="stock"{if $settings[0].element == 'stock'} selected{/if}>{$smarty.const.BOX_SETTINGS_BOX_STOCK_INDICATION}</option>
              <option value="properties"{if $settings[0].element == 'properties'} selected{/if}>{$smarty.const.BOX_CATALOG_PROPERTIES}</option>
            </select>
          </div>

          <div class="setting-row">
            <label for="">{$smarty.const.ADD_LINK_ON_PRODUCT}</label>
            <select name="setting[0][add_link]" id="" class="form-control">
              <option value=""{if $settings[0].add_link == ''} selected{/if}>{$smarty.const.TEXT_NO}</option>
              <option value="1"{if $settings[0].add_link == '1'} selected{/if}>{$smarty.const.TEXT_YES}</option>
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