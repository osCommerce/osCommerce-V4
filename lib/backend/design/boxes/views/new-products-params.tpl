{use class="Yii"}
{use class="\common\helpers\Html"}
<form action="{Yii::getAlias('@web')}/design/box-save" method="post" id="box-save">
  <input type="hidden" name="id" value="{$id}"/>
  <div class="popup-heading">
    {$smarty.const.TEXT_NEW_BUNDLES}
  </div>
  <div class="popup-content">




    <div class="tabbable tabbable-custom">
      <ul class="nav nav-tabs">

        <li class="active" data-bs-toggle="tab" data-bs-target="#type"><a accesskey="1">{$smarty.const.TEXT_SETTINGS}</a></li>
        <li data-bs-toggle="tab" data-bs-target="#product"><a accesskey="2">{$smarty.const.TEXT_PRODUCT_ITEM}</a></li>
        <li data-bs-toggle="tab" data-bs-target="#style"><a accesskey="3">{$smarty.const.HEADING_STYLE}</a></li>
        <li data-bs-toggle="tab" data-bs-target="#align"><a>{$smarty.const.HEADING_WIDGET_ALIGN}</a></li>
        <li data-bs-toggle="tab" data-bs-target="#visibility"><a>{$smarty.const.TEXT_VISIBILITY_ON_PAGES}</a></li>

      </ul>

      <div class="tab-content">
        <div class="tab-pane active menu-list" id="type">
          <div class="block after">
            <div class="menu-list  cbox-left">

                <div class="setting-row">
                  <label for="">{$smarty.const.TEXT_MAX_PRODUCTS}</label>
                  <input type="text" name="params" class="form-control" value="{$params}"/>
                </div>
                <div class="setting-row1 after">
                  <div class="setting-row"><strong>{$smarty.const.TEXT_INCLUDE_EMPTY_ALL}</strong></div>
                  <input name="product_types[0]" type="hidden" value="0">{*array index is used as bit mask*}
                  <div class="col-md-5">
                    <p><label>{Html::checkbox('product_types[1]', $settings[0].product_types & 1)} {$smarty.const.TEXT_SIMPLE}</label></p>
                  </div>
                  <div class="col-md-5">
                    <p><label>{Html::checkbox('product_types[2]', $settings[0].product_types & 2)} {$smarty.const.TEXT_BUNDLES}</label></p>
                  </div>
                  <div class="col-md-5">
                    <p><label>{Html::checkbox('product_types[4]', $settings[0].product_types & 4)} {$smarty.const.TEXT_PC_CONF}</label></p>
                  </div>
                  <div class="col-md-5">
                    <label accesskey="c">{Html::checkbox('setting[0][same_category]', !!$settings[0].same_category)} {$smarty.const.TEXT_FROM_SAME_CATEGORY}</label>
                  </div>
                </div>

                <div class="setting-row">
                  <label for='prop_ids_list' accesskey="p">{$smarty.const.TEXT_SAME_PROPERTIES_VALUE_LIST}</label>{Html::input('text', 'setting[0][same_properties_value]', $settings[0].same_properties_value, ['id' => 'prop_ids_list'])}
                  <span class="input-info">{$smarty.const.TEXT_SAME_PROPERTIES_VALUE_LIST_DESCRIPTION}</span>
                </div>
                <div class="setting-row">
                  <label for="custom_get" accesskey="g">{$smarty.const.TEXT_CUSTOM_GET}</label>{Html::input('text', 'setting[0][get]', $settings[0].get, ['id' => 'custom_get'])}
                  <span class="input-info">{$smarty.const.TEXT_CUSTOM_GET_DESCRIPTION}</span>
                </div>



            </div>

            <div class="menu-list  cbox-right">
                <div class="setting-row">
                  <label for="view_as_select" accesskey="v">{$smarty.const.TEXT_VIEW_AS}</label>
                  <select name="setting[0][view_as]" id="view_as_select" class="form-control">
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
                  <label for="setting_0__hideTitle_" >{$smarty.const.TEXT_CUSTOM_TITLE_CONSTANT}</label>
                  {Html::textInput('setting[0][custom_title]', $settings[0].custom_title, [])}
                  <span class="input-info">{$smarty.const.TEXT_CUSTOM_TITLE_CONSTANT_DESCRIPTION}</span>
                </div>

            </div>
          </div>

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