{use class="common\helpers\Html"}
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

          <div class="block after">
            <div class="menu-list  cbox-left">
              <div class="setting-row">
                <label for="setting_0__showPrice_" accesskey="p">{$smarty.const.TEXT_SHOW_PRICE}</label>
                {Html::checkbox('setting[0][show_prices]', $settings[0].show_prices, ['value'=>'1'])}
              </div>
              <div class="setting-row">
                <label for="setting_0__showImages_" accesskey="i">{$smarty.const.TEXT_SHOW_IMAGE}</label>
                {Html::dropDownList('setting[0][show_images]',$settings[0].show_images, ['swatches' => TEXT_SWATCHES, 'products' => TABLE_HEADING_PRODUCTS])}
              </div>

            </div>

            <div class="menu-list  cbox-right">

            </div>
          </div>

          <div class="setting-row">
            <label for="">Don't group by property categories IDs</label>
            <input type="text" name="setting[0][no_property_ids]" value="{$settings[0].no_property_ids}" id="" class="form-control" style="width: 243px">

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