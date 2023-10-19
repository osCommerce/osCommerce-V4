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
        <li class="active" data-bs-toggle="tab" data-bs-target="#type"><a>{$smarty.const.TEXT_NEW_PRODUCTS}</a></li>
        <li data-bs-toggle="tab" data-bs-target="#product"><a>{$smarty.const.TEXT_PRODUCT_ITEM}</a></li>
        <li data-bs-toggle="tab" data-bs-target="#style"><a>{$smarty.const.HEADING_STYLE}</a></li>
        <li data-bs-toggle="tab" data-bs-target="#align"><a>{$smarty.const.HEADING_WIDGET_ALIGN}</a></li>
        <li data-bs-toggle="tab" data-bs-target="#visibility"><a>{$smarty.const.TEXT_VISIBILITY_ON_PAGES}</a></li>
      </ul>

      <div class="tab-content">

        <div class="tab-pane active menu-list" id="type">

          <div class="block after">
            <div class="menu-list">

              <div class="setting-row">
                <label for="setting_0__xsellTypeId_" accesskey="t">{$smarty.const.BOX_LOCALIZATION_XSELL_TYPES}</label>
                {Html::dropDownList('setting[0][xsell_type_id]',$settings[0].xsell_type_id, $xsellTypeVariants)}
              </div>

              <div class="setting-row">
                <label for="">{$smarty.const.TEXT_MAX_PRODUCTS}</label>
                <input type="text" name="setting[0][max_products]" class="form-control" value="{$settings[0].max_products}"/>
              </div>

              
              {include 'include/ajax.tpl'}
              
            </div>
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