{use class="\yii\helpers\Html"}
{use class="\yii\helpers\Url"}
<div class="widget box box-no-shadow">
    <div class="widget-header widget-header-address">
        <h4>Shipping Address/Recipient
        {if $manager->has('sendto')}
            {assign var=sAddress value=$manager->getDeliveryAddress()}
            <span class="header-address">
            {\common\helpers\Address::address_format($sAddress['country']['address_format_id']|default:null, ['postcode' => $sAddress['postcode']|default:null, 'country_id' => $sAddress['country_id'], 'city' => $sAddress['city']|default:null ], true, '', ', ')}
            {if $manager->isCustomerAssigned()}
            / {$manager->getCustomersIdentity()->customers_firstname|escape:'html'} {$manager->getCustomersIdentity()->customers_lastname|escape:'html'}
            {/if}
			{if sizeof(\common\helpers\Country::get_countries())< 2}
			/ {\common\helpers\Country::get_country_name($sAddress['country_id'])}
			{/if}
            </span>                
        {/if}
        </h4>
        {$manager->render('Toolbar')}
    </div>
    <div class="widget-content after">
        <div class="w-line-row-2">
            {if $manager->isCustomerAssigned()}
            <div>
                <label>
                    Recipient {Html::checkbox('recipient_as_customer')} Same as customer <span class="recipient_customer">({$manager->getCustomersIdentity()->customers_firstname|escape:'html'} {$manager->getCustomersIdentity()->customers_lastname|escape:'html'})</span>
                </label>
            </div>
            <div>
                {if count($manager->getCustomersIdentity()->getAddressBooks()) > 1}
                <label>
                    {Html::a('Show All Addresses', $urlCheckout, ['class' => 'popup alshipping address-list'])}
                </label>
                {/if}
            </div>
            {/if}
        </div>
        <div class="shipping-address form-inputs">
        {$manager->render('AddressesList', ['manager' => $manager, 'type' => 'shipping', 'mode' => 'edit'])}
        </div>
    </div>
    <script>
        $(document).ready(function(){
            $('a.popup.alshipping.address-list').off().popUp();
        })
    </script>
</div>