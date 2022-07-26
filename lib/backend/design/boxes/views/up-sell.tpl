{use class="Yii"}
<form action="{Yii::getAlias('@web')}/design/box-save" method="post" id="box-save">
  <input type="hidden" name="id" value="{$id}"/>
  <div class="popup-heading">
    {$smarty.const.TEXT_CROSS_SELL_PRODUCTS}
  </div>
  <div class="popup-content">




    <div class="tabbable tabbable-custom">
      <ul class="nav nav-tabs">

        <li class="active"><a href="#type" data-toggle="tab">{$smarty.const.TEXT_NEW_PRODUCTS}</a></li>
        <li><a href="#product" data-toggle="tab">{$smarty.const.TEXT_PRODUCT_ITEM}</a></li>
        <li><a href="#style" data-toggle="tab">{$smarty.const.HEADING_STYLE}</a></li>
        <li><a href="#align" data-toggle="tab">{$smarty.const.HEADING_WIDGET_ALIGN}</a></li>
        <li><a href="#visibility" data-toggle="tab">{$smarty.const.TEXT_VISIBILITY_ON_PAGES}</a></li>

      </ul>
      <div class="tab-content">
        <div class="tab-pane active menu-list" id="type">

          <div class="setting-row">
            <label for="">{$smarty.const.CHOOSE_PLATFORM}</label>
            <select name="setting[0][platform]" class="form-control">
              <option value=""{if $settings[0].platform == ''} selected{/if}>{$smarty.const.TEXT_CURRENT}</option>
                {foreach $platformList as $platform}
                  <option value="{$platform.id}"{if $settings[0].platform == $platform.id} selected{/if}>{$platform.text}</option>
                {/foreach}
            </select>
          </div>

          <div class="setting-row">
            <label for="">{$smarty.const.TEXT_MAX_PRODUCTS}</label>
            <input type="text" name="params" class="form-control" value="{$params}"/>
          </div>
          <div class="setting-row">
            <label for="">{$smarty.const.TEXT_VIEW_AS}</label>
            <select name="setting[0][view_as]" id="" class="form-control">
              <option value=""{if $settings[0].view_as == ''} selected{/if}>{$smarty.const.TEXT_DEFAULT}</option>
              <option value="carousel"{if $settings[0].view_as == 'carousel'} selected{/if}>{$smarty.const.TEXT_CAROUSEL}</option>
            </select>
          </div>


          <div class="setting-row">
            <label for="">{$smarty.const.HIDE_PARENTS_IF_EMPTY}</label>
            <select name="setting[0][hide_parents]" id="" class="form-control">
              <option value=""{if $settings[0].hide_parents == ''} selected{/if}>{$smarty.const.TEXT_NO}</option>
              <option value="1"{if $settings[0].hide_parents == '1'} selected{/if}>1</option>
              <option value="2"{if $settings[0].hide_parents == '2'} selected{/if}>2</option>
              <option value="3"{if $settings[0].hide_parents == '3'} selected{/if}>3</option>
              <option value="4"{if $settings[0].hide_parents == '4'} selected{/if}>4</option>
            </select>
          </div>

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