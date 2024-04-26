{*
  $currencies = array(
    "key"=> "USD",
    "title"=> "USD",
    "id" => "11",
    "symbol_left" => "USD",
    "symbol_right" => "",
    "decimal_point" => ".",
    "thousands_point" => ",",
    "decimal_places" => "2",
    "value" => "1.89040005"
    "link" => ""
  );
*}
{function currencyItem}
    {if !$settings[0].hide_key}
        <span class="key">{$currency.key}</span>
    {/if}
    {if $settings[0].show_title}
        <span class="title">{$currency.title}</span>
    {/if}
    {if $settings[0].show_symbol_left}
        <span class="symbol-left">{$currency.symbol_left}</span>
    {/if}
    {if $settings[0].show_symbol_right}
        <span class="symbol-right">{$currency.symbol_right}</span>
    {/if}
{/function}

<div class="currencies" onclick="void(0)">
    <div class="current">
      <span class="currencies-title">
      {foreach $currencies as $currency}
          {if $currency.id == $currency_id}
              {currencyItem}
          {/if}
      {/foreach}
      </span>
    </div>
    {if $currencies|count > 1}
    <div class="select">
        {foreach $currencies as $currency}
            {if $currency.id == $currency_id}
                <span class="select-link current-item" style="display: none">
                    {currencyItem}
                </span>
            {else}
                <a class="select-link" href="{$currency.link}">
                    {currencyItem}
                </a>
            {/if}
        {/foreach}
    </div>
    {/if}
</div>