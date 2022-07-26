
  <div class="contact-info form-inputs">
    <div class="heading-4">{$smarty.const.CONTACT_INFORMATION}</div>
    <div class="col-full">
      <label>
        <span>{field_label const="ENTRY_EMAIL_ADDRESS" required_text="*"}</span>
        <input type="email" name="email_address" value="{$email_address|escape:'html'}" data-required="{$smarty.const.EMAIL_REQUIRED}" data-pattern="email"/>
      </label>
    </div>
    <div class="columns">
{if in_array(ACCOUNT_TELEPHONE, ['required', 'required_register', 'visible', 'visible_register'])}
      <div class="col-2">
        <label>
          <span>{field_label const="ENTRY_TELEPHONE_NUMBER" configuration="ACCOUNT_TELEPHONE"}</span>
          {if in_array(ACCOUNT_TELEPHONE, ['required', 'required_register'])}
          <input type="text" name="telephone" value="{$telephone|escape:'html'}" data-pattern="{$re1}{$smarty.const.ENTRY_TELEPHONE_MIN_LENGTH}{$re2}" data-required="{sprintf($smarty.const.ENTRY_TELEPHONE_NUMBER_ERROR, $smarty.const.ENTRY_TELEPHONE_MIN_LENGTH)}"/>
          {else}
          <input type="text" name="telephone" value="{$telephone|escape:'html'}"/>
          {/if}
        </label>
      </div>
{/if}
{if in_array(ACCOUNT_LANDLINE, ['required', 'required_register', 'visible', 'visible_register'])}
      <div class="col-2">
        <label>
          <span>{field_label const="ENTRY_LANDLINE" configuration="ACCOUNT_LANDLINE"}</span>
          {if in_array(ACCOUNT_LANDLINE, ['required', 'required_register'])}
          <input type="text" name="landline" value="{$landline|escape:'html'}" data-pattern="{$re1}{$smarty.const.ENTRY_LANDLINE_MIN_LENGTH}{$re2}" data-required="{sprintf($smarty.const.ENTRY_LANDLINE_NUMBER_ERROR, $smarty.const.ENTRY_LANDLINE_MIN_LENGTH)}"/>
          {else}
          <input type="text" name="landline" value="{$landline|escape:'html'}"/>
          {/if}
        </label>
      </div>
{/if}
{if in_array(ACCOUNT_COMPANY, ['required', 'required_register', 'visible', 'visible_register'])}
          <div class="col-2">
            <label>
              <span>{field_label const="ENTRY_COMPANY" configuration="ACCOUNT_COMPANY"}</span>
            {if in_array(ACCOUNT_COMPANY, ['required', 'required_register'])}
            <input type="text" name="customer_company" value="{$customer_company|escape:'html'}" data-pattern="{$re1}1{$re2}" data-required="{$smarty.const.ENTRY_COMPANY_ERROR}"/>
            {else}
            <input type="text" name="customer_company" value="{$customer_company|escape:'html'}"/>
            {/if}
            </label>
          </div>
{/if}
{if in_array(ACCOUNT_COMPANY_VAT, ['required', 'required_register', 'visible', 'visible_register'])}
        <div class="col-2 company_vat_box">
            <label for="customer_company_vat">{field_label const="ENTRY_BUSINESS" configuration="ACCOUNT_COMPANY_VAT"}</label>
            {if in_array(ACCOUNT_COMPANY_VAT, ['required', 'required_register'])}
            <input id="customer_company_vat" type="text" name="customer_company_vat" value="{$customer_company_vat|escape:'html'}" data-pattern="{$re1}1{$re2}" data-required="{$smarty.const.ENTRY_VAT_ID_ERROR}"/><span id="customer_company_vat_status"></span>
            {else}
            <input id="customer_company_vat" type="text" name="customer_company_vat" value="{$customer_company_vat|escape:'html'}"/><span id="customer_company_vat_status"></span>
            {/if}
        </div>
{/if}
{if in_array(ACCOUNT_CUSTOMS_NUMBER, ['required', 'required_register', 'visible', 'visible_register', 'required_company'])}
        <div class="col-2 customs_number_box">
            <label for="customer_customs_number">{field_label const="TEXT_CUSTOMS_NUMBER" configuration="ACCOUNT_CUSTOMS_NUMBER"}</label>
            {if in_array(ACCOUNT_CUSTOMS_NUMBER, ['required', 'required_register'])}
            <input id="customer_customs_number" type="text" name="customer_customs_number" value="{$customer_customs_number|escape:'html'}" data-pattern="{$re1}1{$re2}" data-required="{$smarty.const.TEXT_CUSTOMS_NUMBER_ERROR}"/><span id="customer_customs_number_status"></span>
            {else}
            <input id="customer_customs_number" type="text" name="customer_customs_number" value="{$customer_customs_number|escape:'html'}"/><span id="customer_customs_number_status"></span>
            {/if}
        </div>
{/if}
    </div>
  </div>