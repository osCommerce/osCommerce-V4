{use class="Yii"}
<form action="{$app->request->baseUrl}/design/box-save" method="post" id="box-save">
  <input type="hidden" name="id" value="{$id}"/>
  <div class="popup-heading">
    {$smarty.const.TEXT_LISTING_SETTINGS}
  </div>
  <div class="popup-content box-img">




    <div class="tabbable tabbable-custom">
      <ul class="nav nav-tabs">

        <li class="active"><a href="#type" data-toggle="tab">{$smarty.const.TEXT_LISTING_SETTINGS}</a></li>
        <li><a href="#product" data-toggle="tab">{$smarty.const.TEXT_PRODUCT_ITEM}</a></li>
        <li><a href="#style" data-toggle="tab">{$smarty.const.HEADING_STYLE}</a></li>
        <li><a href="#align" data-toggle="tab">{$smarty.const.HEADING_WIDGET_ALIGN}</a></li>

      </ul>
      <div class="tab-content">
        <div class="tab-pane active menu-list" id="type">


          <div class="setting-row">
            <label for="">{$smarty.const.TEXT_PRODUCTS_ON_PAGE}</label>
            <input type="text" name="setting[0][items_on_page]" class="form-control" value="{$settings[0].items_on_page}"/>
          </div>
          <div class="setting-row">
            <label for="">{$smarty.const.TEXT_ALIGN_PRODUCTS}</label>
            <select name="setting[0][products_align]" id="" class="form-control">
              <option value="center"{if $settings[0].products_align == ''} selected{/if}>{$smarty.const.TEXT_CENTER}</option>
              <option value="left"{if $settings[0].products_align == 'left'} selected{/if}>{$smarty.const.TEXT_LEFT}</option>
              <option value="right"{if $settings[0].products_align == 'right'} selected{/if}>{$smarty.const.TEXT_RIGHT}</option>
            </select>
          </div>
          <div class="setting-row">
            <label for="">{$smarty.const.TEXT_PAGINATION}</label>
            <select name="setting[0][fbl]" id="" class="form-control">
              <option value=""{if $settings[0].fbl == ''} selected{/if}>{$smarty.const.TEXT_SPLIT_BY_PAGE}</option>
              <option value="1"{if $settings[0].fbl == '1'} selected{/if}>{$smarty.const.TEXT_FACEBOOK_LIKE}</option>
            </select>
          </div>

          <div class="setting-row">
            <label for="">{$smarty.const.TEG_FOR_PRODUCTS_NAMES}</label>
            <select name="setting[0][product_names_teg]" id="" class="form-control">
              <option value=""{if $settings[0].product_names_teg == ''} selected{/if}>div</option>
              <option value="h2"{if $settings[0].product_names_teg == 'h2'} selected{/if}>h2</option>
              <option value="name_h2"{if $settings[0].product_names_teg == 'name_h2'} selected{/if}>{$smarty.const.PRODUCT_NAME_IN_H2}</option>
              <option value="h3"{if $settings[0].product_names_teg == 'h3'} selected{/if}>h3</option>
              <option value="name_h3"{if $settings[0].product_names_teg == 'name_h3'} selected{/if}>{$smarty.const.PRODUCT_NAME_IN_H3}</option>
              <option value="h4"{if $settings[0].product_names_teg == 'h4'} selected{/if}>h4</option>
              <option value="name_h4"{if $settings[0].product_names_teg == 'name_h4'} selected{/if}>{$smarty.const.PRODUCT_NAME_IN_H4}</option>
            </select>
          </div>
          
            {include 'include/lazy_load.tpl'}


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
          {$widget_listing = 1}
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