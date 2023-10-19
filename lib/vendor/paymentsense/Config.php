<?php
	// Will need to set these variables to valid a MerchantID and Password
	// These were obtained during sign up
	$MerchantID = "lewist-5620897";
	$Password = "Lewistest1234";

	
	// This is the domain (minus any host header or port number for your payment processor
	// e.g. for "https://gwX.paymentsensegateway.com:4430/", this should be "paymentsensegateway.com"
	// e.g. for "https://gwX.thepaymentgateway.net/", this should be "thepaymentgateway.net"
	$PaymentProcessorDomain = "paymentsensegateway.com";
   	// This is the port that the gateway communicates on -->
	// e.g. for "https://gwX.paymentsensegateway.com:4430/", this should be 4430
	// e.g. for "https://gwX.thepaymentgateway.net/", this should be 443
	$PaymentProcessorPort = 4430;

	// This is used to generate the Hash Keys that detect variable tampering
	// You should change this to something else
	$SecretKey = "lewist";

	if ($PaymentProcessorPort == 443)
	{
		$PaymentProcessorFullDomain = $PaymentProcessorDomain."/";
	}
	else
	{
		$PaymentProcessorFullDomain = $PaymentProcessorDomain.":".$PaymentProcessorPort."/";
	}
?>