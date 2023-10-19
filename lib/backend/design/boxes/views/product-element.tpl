{use class="Yii"}


<form action="{$app->request->baseUrl}/design/box-save" method="post" id="box-save">
  <input type="hidden" name="id" value="{$id}"/>
  <div class="popup-heading">
    {$smarty.const.TEXT_PRODUCT_ELEMENT}
  </div>
  <div class="popup-content box-sale">


    <div class="tabbable tabbable-custom">
      <ul class="nav nav-tabs">

        <li class="active" data-bs-toggle="tab" data-bs-target="#text"><a>{$smarty.const.TEXT_PRODUCT_ELEMENT}</a></li>
        <li data-bs-toggle="tab" data-bs-target="#style"><a>{$smarty.const.HEADING_STYLE}</a></li>
        <li data-bs-toggle="tab" data-bs-target="#align"><a>{$smarty.const.HEADING_WIDGET_ALIGN}</a></li>
        <li data-bs-toggle="tab" data-bs-target="#visibility"><a>{$smarty.const.TEXT_VISIBILITY_ON_PAGES}</a></li>

      </ul>
      <div class="tab-content">

        <div class="tab-pane active" id="text">

          <div class="setting-row">
            <label for="">{$smarty.const.CHOOSE_PRODUCT_ELEMENT}</label>
            <select name="setting[0][product_element]" id="" class="form-control">
              <option value=""{if $settings[0].product_element == ''} selected{/if}></option>
              <option value="products_name"{if $settings[0].product_element == 'products_name'} selected{/if}>Name</option>
              <option value="products_price"{if $settings[0].product_element == 'products_price'} selected{/if}>Price</option>
              <option value="products_price_old"{if $settings[0].product_element == 'products_price_old'} selected{/if}>Old Price</option>
              <option value="image"{if $settings[0].product_element == 'image'} selected{/if}>image</option>
              <option value="products_model"{if $settings[0].product_element == 'products_model'} selected{/if}>model</option>
            </select>
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