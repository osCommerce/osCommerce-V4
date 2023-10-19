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


          <div class="setting-row">
            <label for="">{$smarty.const.DATA_TYPE}</label>
            <select name="setting[0][view_item]" id="" class="form-control">
              <option value=""{if $settings[0].view_item == ''} selected{/if}></option>
              <option value="phone_number"{if $settings[0].view_item == 'phone_number'} selected{/if}>{$smarty.const.ENTRY_TELEPHONE_NUMBER}</option>
              <option value="email"{if $settings[0].view_item == 'email'} selected{/if}>{$smarty.const.ENTRY_EMAIL_ADDRESS}</option>
              <option value="address"{if $settings[0].view_item == 'address'} selected{/if}>{$smarty.const.CATEGORY_ADDRESS}</option>
              <option value="name"{if $settings[0].view_item == 'name'} selected{/if}>{$smarty.const.ENTRY_COMPANY}</option>
              <option value="company_no"{if $settings[0].view_item == 'company_no'} selected{/if}>{$smarty.const.ENTRY_BUSINESS_REG_NUMBER}</option>
              <option value="company_vat_id"{if $settings[0].view_item == 'company_vat_id'} selected{/if}>{$smarty.const.ENTRY_BUSINESS}</option>
              <option value="opening_hours"{if $settings[0].view_item == 'opening_hours'} selected{/if}>{$smarty.const.CATEGORY_OPEN_HOURS}</option>
              <option value="data_format"{if $settings[0].view_item == 'data_format'} selected{/if}>Enter data format</option>
            </select>
          </div>

          <div class="setting-row address_spacer" style="display: none">
            <label for="">{$smarty.const.TEXT_SPACER}</label>
            <input type="text" name="setting[0][address_spacer]" value="{$settings[0].address_spacer}" class="form-control" />
          </div>

          <div class="setting-row data_format" style="display: none">
            <label for="">Enter data format</label>
            <textarea name="setting[0][data_format_content]" id="" cols="30" rows="10" class="data-format">{$settings[0].data_format_content}</textarea>
            <a href="#keys-popup" class="btn add-key-btn" data-popup-option="{$tab_data.popupOption}">{$smarty.const.TEXT_TEMPLATES_KEYS_BUTTON}</a>
          </div>

          <div class="setting-row time-format" style="display: none">
            <label for="">{$smarty.const.TEXT_TIME_FORMAT}</label>
            <select name="setting[0][time_format]" id="" class="form-control">
              <option value=""{if $settings[0].time_format == ''} selected{/if}>12</option>
              <option value="24"{if $settings[0].time_format == '24'} selected{/if}>24</option>
            </select>
          </div>

          {*<div class="setting-row seo_tags" style="display: none">
            <label for="">Add 'Microdata'</label>
            <select name="setting[0][seo_tags]" id="" class="form-control">
              <option value=""{if $settings[0].seo_tags == ''} selected{/if}>{$smarty.const.TEXT_NO}</option>
              <option value="24"{if $settings[0].seo_tags == '1'} selected{/if}>{$smarty.const.TEXT_YES}</option>
            </select>
          </div>*}



          <div class="setting-row email-format">
            <label for="">{$smarty.const.USE_AT_IN_EMAIL_ADDRESS}</label>
            <select name="setting[0][use_at_in_email]" id="" class="form-control">
              <option value=""{if $settings[0].use_at_in_email == ''} selected{/if}>{$smarty.const.TEXT_YES}</option>
              <option value="1"{if $settings[0].use_at_in_email == '1'} selected{/if}>{$smarty.const.TEXT_NO}</option>
            </select>
          </div>

          <div class="setting-row email-format">
            <label for="">{$smarty.const.ADD_LINK_ON_EMAIL_ADDRESS}</label>
            <select name="setting[0][add_link_on_email]" id="" class="form-control">
              <option value=""{if $settings[0].add_link_on_email == ''} selected{/if}>{$smarty.const.TEXT_NO}</option>
              <option value="1"{if $settings[0].add_link_on_email == '1'} selected{/if}>{$smarty.const.TEXT_YES}</option>
            </select>
          </div>

          <div class="setting-row phone-format">
            <label for="">{$smarty.const.ADD_LINK_ON_TELEPHONE_NUMBER}</label>
            <select name="setting[0][add_link_on_phone]" id="" class="form-control">
              <option value=""{if $settings[0].add_link_on_phone == ''} selected{/if}>{$smarty.const.TEXT_NO}</option>
              <option value="1"{if $settings[0].add_link_on_phone == '1'} selected{/if}>{$smarty.const.TEXT_YES}</option>
            </select>
          </div>

          <script type="text/javascript">
              (function($){
                  $(function(){
                      $('select[name="setting[0][view_item]"]').on('change', function(){
                          if ($(this).val() === 'opening_hours'){
                              $('.time-format').show()
                          } else {
                              $('.time-format').hide();
                          }
                          if ($(this).val() === 'address'){
                              $('.address_spacer').show();
                          } else {
                              $('.address_spacer').hide()
                          }
                          if ($(this).val() === 'data_format'){
                              $('.data_format').show();
                              $('.time-format').show();
                              $('.seo_tags').show()
                          } else {
                              $('.data_format').hide();
                              $('.time-format').hide()
                              $('.seo_tags').hide();
                          }
                          if ($(this).val() === 'address' || $(this).val() === 'email' || $(this).val() === 'data_format'){
                              $('.email-format').show();
                          } else {
                              $('.email-format').hide();
                          }
                          if ($(this).val() === 'address' || $(this).val() === 'phone_number' || $(this).val() === 'data_format'){
                              $('.phone-format').show()
                          } else {
                              $('.phone-format').hide()
                          }
                      }).trigger('change')
                  })
              })(jQuery)
          </script>



          {*include 'include/ajax.tpl'*}
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

<script type="text/javascript">
    window.currentInput = 0;
    window.currentValue = '';
    window.currentPosition = 0;

    $('.data-format').on('focus keyup click', function(){
        window.currentInput = $(this);
        window.currentValue = $(this).val();
        window.currentPosition = this.selectionStart;
    });

    $('.add-key-btn')
        .popUp({ one_popup: false})
        .on('click', function(){
            if (!window.currentInput) {
                window.currentInput = $('.data-format');
                window.currentValue = window.currentInput.val();
            }
        });
</script>

<div id="keys-popup" style="display: none">
  <div class="popup-heading cat-head">{$smarty.const.TEXT_TEMPLATES_KEYS}</div>
  <div class="pop-up-content">
    <div class="pageLinksWrapper">
      <select name="key" class="form-control js-popup-keys">
        <option value=""></option>
        <option value="##OWNER##">Platform Owner</option>
        <option value="##TITLE##">Platform name</option>
        <option value="##EMAIL_ADDRESS##">E-Mail Address</option>
        <option value="##EMAIL_EXTRA##">Extra Order Emails</option>
        <option value="##TELEPHONE##">Phone number</option>
        <option value="##LANDLINE##">Landline</option>
        <option value="##COMPANY##">Company Name</option>
        <option value="##COMPANY_VAT##">Company VAT-ID</option>
        <option value="##POSTCODE##">Post Code</option>
        <option value="##STREET_ADDRESS##">Street Address</option>
        <option value="##SUBURB##">Suburb</option>
        <option value="##CITY##">Town/City</option>
        <option value="##STATE##">State/Province</option>
        <option value="##REG_NUMBER##">Company No</option>
        <option value="##COUNTRY##">Country</option>
        <option value="##OPEN##">Opening Hours</option>
        <option value="##POST_ADDRESS##">Post Address</option>
      </select>
    </div>
    <div class="pageLinksButton">
      <button class="btn btn-no-margin btn-insert-key">{$smarty.const.IMAGE_INSERT}</button>
    </div>
  </div>
  <script type="text/javascript">
      $(function(){
          $('.btn-insert-key:visible').on('click', function(){
              var key = $('.pageLinksWrapper select:visible').val();
              var input = window.currentInput;
              var inputValue = window.currentValue;
              var cursorPosition = window.currentPosition;

              input.val(inputValue.substr(0, cursorPosition) + key + inputValue.substr(cursorPosition));
              input.trigger('change')

              $('.popup-box-wrap:last').remove()
          })
      })
  </script>
</div>