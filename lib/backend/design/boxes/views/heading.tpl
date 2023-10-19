{use class="Yii"}
<form action="{$app->request->baseUrl}/design/box-save" method="post" id="box-save">
  <input type="hidden" name="id" value="{$id}"/>
  <div class="popup-heading">
    {$smarty.const.TEXT_HEADING}
  </div>
  <div class="popup-content box-img">


    <div class="tabbable tabbable-custom">
      <ul class="nav nav-tabs">

        <li class="active" data-bs-toggle="tab" data-bs-target="#text"><a>{$smarty.const.TEXT_HEADING}</a></li>
        <li data-bs-toggle="tab" data-bs-target="#style"><a>{$smarty.const.HEADING_STYLE}</a></li>
        <li data-bs-toggle="tab" data-bs-target="#align"><a>{$smarty.const.HEADING_WIDGET_ALIGN}</a></li>
        <li data-bs-toggle="tab" data-bs-target="#visibility"><a>{$smarty.const.TEXT_VISIBILITY_ON_PAGES}</a></li>

      </ul>
      <div class="tab-content">

        <div class="tab-pane active" id="text">


          <div class="setting-row">
            <label for="">{$smarty.const.CHOOSE_HEADING_TYPE}</label>
            <select name="setting[0][heading_type]" id="" class="form-control">
              <option value=""{if $settings[0].heading_type == ''} selected{/if}></option>
              <option value="h1"{if $settings[0].heading_type == 'h1'} selected{/if}>h1</option>
              <option value="h2"{if $settings[0].heading_type == 'h2'} selected{/if}>h2</option>
              <option value="h3"{if $settings[0].heading_type == 'h3'} selected{/if}>h3</option>
            </select>
          </div>

          <div class="setting-row">
            <label for="">{$smarty.const.CHOOSE_HEADING_ITEM}</label>
            <select name="setting[0][heading_item]" id="" class="form-control">
              <option value=""{if $settings[0].heading_item == ''} selected{/if}>{$smarty.const.TEXT_BY_QUEUE}</option>
              <option value="1"{if $settings[0].heading_item == '1'} selected{/if}>1{$smarty.const.TEXT_TH}</option>
              <option value="2"{if $settings[0].heading_item == '2'} selected{/if}>2{$smarty.const.TEXT_TH}</option>
              <option value="3"{if $settings[0].heading_item == '3'} selected{/if}>3{$smarty.const.TEXT_TH}</option>
              <option value="4"{if $settings[0].heading_item == '4'} selected{/if}>4{$smarty.const.TEXT_TH}</option>
              <option value="5"{if $settings[0].heading_item == '5'} selected{/if}>5{$smarty.const.TEXT_TH}</option>
              <option value="6"{if $settings[0].heading_item == '6'} selected{/if}>6{$smarty.const.TEXT_TH}</option>
              <option value="7"{if $settings[0].heading_item == '7'} selected{/if}>7{$smarty.const.TEXT_TH}</option>
              <option value="8"{if $settings[0].heading_item == '8'} selected{/if}>8{$smarty.const.TEXT_TH}</option>
              <option value="9"{if $settings[0].heading_item == '9'} selected{/if}>9{$smarty.const.TEXT_TH}</option>
            </select>
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