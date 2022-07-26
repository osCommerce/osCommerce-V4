{use class="common\helpers\Html"}
{use class="yii\helpers\ArrayHelper"}
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

          <div class="block after">
            <div class="menu-list  cbox-left">
              <div class="setting-row">
                <label for="setting_0__platform_" accesskey="c">{$smarty.const.TEXT_COMMON_PLATFORM_TAB}</label>
                {Html::dropDownList('setting[0][platform]', $settings[0].platform, [$smarty.const.TEXT_CURRENT] + ArrayHelper::map($platformList, 'id', 'text') ) }
              </div>

              <div class="setting-row">
                <label for="setting_0__xsellTypeId_" accesskey="t">{$smarty.const.BOX_LOCALIZATION_XSELL_TYPES}</label>
                {Html::dropDownList('setting[0][xsell_type_id]',$settings[0].xsell_type_id, $xsellTypeVariants)}
              </div>

              <div class="setting-row">
                <label for="">{$smarty.const.TEXT_MAX_PRODUCTS}</label>
                <input type="text" name="setting[0][max_products]" class="form-control" value="{$settings[0].max_products}"/>
              </div>

              <div class="setting-row">
                <label for="setting_0__showCartButton_" accesskey="b">{$smarty.const.TEXT_CART_BTN}</label>
                {Html::checkbox('setting[0][show_cart_button]', $settings[0].show_cart_button, ['value'=>'1'])}
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

            </div>

            <div class="menu-list  cbox-right">
              <div class="setting-row">
                <label for="">{$smarty.const.TEXT_VIEW_AS}</label>
                <select name="setting[0][view_as]" id="" class="form-control">
                  <option value=""{if $settings[0].view_as == ''} selected{/if}>{$smarty.const.TEXT_DEFAULT}</option>
                  <option value="carousel"{if $settings[0].view_as == 'carousel'} selected{/if}>{$smarty.const.TEXT_CAROUSEL}</option>
                </select>
              </div>

              <div class="setting-row">
                <label for="">{$smarty.const.TEG_FOR_PRODUCTS_NAMES}</label>
                <select name="setting[0][product_names_teg]" id="" class="form-control">
                  <option value=""{if $settings[0].product_names_teg == ''} selected{/if}>div</option>
                  <option value="h2"{if $settings[0].product_names_teg == 'h2'} selected{/if}>h2</option>
                  <option value="h3"{if $settings[0].product_names_teg == 'h3'} selected{/if}>h3</option>
                  <option value="h4"{if $settings[0].product_names_teg == 'h4'} selected{/if}>h4</option>
                </select>
              </div>
              {include 'include/ajax.tpl'}

              <div class="setting-row">
                <label for="setting_0__hideTitle_" accesskey="h">{$smarty.const.TEXT_CUSTOM_TITLE_CONSTANT}</label>
                {Html::textInput('setting[0][custom_title]', $settings[0].custom_title, [])}
                <span class="input-info">{$smarty.const.TEXT_CUSTOM_TITLE_CONSTANT_DESCRIPTION}</span>
              </div>
              
            </div>
          </div>

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