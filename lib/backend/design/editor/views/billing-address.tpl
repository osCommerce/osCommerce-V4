{use class="\yii\helpers\Html"}
{use class="\yii\helpers\Url"}
{assign var = same value = $manager->isBillAsShip()}
<div class="widget box box-no-shadow {if $same}widget-closed{/if}">
    <div class="widget-header widget-header-billing">
        <h4 {if $same}class="disabled"{/if}>
            <span>Billing Address</span>
            {if $manager->isShippingNeeded()}
                <div class="same-address"><input type="checkbox" name="ship_as_bill" value="1" id="as-shipping"{if $same} checked {/if}/>same as Shipping Address</div>
            {/if}
            {if $manager->has('billto')}
                {assign var=bAddress value=$manager->getBillingAddress()}
                <span class="header-address">{\common\helpers\Address::address_format($bAddress['country']['address_format_id']|default:null, ['postcode' => $bAddress['postcode']|default:null, 'country_id' => $bAddress['country_id'], 'city' => $bAddress['city']|default:null ], true, '', '|')}</span>
            {/if}
        </h4>
        {if $same}
            {$manager->render('Toolbar', ['expanded' => false, 'visible'=>false])}
        {else}
            {$manager->render('Toolbar')}
        {/if}
    </div>
    <div class="widget-content after">
        <div class="w-line-row-2">
            <div>&nbsp;</div>
            <div>
                {if $manager->isCustomerAssigned() && count($manager->getCustomersIdentity()->getAddressBooks())>1}
                <label>
                    {Html::a('Show All Addresses', $urlCheckout, ['class' => 'popup albilling address-list'])}
                </label>
                {/if}
            </div>
        </div>
        <div class="billing-address form-inputs">
        {$manager->render('AddressesList', ['manager' => $manager, 'type' => 'billing', 'mode' => 'edit'])}        
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