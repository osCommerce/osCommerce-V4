{use class="\yii\helpers\Html"}
<div>
        {Html::beginForm('', 'post', ['id' => 'checkoutForm'])}
        <div class="contact-holder">

            <div class="mb-3">
            <div class="widget box widget-closed" id="customer-details">
                <div class="widget-header">
                    <h4>{$smarty.const.BOX_CONFIGURATION_CUSTOMER_DETAILS}</h4>
                    {if !$manager->isCustomerAssigned() && !$manager->getCustomersIdentity()->get('fromOrder')}
                        <span class="btn btn-confirm btn-reassing me-4" style="white-space: nowrap">{$smarty.const.ASSIGN_CUSTOMER}</span>
                    {/if}
                    <div class="toolbar no-padding">
                        <div class="btn-group">
                            <span class="btn btn-xs widget-collapse"><i class="icon-angle-up"></i></span>
                        </div>
                    </div>
                </div>
                <div class="widget-content after">

                    <div class="row">
                        <div class="customer-box col-12 col-md-4 mb-2">
                            {$manager->render('Customer', ['manager' => $manager, 'admin'=> $admin])}
                        </div>
                        {if $manager->isCustomerAssigned() || $manager->getCustomersIdentity()->get('fromOrder')}
                        <div class="shipping-address-box col-12 col-md-4 mb-2">
                            {$manager->render('ShippingAddress', ['manager' => $manager])}
                        </div>
                        <div class="billing-address-box col-12 col-md-4">
                            {$manager->render('BillingAddress', ['manager' => $manager])}
                        </div>
                        {/if}
                    </div>

                </div>
            </div>
            </div>


            <div class="modules-box mb-3">
                <div class="shipping-modules-box">
                    {$manager->render('Shipping', ['manager' => $manager])}
                </div>
                <div class="payment-modules-box">
                    {$manager->render('Payment', ['manager' => $manager])}
                </div>
            </div>
        </div>
        <div class = "btn-tools">
            <div class="btn-left">
                <span onclick="return backStatement();" class="btn btn-back">{$smarty.const.IMAGE_BACK}</span>
                <span id="reset_checkout" class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</span>
            </div>
            {*<div class="btn-right">
                <a href="javascript:void(0)" id="save_checkout"  class="btn btn-primary btn-save-checkout">Save changes</a>
            </div>*}
        </div>
        {Html::endForm()}
</div>
<script>
var saveCheckoutInProgress = false;
function saveCheckoutForm() {
    if (saveCheckoutInProgress == false) {
        saveCheckoutInProgress = true;
        $('#save_checkout').attr('disabled','disabled');
        order.saveCheckout($('#checkoutForm'));
        setTimeout(function () {
            $('#save_checkout').removeAttr('disabled');
            saveCheckoutInProgress = false;
        }, 1000);
    }
    return false;
};

  (function($){
    $("#reset_checkout").click(function(){
        order.resetCheckout();
    })
  })(jQuery)
</script>