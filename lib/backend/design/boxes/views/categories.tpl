{use class="Yii"}
<form action="{Yii::getAlias('@web')}/design/box-save" method="post" id="box-save">
  <input type="hidden" name="id" value="{$id}"/>
  <div class="popup-heading">
    {$smarty.const.TEXT_CATEGORIES}
  </div>
  <div class="popup-content">




    <div class="tabbable tabbable-custom">
      <ul class="nav nav-tabs">

        <li class="active" data-bs-toggle="tab" data-bs-target="#type"><a>{$smarty.const.TEXT_SETTINGS}</a></li>
        <li data-bs-toggle="tab" data-bs-target="#product"><a>{$smarty.const.TEXT_PRODUCT_ITEM}</a></li>
        <li data-bs-toggle="tab" data-bs-target="#style"><a>{$smarty.const.HEADING_STYLE}</a></li>
        <li data-bs-toggle="tab" data-bs-target="#align"><a>{$smarty.const.HEADING_WIDGET_ALIGN}</a></li>
        <li data-bs-toggle="tab" data-bs-target="#visibility"><a>{$smarty.const.TEXT_VISIBILITY_ON_PAGES}</a></li>

      </ul>
      <div class="tab-content">
        <div class="tab-pane active menu-list" id="type">


          <div class="setting-row">
            <label for="">{$smarty.const.TEXT_MAX_ITEMS}</label>
            <input type="text" name="setting[0][max_items]" class="form-control" value="{$settings[0].max_items}"/>
          </div>

          <div class="setting-row lazy-load">
            <label for="">Skip use product image for categories without own image</label>
            <select name="setting[0][skip_product_image]" id="" class="form-control">
              <option value=""{if $settings[0].skip_product_image == ''} selected{/if}>{$smarty.const.TEXT_NO}</option>
              <option value="1"{if $settings[0].skip_product_image == '1'} selected{/if}>{$smarty.const.TEXT_YES}</option>
            </select>
          </div>

          <div class="setting-row lazy-load">
            <label for="">Show images</label>
            <select name="setting[0][hide_images]" id="" class="form-control">
              <option value=""{if $settings[0].hide_images == ''} selected{/if}>{$smarty.const.TEXT_YES}</option>
              <option value="1"{if $settings[0].hide_images == '1'} selected{/if}>{$smarty.const.TEXT_NO}</option>
            </select>
          </div>

          <div class="setting-row">
            <label for="">{$smarty.const.TEXT_VIEW_AS}</label>
            <select name="setting[0][view_as]" id="" class="form-control">
              <option value=""{if $settings[0].view_as == ''} selected{/if}>{$smarty.const.TEXT_DEFAULT}</option>
              <option value="carousel"{if $settings[0].view_as == 'carousel'} selected{/if}>{$smarty.const.TEXT_CAROUSEL}</option>
            </select>
          </div>

            {include 'include/lazy_load.tpl'}

            {include 'include/ajax.tpl'}

          {include 'include/col_in_row.tpl'}

        </div>
        <div class="tab-pane" id="product">
          {include 'include/listings-product.tpl'}
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