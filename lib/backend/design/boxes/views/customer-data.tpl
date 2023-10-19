{use class="Yii"}
<form action="{Yii::getAlias('@web')}/design/box-save" method="post" id="box-save">
  <input type="hidden" name="id" value="{$id}"/>
  <div class="popup-heading">&nbsp;
  </div>
  <div class="popup-content">




    <div class="tabbable tabbable-custom">
      <ul class="nav nav-tabs">

        <li class="active" data-bs-toggle="tab" data-bs-target="#set"><a>{$smarty.const.TEXT_CUSTOMER_DATA}</a></li>
        <li data-bs-toggle="tab" data-bs-target="#style"><a>{$smarty.const.HEADING_STYLE}</a></li>
        <li data-bs-toggle="tab" data-bs-target="#align"><a>{$smarty.const.HEADING_WIDGET_ALIGN}</a></li>
        <li data-bs-toggle="tab" data-bs-target="#visibility"><a>{$smarty.const.TEXT_VISIBILITY_ON_PAGES}</a></li>
        <li data-bs-toggle="tab" data-bs-target="#ajax"><a>{$smarty.const.TEXT_AJAX}</a></li>

      </ul>
      <div class="tab-content">
        <div class="tab-pane active" id="set">



          <div class="setting-row">
            <label for="">{$smarty.const.TEXT_CUSTOMER_DATA}</label>
            <select name="setting[0][customers_data]" id="" class="form-control">
              <option value=""{if $settings[0].customers_data == ''} selected{/if}></option>
              <option value="points"{if $settings[0].customers_data == 'points'} selected{/if}>{$smarty.const.TEXT_POINTS_EARN}</option>
              <option value="credit_amount"{if $settings[0].customers_data == 'credit_amount'} selected{/if}>{$smarty.const.CREDIT_AMOUNT}</option>
              <option value="customer_name"{if $settings[0].customers_data == 'customer_name'} selected{/if}>{$smarty.const.TEXT_CUSTOMER_NAME}</option>
              <option value="group"{if $settings[0].customers_data == 'group'} selected{/if}>{$smarty.const.TEXT_GROUP}</option>
            </select>
          </div>



        </div>
        <div class="tab-pane" id="style">
            {$responsive_settings = ['only-icon.tpl']}
            {include 'include/style.tpl'}
        </div>
        <div class="tab-pane" id="align">
            {include 'include/align.tpl'}
        </div>
        <div class="tab-pane" id="visibility">
            {include 'include/visibility.tpl'}
        </div>
        <div class="tab-pane" id="ajax">
            {include 'include/ajax.tpl'}
        </div>

      </div>
    </div>


  </div>
  <div class="popup-buttons">
    <button type="submit" class="btn btn-primary btn-save">{$smarty.const.IMAGE_SAVE}</button>

    <span class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</span>

  </div>
</form>