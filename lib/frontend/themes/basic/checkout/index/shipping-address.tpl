
    <div class="shipping-address form-inputs" id="shipping_address">
      <div class="heading-4">{$smarty.const.SHIPPING_ADDRESS}</div>

    {if $smarty.const.BILLING_FIRST=='True'}
    <div class="hide-shipping-address"></div>
    <div class="same-address">{$smarty.const.SAME_AS_BILLING} <input type="checkbox" name="bill_as_ship" id="as-billing"{if !$bill_not_ship} checked {/if}/></div>
    {/if}

      {if $addresses_array}
        {if $wExt = \common\helpers\Acl::checkExtensionAllowed('WeddingRegistry', 'allowed')}
            {$wExt::renderCheckoutShippings($addresses_array, $ship_address_book_id)}
        {else}
        <div class="addresses" id="shipping-addresses">
          {foreach $addresses_array as $addresse}
            <div class="address-item">
              <label>
                <input type="radio" name="sendto" value="{$addresse.id}"{if $ship_address_book_id == $addresse.id} checked{/if}/>
                <span>{$addresse.text}</span>
              </label>
            </div>
          {/foreach}

          <div class="address-item">
            <label>
              <input type="radio" name="sendto" value=""{if $ship_address_book_id == ''} checked{/if}/>
              <span>{$smarty.const.NEW_SHIPPING_ADDRESS}</span>
            </label>
          </div>
        </div>
        {/if}
      {/if}


      <div id="shipping-address">
{if in_array(ACCOUNT_GENDER, ['required', 'required_register', 'visible', 'visible_register'])}
          <div class="col-full genders-title">
            <div class="">{field_label const="ENTRY_GENDER" configuration="ACCOUNT_GENDER"}</div>
            <label><input type="radio" name="shipping_gender" value="m"{if $ship_gender == 'm'} checked{/if}/> <span>{\common\helpers\Address::getGenderName($ship_gender)}</span></label>
            <label><input type="radio" name="shipping_gender" value="f"{if $ship_gender == 'f'} checked{/if}/> <span>{\common\helpers\Address::getGenderName($ship_gender)}</span></label>
            <label><input type="radio" name="shipping_gender" value="s"{if $ship_gender == 's'} checked{/if}/> <span>{\common\helpers\Address::getGenderName($ship_gender)}</span></label>
          </div>
{/if}
        <div class="columns">
{if in_array(ACCOUNT_FIRSTNAME, ['required', 'required_register', 'visible', 'visible_register'])}
          <div class="col-2">
            <label>
              <span>{field_label const="ENTRY_FIRST_NAME" configuration="ACCOUNT_FIRSTNAME"}</span>
            {if in_array(ACCOUNT_FIRSTNAME, ['required', 'required_register'])}
              <input type="text" name="ship_firstname" value="{$ship_firstname|escape:'html'}" data-pattern="{$re1}{$smarty.const.ENTRY_FIRST_NAME_MIN_LENGTH}{$re2}" data-required="{sprintf($smarty.const.ENTRY_FIRST_NAME_ERROR, $smarty.const.ENTRY_FIRST_NAME_MIN_LENGTH)}"/>
            {else}
              <input type="text" name="ship_firstname" value="{$ship_firstname|escape:'html'}"/>
            {/if}
            </label>
          </div>
{/if}
{if in_array(ACCOUNT_LASTNAME, ['required', 'required_register', 'visible', 'visible_register'])}
          <div class="col-2">
            <label>
              <span>{field_label const="ENTRY_LAST_NAME" configuration="ACCOUNT_LASTNAME"}</span>
            {if in_array(ACCOUNT_LASTNAME, ['required', 'required_register'])}
              <input type="text" name="ship_lastname" value="{$ship_lastname|escape:'html'}" data-pattern="{$re1}{$smarty.const.ENTRY_LAST_NAME_MIN_LENGTH}{$re2}" data-required="{sprintf($smarty.const.ENTRY_LAST_NAME_ERROR, $smarty.const.ENTRY_LAST_NAME_MIN_LENGTH)}"/>
            {else}
              <input type="text" name="ship_lastname" value="{$ship_lastname|escape:'html'}"/>
            {/if}
            </label>
          </div>
{/if}
{if in_array(ACCOUNT_STREET_ADDRESS, ['required', 'required_register', 'visible', 'visible_register'])}
          <div class="col-2">
            <label>
              <span>{field_label const="ENTRY_STREET_ADDRESS" configuration="ACCOUNT_STREET_ADDRESS"}</span>
            {if in_array(ACCOUNT_STREET_ADDRESS, ['required', 'required_register'])}
              <input type="text" name="ship_street_address_line1" value="{$ship_street_address|escape:'html'}" data-pattern="{$re1}{$smarty.const.ENTRY_STREET_ADDRESS_MIN_LENGTH}{$re2}" data-required="{$smarty.const.ENTRY_STREET_ADDRESS_ERROR}"/>
            {else}
              <input type="text" name="ship_street_address_line1" value="{$ship_street_address|escape:'html'}"/>
            {/if}
            </label>
          </div>
{/if}
{if in_array(ACCOUNT_SUBURB, ['required', 'required_register', 'visible', 'visible_register'])}
            <div class="col-2">
              <label>
                <span>{field_label const="ENTRY_SUBURB" configuration="ACCOUNT_SUBURB"}</span>
            {if in_array(ACCOUNT_SUBURB, ['required', 'required_register'])}
                <input type="text" name="ship_street_address_line2" value="{$ship_suburb|escape:'html'}" data-pattern="{$re1}1{$re2}" data-required="{$smarty.const.ENTRY_SUBURB_ERROR}"/>
            {else}
                <input type="text" name="ship_street_address_line2" value="{$ship_suburb|escape:'html'}"/>
            {/if}
              </label>
            </div>
{/if}
{if in_array(ACCOUNT_POSTCODE, ['required', 'required_register', 'visible', 'visible_register'])}
          <div class="col-2">
            <label>
              <span>{field_label const="ENTRY_POST_CODE" configuration="ACCOUNT_POSTCODE"}</span>
            {if in_array(ACCOUNT_POSTCODE, ['required', 'required_register'])}
              <input type="text" name="ship_postcode" value="{$ship_postcode|escape:'html'}" data-pattern="{$re1}{$smarty.const.ENTRY_POSTCODE_MIN_LENGTH}{$re2}" data-required="{$smarty.const.ENTRY_POST_CODE_ERROR}"/>
            {else}
              <input type="text" name="ship_postcode" value="{$ship_postcode|escape:'html'}"/>
            {/if}
            </label>
          </div>
{/if}
{if in_array(ACCOUNT_CITY, ['required', 'required_register', 'visible', 'visible_register'])}
          <div class="col-2">
            <label>
              <span>{field_label const="ENTRY_CITY" configuration="ACCOUNT_CITY"}</span>
            {if in_array(ACCOUNT_CITY, ['required', 'required_register'])}
              <input type="text" name="ship_city" value="{$ship_city|escape:'html'}" data-pattern="{$re1}{$smarty.const.ENTRY_CITY_MIN_LENGTH}{$re2}" data-required="{$smarty.const.ENTRY_CITY_ERROR}"/>
            {else}
              <input type="text" name="ship_city" value="{$ship_city|escape:'html'}"/>
            {/if}
            </label>
          </div>
{/if}
{if in_array(ACCOUNT_STATE, ['required', 'required_register', 'visible', 'visible_register'])}
            <div class="col-2">
              <label>
                <span>{field_label const="ENTRY_STATE" configuration="ACCOUNT_STATE"}</span>
                {if $entry_state_has_zones}
                  <select name="ship_state">
                    {foreach $zones_array as $zone}
                      <option value="{$zone.id}"{if $ship_state == $zone.id} selected{/if}>{$zone.text}</option>
                    {/foreach}
                  </select>
                {else}
                {if in_array(ACCOUNT_STATE, ['required', 'required_register'])}
                  <input type="text" name="ship_state" value="{$ship_state|escape:'html'}" data-pattern="{$re1}{$smarty.const.ENTRY_STATE_MIN_LENGTH}{$re2}" data-required="{$smarty.const.ENTRY_STATE_ERROR}"/>
                {else}
                  <input type="text" name="ship_state" value="{$ship_state|escape:'html'}"/>
                {/if}
                {/if}
              </label>
            </div>
{/if}
{if in_array(ACCOUNT_COUNTRY, ['required', 'required_register', 'visible', 'visible_register'])}
          <div class="col-2">
            <label>
              <span>{field_label const="ENTRY_COUNTRY" configuration="ACCOUNT_COUNTRY"}</span>
              <select name="ship_country" data-required="{$smarty.const.ENTRY_COUNTRY_ERROR}">
                {foreach $ship_countries as $country}
                  <option value="{$country.countries_id}"{if $country.countries_id == $ship_country} selected{/if}>{$country.countries_name}</option>
                {/foreach}
              </select>
            </label>
          </div>
{/if}
        </div>
      </div>
    </div>