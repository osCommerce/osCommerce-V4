<div class="shipping-estimator">
  {\frontend\design\Info::addBoxToCss('form')}
  <div class="heading-2">{$smarty.const.SHIPPING_OPTIONS}</div>

  {if not $is_logged_customer}
  <div class="info">{sprintf($smarty.const.PLEASE_LOG_IN, tep_href_link('account/login', '', 'SSL'))}</div>
  {/if}


  <div class="left-area">
    {if $is_logged_customer}
    <div class="heading-3">{$smarty.const.SHIPPING_ADDRESS}</div>
    <div class="addresses" id="shipping-addresses">
      {foreach $addresses as $address }
      <div class="address-item active">
        <label>
          <input class="js-ship-estimate" type="radio" name="estimate[sendto]" value="{$address.address_book_id}" {if $address.address_book_id==$addresses_selected_value} checked="checked"{/if}>
          <span>{\common\helpers\Address::address_format($address['country']['address_format_id'], $address, 0, ' ', ' ', true)}</span>
        </label>
      </div>
      {/foreach}
    </div>
    {else}
    <div class="heading-3">{$smarty.const.ESTIMATE_SHIPPING}</div>

    <div class="estimate-shipping form-inputs">

      <div class="col-full">
        <label>
          <span>{field_label const="COUNTRY" required_text=""}</span>
          <select name="estimate[country_id]">
            {foreach $countries as $country}
              <option value="{$country.countries_id}"{if isset( $estimate['country_id']) && $country.countries_id == $estimate['country_id']} selected{/if}>{$country.countries_name}</option>
            {/foreach}
          </select>
        </label>
      </div>
      <div class="col-left">
        <label>
          <span>{field_label const="ENTRY_POST_CODE" required_text=""}</span>
          <input type="text" name="estimate[post_code]" value="{if isset($estimate['postcode'])}{$estimate['postcode']|escape:'html'}{/if}"/>
        </label>
      </div>
      <div class="col-right">
        <span>&nbsp;</span><br><span class="btn js-ship-estimate">{$smarty.const.RECALCULATE}</span>
      </div>

    </div>
    {/if}

  </div>
  <div class="right-area">

    {if $display_weight}
    <div class="heading-3"><div class="right-text"><strong>{$smarty.const.WEIGHT}</strong> {$weight}{$weight_unit}</div>{$smarty.const.SHIPPING_METHOD}</div>
    {/if}

    <div class="shipping-method">
        <div>
        {assign var=mCount value = 0}
        {foreach $manager->getShippingQuotesByChoice() as $shipping_quote_item}
            {if $mCount eq 0}{$mCount = $manager->getShippingCollection()->allMethodsCount}{/if}
            <div class="item" {if isset($shipping_quote_item.hide_row) && $shipping_quote_item.hide_row}style="display: none;"{/if}>
                {if isset($shipping_quote_item.error) && !empty($shipping_quote_item.error)}
                    {*<div class="error">{$shipping_quote_item.error}</div>*}
                {else}
                    <div class="title">{$shipping_quote_item.module}</div>
                    {foreach $shipping_quote_item.methods as $shipping_quote_item_method}
                        <label class="row">
                            {if $mCount > 1}
                                <div class="input"><input class="js-ship-estimate" value="{$shipping_quote_item_method.code}" {if $shipping_quote_item_method.selected}checked="checked"{/if} type="radio" name="estimate[shipping]"/></div>
                            {else}
                                <input value="{$shipping_quote_item_method.code}" type="hidden" name="estimate[shipping]"/>
                            {/if}
                            <div class="cost">{$shipping_quote_item_method.cost_f}</div>
                            <div class="sub-title">{$shipping_quote_item_method.title}</div>
                        </label>
                    {/foreach}
                {/if}
            </div>
        {/foreach}

        </div>
    </div>

  </div>
</div>
