{use class="\yii\helpers\Html"}
{use class="\yii\helpers\Url"}
{assign var = same value = $manager->isBillAsShip()}
<div class="widget box box-no-shadow">
    <div class="widget-header widget-header-billing">
        <h4>{$smarty.const.TEXT_BILLING_ADDRESS}</h4>
    </div>
    <div class="widget-content address-block">

        <div>
        {if $manager->isShippingNeeded()}
            <div class="same-address mb-2"><input type="checkbox" name="ship_as_bill" value="1" id="as-shipping"{if $same} checked {/if}/>{$smarty.const.SAME_AS_SHIPPING_ADDRESS}</div>
        {/if}
        </div>

        <div class="billing-address form-inputs {if $same} disabled{/if}">
            {$manager->render('AddressesList', ['manager' => $manager, 'type' => 'billing', 'mode' => 'single'])}
        </div>
    </div>
    <script>
        (function($){
            $('input[name="ship_as_bill"]').click(function(){
                let _this = this;
                if ($(this).prop('checked')){
                    order.setBillAsShip(function(){
                        $(_this).closest('.widget-header').find('.toolbar').hide();
                    });
                } else {
                    //App.init();
                    $(_this).closest('.widget-header').find('h4').removeClass('disabled');
                    $(_this).closest('.widget-header').find('.toolbar').show();
                    $(_this).closest('.widget-header').find('.toolbar .widget-collapse').trigger('click');
                    $(_this).closest('.widget').find('.widget-content .address-list').off().popUp().trigger('click');
                }
            })
            $(document).ready(function(){
                if ($('input[name="ship_as_bill"]').prop('checked')){
                    order.copyAddress({ data: { address_prefix: 'shipping_address', address_box:'shipping-address-box' } }, $('#tab_contact'), '');
                }
                $('a.popup.albilling.address-list').off().popUp();
            })
        })(jQuery)
    </script>
</div>