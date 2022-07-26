{*
This file is part of osCommerce ecommerce platform.
 * osCommerce the ecommerce

@link https://www.oscommerce.com
@copyright Copyright (c) 2000-2022 osCommerce LTD

Released under the GNU General Public License
 * For the full copyright and license information, please view the LICENSE.TXT file that was distributed with this source code.
*}
{use class="Yii"}
{use class="frontend\design\boxes\checkout\ShippingList"}
{use class="frontend\design\Info"}
{use class = "yii\helpers\Html"}
<style>
  #addressBookWidgetDiv {
  min-width: 300px;
  max-width: 100%;
  min-height: 228px;
  height: 240px;
}
#walletWidgetDiv {
  min-width: 300px;
  max-width: 100%;
  min-height: 228px;
  height: 240px;
}
.amazon-continue {
  display: none;
}
</style>

{if $payment_error && $payment_error.title }
  <p><strong>{$payment_error.title}</strong><br>{$payment_error.error}</p>
{/if}

{if $message != ''}
  <p>{$message}</p>
{/if}

{Html::beginForm('', 'post', ['id' => 'frmCheckout', 'name' => 'one_page_checkout'])}
  {\frontend\design\Info::addBoxToCss('form')}
  <input type="hidden" value='' id="amazon_order_reference" name="amazon_order_reference">

  <div style="width:50%; float:left">
    <div id="addressBookWidgetDiv" class="amazon-ship-address"> </div>
    <div id="walletWidgetDiv" class="amazon-ship-address"> </div>
    <button style="display:none;" id='Logout' class="btn-2">{$smarty.const.LOGOFF}</button>
  </div>

  <div class=" p-checkout-index col-right amazon-continue">
    <div id="shipping_modules_list" class="shipping-method amazon-ship-methods"> </div>
    <div class="buttons">
      <div class="right-buttons">
        <span class="continue-text">{$smarty.const.CONTINUE_CHECKOUT_PROCEDURE}</span>
        <button id='confirmAmazon' class="btn-2">{$smarty.const.CONTINUE}</button>
      </div>
    </div>
  </div>



<script>
window.onAmazonLoginReady = function() { amazon.Login.setClientId('{$clientId}'); };
window.onAmazonPaymentsReady = function() {
  new OffAmazonPayments.Widgets.AddressBook({
    sellerId: '{$merchantId}',
    scope: 'profile payments:widget',/*Example: scope: profile payments:widget payments:shipping_address payments:billing_address*/
    onOrderReferenceCreate: function(orderReference) {
      // Here is where you can grab the Order Reference ID.
      ref = orderReference.getAmazonOrderReferenceId(); 
      $('#amazon_order_reference').val(ref);
    },
    onAddressSelect: updateShippingTotal,
    design: {
      designMode: 'responsive'
    },
    onReady: function(orderReference) {
      // Enter code here you want to be executed
      // when the AddressBook widget has been rendered.
    },
    onError: function(error) {
      // Your error handling code.
      // During development you can use the following
      // code to view error messages:
      // console.log(error.getErrorCode() + ': ' + error.getErrorMessage());
      // See "Handling Errors" for more information.
    }
  }).bind("addressBookWidgetDiv");

  new OffAmazonPayments.Widgets.Wallet({
    sellerId: '{$merchantId}',
    onPaymentSelect: function(orderReference) {
      // Replace this code with the action that you want to perform
      // after the payment method is selected.

      // Ideally this would enable the next action for the buyer
      // including either a "Continue" or "Place Order" button.
    },
    design: {
      designMode: 'responsive'
    },
    onError: function(error) {
      // Your error handling code.
      // During development you can use the following
      // code to view error messages:
      // console.log(error.getErrorCode() + ': ' + error.getErrorMessage());
      // See "Handling Errors" for more information.
    }
  }).bind("walletWidgetDiv");
};
function toggle_continue_box() {
	if ($('input[name=shipping]:checked', 'form#frmCheckout').length && document.getElementById('amazon_order_reference').value) {
		$('.amazon-continue').show();
	} else {
		$('.amazon-continue').hide();
	}
}

function updateShippingTotal () {

  if ( $('#amazon_order_reference', 'form#frmCheckout').val()  ) {

    $('#shipping_modules_list').css('opacity', '0.5').css('background',"url('https://images-na.ssl-images-amazon.com/images/G/01/ep/loading-large._V364197283_.gif') center center no-repeat");
    $.post('{$updateShippingUrl}', $('#frmCheckout').serialize(), function (data, status) {
        if (status == "success") {
          if (data.replace != undefined) {
            $('#shipping_modules_list').html('<div>'  + data.replace.shipping_method + '</div><div class="price-box" id="order_totals">'  + data.replace.order_totals + '</div>');
            $('input[name="shipping"]').off('click').on('click', updateShippingTotal);
          }

        } else {
          //alert("Request error.");
          console.error(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
          $('#shipping_modules_list').html('<div class="messageStackError">{$smarty.const.MODULE_SHIPPING_ZONES_INVALID_ZONE}</div>');
        }
        $('#shipping_modules_list').css('opacity', '1').css('background', 'transparent');
        $('#confirmAmazon').off('click').on('click', function () {
          window.location.href = "{Yii::$app->urlManager->createAbsoluteUrl( ['checkout/confirmation'])}";
          return false;
        });
        toggle_continue_box();
    }, "json");
  }
}


</script>

<script async="async"	src='{$widgetUrl}'></script>
<script>
  document.getElementById('Logout').onclick = function() {
    amazon.Login.logout();
  };
</script>

{Html::endForm()}



