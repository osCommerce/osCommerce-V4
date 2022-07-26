<?php
/*
  MultiSafepay Payment Module for osCommerce 
  http://www.multisafepay.com

  Copyright (C) 2008 MultiSafepay.com
 */

if (MODULE_PAYMENT_MULTISAFEPAY_FCO_STATUS == 'True'){
?>
<div style="float:left;">
<pre>
</pre>
</div>
<div class="clear:both"></div>
<!-- BEGIN MSP CHECKOUT -->
<div align="right">
	<div style="width: 220px; margin-top:15px; margin-bottom:5px;">
		<div align="center">
<?php
if ($cart->count_contents() > 0) {
	echo '<a href="mspcheckout/process.php"><img src="mspcheckout/images/button.png" alt="Checkout" name="Checkout"></a>';
	//echo ' <p align="right" style="clear: both; padding: 15px 50px 0 0;"> OR </p>';
}
?>
		</div>
	</div>
</div>

<?php
  // display any MSP error

  if (isset($_GET['payment_error']) && is_object(${$_GET['payment_error']}) && ($error = ${$_GET['payment_error']}->get_error())) {
?>
      <table border="0" width="100%" cellspacing="0" cellpadding="2">
        <tr>
          <td class="main"><b><?php echo \common\helpers\Output::output_string_protected($error['title']); ?></b></td>
        </tr>
      </table>
        
      <table border="0" width="100%" cellspacing="1" cellpadding="2" class="infoBoxNotice">
        <tr class="infoBoxNoticeContents">
          <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
            <tr>
              <td><?php echo tep_draw_separator('pixel_trans.gif', '10', '1'); ?></td>
              <td class="main" width="100%" valign="top"><?php echo \common\helpers\Output::output_string_protected($error['error']); ?></td>
              <td><?php echo tep_draw_separator('pixel_trans.gif', '10', '1'); ?></td>
            </tr>
          </table></td>
        </tr>
      </table>
        
<?php
  }
?>

<!-- END MSP CHECKOUT -->
<?php
}
?>