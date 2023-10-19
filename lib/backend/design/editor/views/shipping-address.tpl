{use class="\yii\helpers\Html"}
{use class="\yii\helpers\Url"}
<div class="widget box box-no-shadow">
    <div class="widget-header widget-header-address">
        <h4>{$smarty.const.ENTRY_SHIPPING_ADDRESS}</h4>
        {*$manager->render('Toolbar')*}
    </div>
    <div class="widget-content address-block">
        {*<div class="w-line-row-2">
            {if $manager->isCustomerAssigned()}
                <div class="mb-3">
                    <label>
                        Recipient {Html::checkbox('recipient_as_customer')}
                        Same as customer
                        <span class="recipient_customer">
                            ({$manager->getCustomersIdentity()->customers_firstname|escape:'html'} {$manager->getCustomersIdentity()->customers_lastname|escape:'html'})
                        </span>
                    </label>
                </div>
            {/if}
        </div>*}
        <div class="shipping-address form-inputs">
            {$manager->render('AddressesList', ['manager' => $manager, 'type' => 'shipping', 'mode' => 'single'])}
        </div>
    </div>
    <script>
        $(document).ready(function(){
            $('a.popup.alshipping.address-list').off().popUp();
        })
    </script>
</div>