
    <div class="shipping-address form-inputs">


        {if $addresses_array != ''}
            <div class="addresses" id="billing-addresses">
                {foreach $addresses_array as $addresse}
                    <div class="address-item">
                        <label>
                            <input type="radio" name="billto" value="{$addresse.id}"{if $billing_address_book_id == $addresse.id} checked{/if}/>
                            <span>{$addresse.text}</span>
                        </label>
                    </div>
                {/foreach}

                <div class="address-item">
                    <label>
                        <input type="radio" name="billto" value=""{if $billing_address_book_id == ''} checked{/if}/>
                        <span>{$smarty.const.NEW_BILLING_ADDRESS}</span>
                    </label>
                </div>
            </div>
        {/if}

        {assign var=re1 value='.{'}
        {assign var=re2 value='}'}

        <div id="billing-address">
            {if in_array(ACCOUNT_GENDER, ['required', 'required_register', 'visible', 'visible_register'])}
                <div class="col-full genders-title">
                    <div class="">{field_label const="ENTRY_GENDER" configuration="ACCOUNT_GENDER"}</div>
                    <label><input type="radio" name="gender" value="m"{if $billing_gender == 'm'} checked{/if}/> <span>{$smarty.const.MR}</span></label>
                    <label><input type="radio" name="gender" value="f"{if $billing_gender == 'f'} checked{/if}/> <span>{$smarty.const.MRS}</span></label>
                    <label><input type="radio" name="gender" value="s"{if $billing_gender == 's'} checked{/if}/> <span>{$smarty.const.MISS}</span></label>
                </div>
            {/if}

            <div class="columns">
                {if in_array(ACCOUNT_FIRSTNAME, ['required', 'required_register', 'visible', 'visible_register'])}
                    <div class="col-2">
                        <label>
                            <span>{field_label const="ENTRY_FIRST_NAME" configuration="ACCOUNT_FIRSTNAME"}</span>
                            {if in_array(ACCOUNT_FIRSTNAME, ['required', 'required_register'])}
                                <input type="text" name="firstname" value="{$billing_firstname|escape:'html'}" data-pattern="{$re1}{$smarty.const.ENTRY_FIRST_NAME_MIN_LENGTH}{$re2}" data-required="{sprintf($smarty.const.ENTRY_FIRST_NAME_ERROR, $smarty.const.ENTRY_FIRST_NAME_MIN_LENGTH)}"/>
                            {else}
                                <input type="text" name="firstname" value="{$billing_firstname|escape:'html'}"/>
                            {/if}
                        </label>
                    </div>
                {/if}
                {if in_array(ACCOUNT_LASTNAME, ['required', 'required_register', 'visible', 'visible_register'])}
                    <div class="col-2">
                        <label>
                            <span>{field_label const="ENTRY_LAST_NAME" configuration="ACCOUNT_LASTNAME"}</span>
                            {if in_array(ACCOUNT_LASTNAME, ['required', 'required_register'])}
                                <input type="text" name="lastname" value="{$billing_lastname|escape:'html'}" data-pattern="{$re1}{$smarty.const.ENTRY_LAST_NAME_MIN_LENGTH}{$re2}" data-required="{sprintf($smarty.const.ENTRY_LAST_NAME_ERROR, $smarty.const.ENTRY_LAST_NAME_MIN_LENGTH)}"/>
                            {else}
                                <input type="text" name="lastname" value="{$billing_lastname|escape:'html'}"/>
                            {/if}
                        </label>
                    </div>
                {/if}
                {if in_array(ACCOUNT_STREET_ADDRESS, ['required', 'required_register', 'visible', 'visible_register'])}
                    <div class="col-2">
                        <label>
                            <span>{field_label const="ENTRY_STREET_ADDRESS" configuration="ACCOUNT_STREET_ADDRESS"}</span>
                            {if in_array(ACCOUNT_STREET_ADDRESS, ['required', 'required_register'])}
                                <input type="text" name="street_address_line1" value="{$billing_street_address|escape:'html'}" data-pattern="{$re1}{$smarty.const.ENTRY_STREET_ADDRESS_MIN_LENGTH}{$re2}" data-required="{$smarty.const.ENTRY_STREET_ADDRESS_ERROR}"/>
                            {else}
                                <input type="text" name="street_address_line1" value="{$billing_street_address|escape:'html'}"/>
                            {/if}
                        </label>
                    </div>
                {/if}
                {if in_array(ACCOUNT_SUBURB, ['required', 'required_register', 'visible', 'visible_register'])}
                    <div class="col-2">
                        <label>
                            <span>{field_label const="ENTRY_SUBURB" configuration="ACCOUNT_SUBURB"}</span>
                            {if in_array(ACCOUNT_SUBURB, ['required', 'required_register'])}
                                <input type="text" name="street_address_line2" value="{$billing_suburb|escape:'html'}" data-pattern="{$re1}1{$re2}" data-required="{$smarty.const.ENTRY_SUBURB_ERROR}"/>
                            {else}
                                <input type="text" name="street_address_line2" value="{$billing_suburb|escape:'html'}"/>
                            {/if}
                        </label>
                    </div>
                {/if}
                {if in_array(ACCOUNT_POSTCODE, ['required', 'required_register', 'visible', 'visible_register'])}
                    <div class="col-2">
                        <label>
                            <span>{field_label const="ENTRY_POST_CODE" configuration="ACCOUNT_POSTCODE"}</span>
                            {if in_array(ACCOUNT_POSTCODE, ['required', 'required_register'])}
                                <input type="text" name="postcode" value="{$billing_postcode|escape:'html'}" data-pattern="{$re1}{$smarty.const.ENTRY_POSTCODE_MIN_LENGTH}{$re2}" data-required="{$smarty.const.ENTRY_POST_CODE_ERROR}"/>
                            {else}
                                <input type="text" name="postcode" value="{$billing_postcode|escape:'html'}"/>
                            {/if}
                        </label>
                    </div>
                {/if}
                {if in_array(ACCOUNT_CITY, ['required', 'required_register', 'visible', 'visible_register'])}
                    <div class="col-2">
                        <label>
                            <span>{field_label const="ENTRY_CITY" configuration="ACCOUNT_CITY"}</span>
                            {if in_array(ACCOUNT_CITY, ['required', 'required_register'])}
                                <input type="text" name="city" value="{$billing_city|escape:'html'}" data-pattern="{$re1}{$smarty.const.ENTRY_CITY_MIN_LENGTH}{$re2}" data-required="{$smarty.const.ENTRY_CITY_ERROR}"/>
                            {else}
                                <input type="text" name="city" value="{$billing_city|escape:'html'}"/>
                            {/if}
                        </label>
                    </div>
                {/if}
                {if in_array(ACCOUNT_STATE, ['required', 'required_register', 'visible', 'visible_register'])}
                    <div class="col-2">
                        <label>
                            <span>{field_label const="ENTRY_STATE" configuration="ACCOUNT_STATE"}</span>
                            {if $entry_state_has_zones}
                                <select name="state">
                                    {foreach $zones_array as $zone}
                                        <option value="{$zone.id}"{if $billing_state == $zone.id} selected{/if}>{$zone.text}</option>
                                    {/foreach}
                                </select>
                            {else}
                                {if in_array(ACCOUNT_STATE, ['required', 'required_register'])}
                                    <input type="text" name="state" value="{$billing_state|escape:'html'}" data-pattern="{$re1}{$smarty.const.ENTRY_STATE_MIN_LENGTH}{$re2}" data-required="{$smarty.const.ENTRY_STATE_ERROR}"/>
                                {else}
                                    <input type="text" name="state" value="{$billing_state|escape:'html'}"/>
                                {/if}
                            {/if}
                        </label>
                    </div>
                {/if}
                {if in_array(ACCOUNT_COUNTRY, ['required', 'required_register', 'visible', 'visible_register'])}
                    <div class="col-2">
                        <label>
                            <span>{field_label const="ENTRY_COUNTRY" configuration="ACCOUNT_COUNTRY"}</span>
                            <select name="country" data-required="{$smarty.const.ENTRY_COUNTRY_ERROR}">
                                {foreach $bill_countries as $country}
                                    <option value="{$country.countries_id}"{if $country.countries_id == $billing_country} selected{/if}>{$country.countries_name}</option>
                                {/foreach}
                            </select>
                        </label>
                    </div>
                {/if}
            </div>
        </div>
    </div>
