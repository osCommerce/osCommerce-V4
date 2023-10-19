<div class="cart-checkout-buttons">
<a href="{$link}" class="btn-2 btn-to-checkout"><span class="btn-title">{$title}</span></a>
{if is_array($inline)}
    <div class="cart-express-buttons">
  {foreach $inline as $link}
      <div class="or-text">{$smarty.const.TEXT_OR}</div>
      <div class="add-buttons">
          {$link}
      </div>
  {/foreach}
    </div>
{/if}
</div>
{if ($ccExt = \common\helpers\Acl::checkExtensionAllowed('CustomerCredit', 'allowed'))}
    {$ccExt::getButtonContinueHtml($manager)}
{/if}