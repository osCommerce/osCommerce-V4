{use class="Yii"}
<form action="{Yii::getAlias('@web')}/design/box-save" method="post" id="box-save">
  <input type="hidden" name="id" value="{$id}"/>
  <div class="popup-heading">
    {$smarty.const.TEXT_CATEGORIES}
  </div>
  <div class="popup-content">




    <div class="tabbable tabbable-custom">
      <ul class="nav nav-tabs">

        <li class="active"><a href="#type" data-toggle="tab">{$smarty.const.TEXT_SETTINGS}</a></li>
        <li><a href="#product" data-toggle="tab">{$smarty.const.TEXT_PRODUCT_ITEM}</a></li>
        <li><a href="#style" data-toggle="tab">{$smarty.const.HEADING_STYLE}</a></li>
        <li><a href="#align" data-toggle="tab">{$smarty.const.HEADING_WIDGET_ALIGN}</a></li>
        <li><a href="#visibility" data-toggle="tab">{$smarty.const.TEXT_VISIBILITY_ON_PAGES}</a></li>

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


          <div class="tabbable tabbable-custom">
            <ul class="nav nav-tabs">

              <li class="active"><a href="#list" data-toggle="tab">Main</a></li>
              {foreach $settings.media_query as $item}
                <li><a href="#list{$item.id}" data-toggle="tab">{$item.setting_value}</a></li>
              {/foreach}

            </ul>
            <div class="tab-content">
              <div class="tab-pane active menu-list" id="list">

                <div class="setting-row">
                  <label for="">{$smarty.const.TEXT_COLUMNS_IN_ROW}</label>
                  <input type="text" name="setting[0][col_in_row]" class="form-control" value="{$settings[0].col_in_row}"/>
                </div>

              </div>
              {foreach $settings.media_query as $item}
                <div class="tab-pane menu-list" id="list{$item.id}">

                  <div class="setting-row">
                    <label for="">{$smarty.const.TEXT_COLUMNS_IN_ROW}</label>
                    <input type="text" name="visibility[0][{$item.id}][col_in_row]" class="form-control" value="{$visibility[0][{$item.id}].col_in_row}"/>
                  </div>

                </div>
              {/foreach}

            </div>
          </div>

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