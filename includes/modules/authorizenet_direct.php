<?php
/*
   osCommerce 2.2 (Snapshot on November 10, 2002) Open Source E-Commerce Solutions
   Authorizenet ADC Direct Connection
   Last Update: November 10, 2002
   Author: Bao Nguyen
   Email: baonguyenx@yahoo.com

   Update: August 13, 2003
   Added: Transaction Key, Sort Order
   Author: Austin Renfroe (Austin519)
   Email: Austin519@aol.com
*/

	unset($form_data);
	$xx = '';
	for ($i=0; $i<sizeof($order->products); $i++) {
		$xx .= $order->products[$i]['qty'] . '-' . ($order->products[$i]['name']) . '**'; 
	}
	//Austin519 - added transaction key
	$form_data = array(
	x_Login => MODULE_PAYMENT_AUTHORIZENET_LOGIN,
	x_tran_key => MODULE_PAYMENT_AUTHORIZENET_TRANSKEY,
	x_Delim_Data => 'TRUE',
	x_Version => '3.1',
	x_Type => 'AUTH_CAPTURE',
	x_Method => MODULE_PAYMENT_AUTHORIZENET_METHOD == 'Credit Card' ? 'CC' : 'ECHECK',
	x_Amount => number_format($order->info['total'], 2),
	x_Card_Num => "$x_Card_Num",
	x_Exp_Date => "$x_Exp_Date",
	x_Card_Code => "$x_Card_Code", 
	x_Email_Customer => MODULE_PAYMENT_AUTHORIZENET_EMAIL_CUSTOMER == 'True' ? 'TRUE': 'FALSE',
	x_Email_Merchant => MODULE_PAYMENT_AUTHORIZENET_EMAIL_MERCHANT == 'True' ? 'TRUE': 'FALSE',
	x_Cust_ID => "$customer_id",
	x_First_Name => "{$order->customer['firstname']}",
	x_Last_Name => "{$order->customer['lastname']}",
	x_Address => "{$order->customer['street_address']}",
	x_City => "{$order->customer['city']}",
	x_State => "{$order->customer['state']}",
	x_Zip => "{$order->customer['postcode']}",
	x_Country => "{$order->customer['country']['title']}",
	x_Phone => "{$order->customer['telephone']}",
	x_Email => "{$order->customer['email_address']}",
	x_Ship_To_First_Name => "{$order->delivery['firstname']}",
	x_Ship_To_Last_Name => "{$order->delivery['lastname']}",
	x_Ship_To_Address => "{$order->delivery['street_address']}",
	x_Ship_To_City => "{$order->delivery['city']}",
	x_Ship_To_State => "{$order->delivery['state']}",
	x_Ship_To_Zip => "{$order->delivery['postcode']}",
	x_Ship_To_Country => "{$order->delivery['country']['title']}",
	x_Customer_IP => "{$_SERVER['REMOTE_ADDR']}",
	x_Description => "$xx",
	tep_session_name() => tep_session_id());

	if(MODULE_PAYMENT_AUTHORIZENET_TESTMODE == 'Test')
	{	$form_data['x_Test_Request'] = 'TRUE';		}

	// concatenate order information variables to $data
        if (is_array($form_data)) foreach ($form_data as $key => $value) {
            $data .= $key . '=' . urlencode(str_replace(',', '', $value)) . '&';
        }
	
	// take the last & out for the string
	$data = substr($data, 0, -1);
	
	unset($response);
	// Post order info data to Authorize.net, make sure you have curl installed
	// Please edit the "Path to cURL" to reflect your path to cURL.  Leave the -d and everything
	// after it intact (i.e. /usr/local/bin/curl or c:/apache/htdocs/bin/curl)
	exec("Path to cURL -d \"$data\" https://secure.authorize.net/gateway/transact.dll", $response);


