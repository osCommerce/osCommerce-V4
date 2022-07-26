{use class="Yii"}
<form action="{Yii::getAlias('@web')}/design/box-save" method="post" id="box-save">
  <input type="hidden" name="id" value="{$id}"/>
  <div class="popup-heading">
    {$smarty.const.TABLE_HEADING_PRODUCTS_MULTIINVENTORY}
  </div>
  <div class="popup-content">




    <div class="tabbable tabbable-custom">
      <ul class="nav nav-tabs">

        <li class="active"><a href="#type" data-toggle="tab">{$smarty.const.TABLE_HEADING_PRODUCTS_MULTIINVENTORY}</a></li>
        <li><a href="#style" data-toggle="tab">{$smarty.const.HEADING_STYLE}</a></li>
        <li><a href="#align" data-toggle="tab">{$smarty.const.HEADING_WIDGET_ALIGN}</a></li>
        <li><a href="#visibility" data-toggle="tab">{$smarty.const.TEXT_VISIBILITY_ON_PAGES}</a></li>

      </ul>
      <div class="tab-content">
        <div class="tab-pane active menu-list" id="type">
          <div class="setting-row">
            <label for="">{$smarty.const.TEXT_PRICE_OPTION}</label>
            <select name="setting[0][price_option]" id="" class="form-control">
              <option value="0"{if $settings[0].price_option == '0'} selected{/if}>{$smarty.const.TEXT_PRICE_DIFFERENCE}</option>
              <option value="1"{if $settings[0].price_option == '1'} selected{/if}>{$smarty.const.TEXT_FULL_PRICE}</option>
              <option value="2"{if $settings[0].price_option == '2'} selected{/if}>{$smarty.const.TEXT_DONT_SHOW_PRICE}</option>
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