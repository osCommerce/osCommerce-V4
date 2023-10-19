{use class="frontend\design\Info"}
<div class="billing-address-wrap" id="billing_address">
    
    {if (($manager->isShippingNeeded() && $manager->getShippingChoice()) || $manager->combineShippings) && $smarty.const.BILLING_FIRST!='True' && $showBillAsShip}
        {*<div class="hide-billing-address"></div>*}
        <div class="same-address">
            <input type="checkbox" name="ship_as_bill" id="as-shipping"{if $manager->isBillAsShip()} checked {/if}/>
            {$smarty.const.SAME_AS_SHIPPING}
        </div>
    {/if}
    
    {\frontend\design\boxes\checkout\AddressesList::widget(['manager' => $manager, 'type' => 'billing', 'mode' => 'single', 'settings' => $settings])}

{if $smarty.const.BILLING_FIRST!='True' && $showBillAsShip}
    <script>
     tl([
      '{Info::themeFile('/js/main.js')}',
      '{Info::themeFile('/js/bootstrap-switch.js')}'
    ], function(){
        {\frontend\design\Info::addBoxToCss('switch')}
        
        var switchBillAsShip = function () {
            $('#as-shipping').bootstrapSwitch({
                offText: '{$smarty.const.TEXT_NO}',
                onText: '{$smarty.const.TEXT_YES}',
                onSwitchChange: function (event, status) {
                    if (status) {
                        //$('.hide-billing-address').show();
                        checkout.copy_address({ data: { address_prefix: 'shipping_address', address_box:'shipping-addresses' } });
                        let dataChanged = checkout.data_changed('recalculation', []);
                        {if $manager->isCustomerAssigned()}
                        dataChanged
                            .then(function(){
                                checkout.set_bill_as_ship();
                            })
                            .catch(function(error){
                                console.error(error)
                            });
                        {/if}
                        setTimeout(function(){
                            $('#billing-addresses').hide();
                        }, 300)
                    } else {
                        {if $manager->isCustomerAssigned()}
                        if ($('.billing-addresses').closest('.box').data('address_in') == 'popup') {
                            checkout.get_address_list_popup('billing');
                        } else {
                            checkout.get_address_list('billing');
                        }
                        {/if}
                        //$('.hide-billing-address').hide();
                        $('#billing-addresses').show();
                    }
                }
              });
          }

        switchBillAsShip();
        {if $manager->isBillAsShip()}
            //$('.hide-billing-address').show();
            {if $manager->getShippingChoice() && $manager->isShippingNeeded()}
            $('#billing-addresses').hide();
            {/if}
        {else}
            //$('.hide-billing-address').hide();
         $('#billing-addresses').show();
        {/if}
    })
    </script>
{/if}
</div>
