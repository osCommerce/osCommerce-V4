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

<div class="currencies" onclick="void(0)">
    <div class="current">
      <span class="currencies-title">
      {foreach $currencies as $currency}
        {if $currency.id == $currency_id}
          {$currency.key}
        {/if}
      {/foreach}
      </span>
    </div>
    <div class="select">
      {foreach $currencies as $currency}
        {if $currency.id != $currency_id}
          <a class="select-link" href="{$currency.link}">{$currency.key}</a>
        {/if}
      {/foreach}
    </div>
</div>