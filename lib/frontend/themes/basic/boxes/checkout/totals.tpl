{\frontend\design\Info::addBoxToCss('price-box')}
<div class="price-box order_totals" id="order_totals">
<span class="icon" style="display:none;"></span>
{foreach $order_total_output as $order_total}
    <div class="price-row {$order_total.code} ot_code_{$order_total.code}{if $order_total.code=='ot_total'} total{/if} {if isset($order_total.class)}{$order_total.class}{/if}{if $order_total.show_line} totals-line{/if}">
        <div class="title">{$order_total.title}</div>
        <div class="price">
            {if $order_total.code == 'ot_shipping'}
                {if $PremiumAccountClass = \common\helpers\Acl::checkExtensionAllowed('PremiumAccount', 'allowed')}
                    {$PremiumAccountClass::showShippingCost($order_total.value)}
                {/if}
            {/if}
            {$order_total.text}
            {if $order_total.code == 'ot_coupon' && !empty($order_total.coupon)}
                <span class="remove-discount" data-code="{$order_total.coupon}"></span>
            {/if}
            {if $order_total.code == 'ot_gv' || $order_total.code == 'ot_bonus_points'}
                <span class="remove-discount"></span>
            {/if}
        </div>
    </div>
{/foreach}
{if ($ccExt = \common\helpers\Acl::checkExtensionAllowed('CustomerCredit', 'allowed'))}
    {$ccExt::getCheckoutTotalHtml($manager)}
{/if}

<script>
    tl(function(){
        $('.ot_gv .remove-discount').on('click', function(){
            $(window).trigger('removeCreditAmount')
        });
        $('.ot_coupon .remove-discount').on('click', function(){
            var postData = { 'code' : $(this).data('code') };
            $.get("{$coupon_remove_action}", postData, function(data, status){
                $(window).trigger('removeDiscountCoupon')
            },"json");
        });
        $('.ot_bonus_points .remove-discount').on('click', function(){
            $(window).trigger('removeBonusPoints')
        });
		$('.icon').off().on('click', function(){
            $(this).toggleClass('active opened');
            $('.price-row').toggleClass('active');
        });
    })
</script>
</div>
