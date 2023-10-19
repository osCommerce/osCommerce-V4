
<div class="contacts {if $settings[0].show_icons}contacts-icons{/if}" {*itemscope itemtype="http://schema.org/Organization"*}>
  <div class="heading-4">
    {if $settings[0]['tag_company']}
      <{$settings[0]['tag_company']}>{$data.company}</{$settings[0]['tag_company']}>
    {else}
      {$data.company}
    {/if}
  </div>

  <address {*itemprop="address" itemscope itemtype="http://schema.org/PostalAddress"*}>
    {$address}
  </address>

  <table>
    <tr>
      <td class="phone">
          {if $settings[0]['tag_phone_label']}
            <{$settings[0]['tag_phone_label']}><strong>{$smarty.const.ENTRY_TELEPHONE_NUMBER}</strong></{$settings[0]['tag_phone_label']}>
          {else}
            <strong>{$smarty.const.ENTRY_TELEPHONE_NUMBER}</strong>
          {/if}
      </td>
      <td>
          {if $settings[0]['tag_phone']}
            <{$settings[0]['tag_phone']}>
              <span {*itemprop="telephone"*} content="{$phone}">{if $settings[0]['add_link_on_phone']}<a href="tel:{$data.telephone}">{$data.telephone}</a>{else}{$data.telephone}{/if}</span>
            </{$settings[0]['tag_phone']}>
          {else}
            <span {*itemprop="telephone"*} content="{$phone}">{if $settings[0]['add_link_on_phone']}<a href="tel:{$data.telephone}">{$data.telephone}</a>{else}{$data.telephone}{/if}</span>
          {/if}
      </td>
    </tr>
    {if $settings[0]['show_landline']|default}
      <tr>
        <td class="phone">
            <strong>{$smarty.const.ENTRY_LANDLINE}</strong>
        </td>
        <td>
            {$data.landline}
        </td>
      </tr>
    {/if}
    <tr>
      <td class="email">
          {if $settings[0]['tag_email_label']}
            <{$settings[0]['tag_email_label']}>
              <strong>{output_label const="TEXT_EMAIL"}</strong>
            </{$settings[0]['tag_email_label']}>
          {else}
            <strong>{output_label const="TEXT_EMAIL"}</strong>
          {/if}
      </td>
      <td>
          {if $settings[0]['tag_email']}
            <{$settings[0]['tag_email']}>
              <span {*itemprop="email"*}>
                  {if $settings[0]['add_link_on_email']}
                      <a href="mailto:{$data.email_address}">
                  {/if}
                  {if $settings[0]['use_at_in_email']}
                      {$data.email_address}
                  {else}
                      {str_replace('@', '(at)', $data.email_address)}
                  {/if}
                  {if $settings[0]['add_link_on_email']}
                      </a>
                  {/if}
              </span>
            </{$settings[0]['tag_email']}>
          {else}
            <span {*itemprop="email"*}>
                  {if $settings[0]['add_link_on_email']}
                <a href="mailto:{$data.email_address}">
                  {/if}
                    {if $settings[0]['use_at_in_email']}
                        {$data.email_address}
                    {else}
                        {str_replace('@', '(at)', $data.email_address)}
                    {/if}
                    {if $settings[0]['add_link_on_email']}
                      </a>
                {/if}
            </span>
          {/if}

      </td>
    </tr>

  {if $data.reg_number neq ''}
    <tr>
      <td class="reg-number">
          {if $settings[0]['tag_company_no_label']}
            <{$settings[0]['tag_company_no_label']}>
              <strong>{output_label const="TEXT_REG_NUMBER"}</strong>
            </{$settings[0]['tag_company_no_label']}>
          {else}
            <strong>{output_label const="TEXT_REG_NUMBER"}</strong>
          {/if}

      </td>
      <td>
          {if $settings[0]['tag_company_no']}
            <{$settings[0]['tag_company_no']}>
              <span itemprop="leiCode">{$data.reg_number}</span>
            </{$settings[0]['tag_company_no']}>
          {else}
            <span itemprop="leiCode">{$data.reg_number}</span>
          {/if}

      </td>
    </tr>
  {/if}
  {if isset($data.company_vat) && $data.company_vat neq ''}
    <tr>
      <td class="company-vat">
          {if $settings[0]['tag_company_vat_label']}
            <{$settings[0]['tag_company_vat_label']}>
              <strong>{output_label const="ENTRY_BUSINESS"}</strong>
            </{$settings[0]['tag_company_vat_label']}>
          {else}
            <strong>{output_label const="ENTRY_BUSINESS"}</strong>
          {/if}
      </td>
      <td>
          {if $settings[0]['tag_company_vat']}
            <{$settings[0]['tag_company_vat']}>
              <span itemprop="vatID">{$data.company_vat}</span>
            </{$settings[0]['tag_company_vat']}>
          {else}
            <span itemprop="vatID">{$data.company_vat}</span>
          {/if}
      </td>
    </tr>
  {/if}
  </table>
  
  <div class="hours" style="margin-top: 20px;">
    {if $settings[0]['tag_opening_hours_label']}
      <{$settings[0]['tag_opening_hours_label']}>
        <strong>{$smarty.const.TEXT_OPENING_HOURS}</strong>
      </{$settings[0]['tag_opening_hours_label']}>
    {else}
      <strong>{$smarty.const.TEXT_OPENING_HOURS}</strong>
    {/if}

  </div>

  <div class="hours-content">
  {foreach $data.open as $item}
    {if $settings[0]['tag_opening_hours']}
    <div>
      <{$settings[0]['tag_opening_hours']}>
        {$item.days_short} (<time datetime="{$item.time_from}">{$item.time_from}</time>-<time datetime="{$item.time_to}">{$item.time_to}</time>)
      </{$settings[0]['tag_opening_hours']}>
    </div>
    {else}
      <p>{$item.days_short} (<time datetime="{$item.time_from}">{$item.time_from}</time>-<time datetime="{$item.time_to}">{$item.time_to}</time>)</p>
    {/if}


  {/foreach}
  </div>
</div>