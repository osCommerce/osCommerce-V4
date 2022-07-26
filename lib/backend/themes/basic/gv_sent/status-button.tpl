<a class="btn btn-primary btn-process-order toggleStatus" data-id="{$gInfo->coupon_id}" data-status="{$gInfo->coupon_active}">
    {if $gInfo->coupon_active === 'Y'}{$smarty.const.IMAGE_ICON_STATUS_RED_LIGHT}{else}{$smarty.const.IMAGE_ICON_STATUS_GREEN_LIGHT}{/if}
</a>