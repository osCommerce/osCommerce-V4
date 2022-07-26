{if $manager->get('is_multi') == 1}
<div class="">{$manager->get('customer_email_address')|escape:'html'}</div>
{else}
<div class="">{$data.email_address|escape:'html'}</div>
{/if}
<div class="">{$data.telephone|escape:'html'}</div>
<div class="">{$data.landline|escape:'html'}</div>
