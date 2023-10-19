{use class="Yii"}
<form action="{$app->request->baseUrl}/design/box-save" method="post" id="box-save">
  <input type="hidden" name="id" value="{$id}"/>
  <div class="popup-heading">
    {$smarty.const.TEXT_CONTACTS}
  </div>
  <div class="popup-content box-img">




    <div class="tabbable tabbable-custom">
      <ul class="nav nav-tabs">

        <li class="active" data-bs-toggle="tab" data-bs-target="#type"><a>{$smarty.const.TEXT_CONTACTS}</a></li>
        <li data-bs-toggle="tab" data-bs-target="#style"><a>{$smarty.const.HEADING_STYLE}</a></li>
        <li data-bs-toggle="tab" data-bs-target="#align"><a>{$smarty.const.HEADING_WIDGET_ALIGN}</a></li>
        <li data-bs-toggle="tab" data-bs-target="#visibility"><a>{$smarty.const.TEXT_VISIBILITY_ON_PAGES}</a></li>

      </ul>
      <div class="tab-content">
        <div class="tab-pane active menu-list" id="type">


          <p><label><input type="checkbox" name="setting[0][show_icons]"{if $settings[0].show_icons} checked{/if}/> {$smarty.const.TEXT_SHOW_ICONS}</label></p>

          <div class="setting-row">
            <label for="">{$smarty.const.USE_AT_IN_EMAIL_ADDRESS}</label>
            <select name="setting[0][use_at_in_email]" id="" class="form-control">
              <option value=""{if $settings[0].use_at_in_email == ''} selected{/if}>{$smarty.const.TEXT_YES}</option>
              <option value="1"{if $settings[0].use_at_in_email == '1'} selected{/if}>{$smarty.const.TEXT_NO}</option>
            </select>
          </div>

          <div class="setting-row">
            <label for="">{$smarty.const.ADD_LINK_ON_EMAIL_ADDRESS}</label>
            <select name="setting[0][add_link_on_email]" id="" class="form-control">
              <option value=""{if $settings[0].add_link_on_email == ''} selected{/if}>{$smarty.const.TEXT_NO}</option>
              <option value="1"{if $settings[0].add_link_on_email == '1'} selected{/if}>{$smarty.const.TEXT_YES}</option>
            </select>
          </div>

          <div class="setting-row">
            <label for="">{$smarty.const.ADD_LINK_ON_TELEPHONE_NUMBER}</label>
            <select name="setting[0][add_link_on_phone]" id="" class="form-control">
              <option value=""{if $settings[0].add_link_on_phone == ''} selected{/if}>{$smarty.const.TEXT_NO}</option>
              <option value="1"{if $settings[0].add_link_on_phone == '1'} selected{/if}>{$smarty.const.TEXT_YES}</option>
            </select>
          </div>

          <div class="setting-row">
            <label for="">{$smarty.const.ENTRY_LANDLINE}</label>
            <select name="setting[0][show_landline]" id="" class="form-control">
              <option value=""{if $settings[0].show_landline == ''} selected{/if}>{$smarty.const.TEXT_NO}</option>
              <option value="1"{if $settings[0].show_landline == '1'} selected{/if}>{$smarty.const.TEXT_YES}</option>
            </select>
          </div>

          <div class="setting-row">
            <label for="">{$smarty.const.TEXT_TIME_FORMAT}</label>
            <select name="setting[0][time_format]" id="" class="form-control">
              <option value=""{if $settings[0].time_format == ''} selected{/if}>12</option>
              <option value="24"{if $settings[0].time_format == '24'} selected{/if}>24</option>
            </select>
          </div>


          <div class="setting-row">
            <label for="">Tag for company name</label>
            <select name="setting[0][tag_company]" id="" class="form-control">
              <option value=""{if $settings[0].tag_company == ''} selected{/if}>default</option>
              <option value="h1"{if $settings[0].tag_company == 'h1'} selected{/if}>h1</option>
              <option value="h2"{if $settings[0].tag_company == 'h2'} selected{/if}>h2</option>
              <option value="h3"{if $settings[0].tag_company == 'h3'} selected{/if}>h3</option>
              <option value="h4"{if $settings[0].tag_company == 'h4'} selected{/if}>h4</option>
            </select>
          </div>
          <div class="setting-row">
            <label for="">Tag for street address</label>
            <select name="setting[0][tag_street_address]" id="" class="form-control">
              <option value=""{if $settings[0].tag_street_address == ''} selected{/if}>default</option>
              <option value="h1"{if $settings[0].tag_street_address == 'h1'} selected{/if}>h1</option>
              <option value="h2"{if $settings[0].tag_street_address == 'h2'} selected{/if}>h2</option>
              <option value="h3"{if $settings[0].tag_street_address == 'h3'} selected{/if}>h3</option>
              <option value="h4"{if $settings[0].tag_street_address == 'h4'} selected{/if}>h4</option>
            </select>
          </div>
          <div class="setting-row">
            <label for="">Tag for city</label>
            <select name="setting[0][tag_city]" id="" class="form-control">
              <option value=""{if $settings[0].tag_city == ''} selected{/if}>default</option>
              <option value="h1"{if $settings[0].tag_city == 'h1'} selected{/if}>h1</option>
              <option value="h2"{if $settings[0].tag_city == 'h2'} selected{/if}>h2</option>
              <option value="h3"{if $settings[0].tag_city == 'h3'} selected{/if}>h3</option>
              <option value="h4"{if $settings[0].tag_city == 'h4'} selected{/if}>h4</option>
            </select>
          </div>
          <div class="setting-row">
            <label for="">Tag for state</label>
            <select name="setting[0][tag_state]" id="" class="form-control">
              <option value=""{if $settings[0].tag_state == ''} selected{/if}>default</option>
              <option value="h1"{if $settings[0].tag_state == 'h1'} selected{/if}>h1</option>
              <option value="h2"{if $settings[0].tag_state == 'h2'} selected{/if}>h2</option>
              <option value="h3"{if $settings[0].tag_state == 'h3'} selected{/if}>h3</option>
              <option value="h4"{if $settings[0].tag_state == 'h4'} selected{/if}>h4</option>
            </select>
          </div>
          <div class="setting-row">
            <label for="">Tag for suburb</label>
            <select name="setting[0][tag_suburb]" id="" class="form-control">
              <option value=""{if $settings[0].tag_suburb == ''} selected{/if}>default</option>
              <option value="h1"{if $settings[0].tag_suburb == 'h1'} selected{/if}>h1</option>
              <option value="h2"{if $settings[0].tag_suburb == 'h2'} selected{/if}>h2</option>
              <option value="h3"{if $settings[0].tag_suburb == 'h3'} selected{/if}>h3</option>
              <option value="h4"{if $settings[0].tag_suburb == 'h4'} selected{/if}>h4</option>
            </select>
          </div>
          <div class="setting-row">
            <label for="">Tag for post code</label>
            <select name="setting[0][tag_post_code]" id="" class="form-control">
              <option value=""{if $settings[0].tag_post_code == ''} selected{/if}>default</option>
              <option value="h1"{if $settings[0].tag_post_code == 'h1'} selected{/if}>h1</option>
              <option value="h2"{if $settings[0].tag_post_code == 'h2'} selected{/if}>h2</option>
              <option value="h3"{if $settings[0].tag_post_code == 'h3'} selected{/if}>h3</option>
              <option value="h4"{if $settings[0].tag_post_code == 'h4'} selected{/if}>h4</option>
            </select>
          </div>
          <div class="setting-row">
            <label for="">Tag for post country</label>
            <select name="setting[0][tag_country]" id="" class="form-control">
              <option value=""{if $settings[0].tag_country == ''} selected{/if}>default</option>
              <option value="h1"{if $settings[0].tag_country == 'h1'} selected{/if}>h1</option>
              <option value="h2"{if $settings[0].tag_country == 'h2'} selected{/if}>h2</option>
              <option value="h3"{if $settings[0].tag_country == 'h3'} selected{/if}>h3</option>
              <option value="h4"{if $settings[0].tag_country == 'h4'} selected{/if}>h4</option>
            </select>
          </div>
          <div class="setting-row">
            <label for="">Tag for phone label</label>
            <select name="setting[0][tag_phone_label]" id="" class="form-control">
              <option value=""{if $settings[0].tag_phone_label == ''} selected{/if}>default</option>
              <option value="h1"{if $settings[0].tag_phone_label == 'h1'} selected{/if}>h1</option>
              <option value="h2"{if $settings[0].tag_phone_label == 'h2'} selected{/if}>h2</option>
              <option value="h3"{if $settings[0].tag_phone_label == 'h3'} selected{/if}>h3</option>
              <option value="h4"{if $settings[0].tag_phone_label == 'h4'} selected{/if}>h4</option>
            </select>
          </div>
          <div class="setting-row">
            <label for="">Tag for phone</label>
            <select name="setting[0][tag_phone]" id="" class="form-control">
              <option value=""{if $settings[0].tag_phone == ''} selected{/if}>default</option>
              <option value="h1"{if $settings[0].tag_phone == 'h1'} selected{/if}>h1</option>
              <option value="h2"{if $settings[0].tag_phone == 'h2'} selected{/if}>h2</option>
              <option value="h3"{if $settings[0].tag_phone == 'h3'} selected{/if}>h3</option>
              <option value="h4"{if $settings[0].tag_phone == 'h4'} selected{/if}>h4</option>
            </select>
          </div>
          <div class="setting-row">
            <label for="">Tag for email label</label>
            <select name="setting[0][tag_email_label]" id="" class="form-control">
              <option value=""{if $settings[0].tag_email_label == ''} selected{/if}>default</option>
              <option value="h1"{if $settings[0].tag_email_label == 'h1'} selected{/if}>h1</option>
              <option value="h2"{if $settings[0].tag_email_label == 'h2'} selected{/if}>h2</option>
              <option value="h3"{if $settings[0].tag_email_label == 'h3'} selected{/if}>h3</option>
              <option value="h4"{if $settings[0].tag_email_label == 'h4'} selected{/if}>h4</option>
            </select>
          </div>
          <div class="setting-row">
            <label for="">Tag for email</label>
            <select name="setting[0][tag_email]" id="" class="form-control">
              <option value=""{if $settings[0].tag_email == ''} selected{/if}>default</option>
              <option value="h1"{if $settings[0].tag_email == 'h1'} selected{/if}>h1</option>
              <option value="h2"{if $settings[0].tag_email == 'h2'} selected{/if}>h2</option>
              <option value="h3"{if $settings[0].tag_email == 'h3'} selected{/if}>h3</option>
              <option value="h4"{if $settings[0].tag_email == 'h4'} selected{/if}>h4</option>
            </select>
          </div>
          <div class="setting-row">
            <label for="">Tag for Company No label</label>
            <select name="setting[0][tag_company_no_label]" id="" class="form-control">
              <option value=""{if $settings[0].tag_company_no_label == ''} selected{/if}>default</option>
              <option value="h1"{if $settings[0].tag_company_no_label == 'h1'} selected{/if}>h1</option>
              <option value="h2"{if $settings[0].tag_company_no_label == 'h2'} selected{/if}>h2</option>
              <option value="h3"{if $settings[0].tag_company_no_label == 'h3'} selected{/if}>h3</option>
              <option value="h4"{if $settings[0].tag_company_no_label == 'h4'} selected{/if}>h4</option>
            </select>
          </div>
          <div class="setting-row">
            <label for="">Tag for Company No</label>
            <select name="setting[0][tag_company_no]" id="" class="form-control">
              <option value=""{if $settings[0].tag_company_no == ''} selected{/if}>default</option>
              <option value="h1"{if $settings[0].tag_company_no == 'h1'} selected{/if}>h1</option>
              <option value="h2"{if $settings[0].tag_company_no == 'h2'} selected{/if}>h2</option>
              <option value="h3"{if $settings[0].tag_company_no == 'h3'} selected{/if}>h3</option>
              <option value="h4"{if $settings[0].tag_company_no == 'h4'} selected{/if}>h4</option>
            </select>
          </div>
          <div class="setting-row">
            <label for="">Tag for Company Vat label</label>
            <select name="setting[0][tag_company_vat_label]" id="" class="form-control">
              <option value=""{if $settings[0].tag_company_vat_label == ''} selected{/if}>default</option>
              <option value="h1"{if $settings[0].tag_company_vat_label == 'h1'} selected{/if}>h1</option>
              <option value="h2"{if $settings[0].tag_company_vat_label == 'h2'} selected{/if}>h2</option>
              <option value="h3"{if $settings[0].tag_company_vat_label == 'h3'} selected{/if}>h3</option>
              <option value="h4"{if $settings[0].tag_company_vat_label == 'h4'} selected{/if}>h4</option>
            </select>
          </div>
          <div class="setting-row">
            <label for="">Tag for Company No</label>
            <select name="setting[0][tag_company_vat]" id="" class="form-control">
              <option value=""{if $settings[0].tag_company_vat == ''} selected{/if}>default</option>
              <option value="h1"{if $settings[0].tag_company_vat == 'h1'} selected{/if}>h1</option>
              <option value="h2"{if $settings[0].tag_company_vat == 'h2'} selected{/if}>h2</option>
              <option value="h3"{if $settings[0].tag_company_vat == 'h3'} selected{/if}>h3</option>
              <option value="h4"{if $settings[0].tag_company_vat == 'h4'} selected{/if}>h4</option>
            </select>
          </div>
          <div class="setting-row">
            <label for="">Tag for Opening hours label</label>
            <select name="setting[0][tag_opening_hours_label]" id="" class="form-control">
              <option value=""{if $settings[0].tag_opening_hours_label == ''} selected{/if}>default</option>
              <option value="h1"{if $settings[0].tag_opening_hours_label == 'h1'} selected{/if}>h1</option>
              <option value="h2"{if $settings[0].tag_opening_hours_label == 'h2'} selected{/if}>h2</option>
              <option value="h3"{if $settings[0].tag_opening_hours_label == 'h3'} selected{/if}>h3</option>
              <option value="h4"{if $settings[0].tag_opening_hours_label == 'h4'} selected{/if}>h4</option>
            </select>
          </div>
          <div class="setting-row">
            <label for="">Tag for Opening hours</label>
            <select name="setting[0][tag_opening_hours]" id="" class="form-control">
              <option value=""{if $settings[0].tag_opening_hours == ''} selected{/if}>default</option>
              <option value="h1"{if $settings[0].tag_opening_hours == 'h1'} selected{/if}>h1</option>
              <option value="h2"{if $settings[0].tag_opening_hours == 'h2'} selected{/if}>h2</option>
              <option value="h3"{if $settings[0].tag_opening_hours == 'h3'} selected{/if}>h3</option>
              <option value="h4"{if $settings[0].tag_opening_hours == 'h4'} selected{/if}>h4</option>
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
    <script type="text/javascript">
      $('.btn-cancel').on('click', function(){
        $('.popup-box-wrap').remove()
      })
    </script>

  </div>
</form>