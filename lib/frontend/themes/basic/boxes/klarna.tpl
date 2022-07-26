<klarna-placement
  data-key="{if !empty($data_key)}{$data_key}{else}credit-promotion-small{/if}"
  {if !empty($locale)}
  data-locale="{$locale}"
  {/if}
  {if !empty($theme)}
   data-theme="{$theme}"
  {/if}
  data-purchase-amount="{$price}"
></klarna-placement>
