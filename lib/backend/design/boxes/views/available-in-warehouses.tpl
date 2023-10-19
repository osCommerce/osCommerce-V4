{use class="Yii"}
<form action="{Yii::getAlias('@web')}/design/box-save" method="post" id="box-save">
  <input type="hidden" name="id" value="{$id}"/>
  <div class="popup-heading">
    {$smarty.const.TEXT_AVAILABLE_AT_WAREHOUSES}
  </div>
  <div class="popup-content">




    <div class="tabbable tabbable-custom">
      <ul class="nav nav-tabs">

        <li class="active" data-bs-toggle="tab" data-bs-target="#type"><a>{$smarty.const.TEXT_AVAILABLE_AT_WAREHOUSES}</a></li>
        <li data-bs-toggle="tab" data-bs-target="#style"><a>{$smarty.const.HEADING_STYLE}</a></li>
        <li data-bs-toggle="tab" data-bs-target="#align"><a>{$smarty.const.HEADING_WIDGET_ALIGN}</a></li>
        <li data-bs-toggle="tab" data-bs-target="#visibility"><a>{$smarty.const.TEXT_VISIBILITY_ON_PAGES}</a></li>

      </ul>
      <div class="tab-content">
        <div class="tab-pane active menu-list" id="type">

          <div class="setting-row">
            <label for="">{$smarty.const.AVAILABLE_AT_WAREHOUSES_SHOW_ADDRESS}</label>
            <input type="checkbox" name="setting[0][show_address]" class="form-control" value="1" {if $settings[0].show_address == '1'}checked{/if}/>
          </div>
          <div class="setting-row">
            <label for="">{$smarty.const.AVAILABLE_AT_WAREHOUSES_SHOW_TIME}</label>
            <input type="checkbox" name="setting[0][show_time]" class="form-control" value="1" {if $settings[0].show_time == '1'}checked{/if}/>
          </div>
          <div class="setting-row">
            <label for="">{$smarty.const.AVAILABLE_AT_WAREHOUSES_SHOW_QTY}</label>
            <input type="checkbox" name="setting[0][show_qty]" class="form-control" value="1" {if $settings[0].show_qty == '1'}checked{/if}/>
          </div>
          <div class="setting-row">
            <label for="">{$smarty.const.AVAILABLE_AT_WAREHOUSES_SHOW_QTY_AS_LEVELS}</label>
            <input type="checkbox" name="setting[0][show_as_levels]" class="form-control" value="1" {if $settings[0].show_as_levels == '1'}checked{/if} onclick="$('.available-at-qty-div').toggle();"/>
          </div>


          <div class="setting-row available-at-qty-div" id="available_at_qty_div" {if $settings[0].show_as_levels == 1}style="display:none"{/if} >

            <div class="setting-row">
              <label for="">{$smarty.const.AVAILABLE_AT_WAREHOUSES_SHOW_QTY_LESS}</label>
              <input type="text" name="setting[0][show_qty_less]" class="form-control" value="{$settings[0].show_qty_less}"/>
            </div>

          </div>

          <div class="setting-row available-at-qty-div" id="available_at_qty_div" {if $settings[0].show_as_levels != 1}style="display:none"{/if}>

            <div class="setting-row">
              <label for="">{$smarty.const.AVAILABLE_AT_WAREHOUSES_SHOW_QTY_LEVEL1}</label>
              <input type="text" name="setting[0][show_qty_level1]" class="form-control" value="{$settings[0].show_qty_level1}"/>
            </div>
            <div class="setting-row">
              <label for="">{$smarty.const.AVAILABLE_AT_WAREHOUSES_SHOW_QTY_LEVEL2}</label>
              <input type="text" name="setting[0][show_qty_level2]" class="form-control" value="{$settings[0].show_qty_level2}"/>
            </div>

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