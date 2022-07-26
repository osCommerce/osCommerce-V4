<div class="gift-code gift-certificate">
  <div>
    <div class="heading-4">{$smarty.const.GIFT_CERTIFICATE}</div>
    <div class="content">
    <p>{$smarty.const.GIFT_CERTIFICATE_TEXT}</p>
    {if $message_discount_gv}
      {$message_discount_gv}
    {/if}
    <div class="input-apple">
      <button type="submit" class="btn">{$smarty.const.TEXT_APPLY}</button>
      <div><input type="text" name="gv_redeem_code" autocomplete="off" /></div>
    </div>

    </div>
  </div>
</div>