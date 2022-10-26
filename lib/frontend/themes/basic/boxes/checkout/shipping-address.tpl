{use class="frontend\design\Info"}
{*$manager->render('ShippingChoice', ['manager' => $manager, 'params' => $params])*}

{if $manager->combineShippings || (!$manager->combineShippings && $manager->getShippingChoice() eq 1)}
    <a name="shipping_address"></a>
    <div class="heading-4" id="shipping_address">{$smarty.const.SHIPPING_ADDRESS}</div>
    {if $smarty.const.BILLING_FIRST=='True'}
        <div class="same-address">
            <input type="checkbox" name="bill_as_ship" id="as-billing"{if $manager->isBillAsShip()} checked {/if}/>
            {$smarty.const.SAME_AS_BILLING}
        </div>
    {/if}

    {*<div class="shipping-address">*}
        {\frontend\design\boxes\checkout\AddressesList::widget(['manager' => $manager, 'type' => 'shipping', 'mode' => 'single', 'settings' => $settings])}
    {*</div> twice*}

{if $smarty.const.BILLING_FIRST=='True'}
   <script>
     tl([
      '{Info::themeFile('/js/main.js')}',
      '{Info::themeFile('/js/bootstrap-switch.js')}'
    ], function(){
        {\frontend\design\Info::addBoxToCss('switch')}

        var switchShipAsBill = function () {
            $('#as-billing').bootstrapSwitch({
                offText: '{$smarty.const.TEXT_NO}',
                onText: '{$smarty.const.TEXT_YES}',
                onSwitchChange: function (event, status) {
                    if (status) {
                        checkout.copy_address({ data: { address_prefix: 'billing_address', address_box:'billing-addresses' } });
                        let dataChanged = checkout.data_changed('recalculation', []);
                        {if $manager->isCustomerAssigned()}
                        dataChanged
                            .then(function(){
                                checkout.set_ship_as_bill();
                            })
                            .catch(function(error){
                                console.error(error)
                            });
                        {/if}
                        setTimeout(function(){
                            $('#shipping-addresses').hide();
                        }, 300)
                    } else {
                        {if $manager->isCustomerAssigned()}
                        if ($('.shipping-addresses').closest('.box').data('address_in') == 'popup') {
                            checkout.get_address_list_popup('shipping');
                        } else {
                            checkout.get_address_list('shipping');
                        }
                        {/if}
                        //$('.hide-billing-address').hide();
                        $('#shipping-addresses').show();
                    }
                }
              });
          }

        switchShipAsBill();

        {if $manager->isBillAsShip()}
            {if $manager->getShippingChoice() && $manager->isShippingNeeded()}
            $('#shipping-addresses').hide();
            {/if}
        {else}
         $('#shipping-addresses').show();
        {/if}

    })
    </script>
    {/if}
{else}
    
{/if}