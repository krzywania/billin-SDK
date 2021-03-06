#!/usr/bin/env php

<?php
require 'billin.php5';

## session initiation
$sess = new BillinSession();

## billing data creation
$billing_data = $sess->create_billing_data(
			       array(fname => 'Jan', 
				     lname => 'Kowalski', 
				     city => 'Warszawa', 
				     post_code => '00001', 
				     country => 'Poland', 
				     tax_id => '1112223344', 
				     email => 'bit-bucket@mailinator.com', 
				     phone => '+48111222333', 
				     company_name => 'ACME Widgets Inc.')
			     );

## coupon check
$sess->check_coupon('000sd');
$sess->check_coupon('000sd', 'CRM Basic - month');

## customer creation
$customer = $sess->create_customer($billing_data);

$sess->redeem_coupon(ref(-1), '000sd');

## product retrieval by product_id, $customer was assigned before
$product = $sess->get_product_params('CRM Complete - month', ref(-2));

## product configuration
## ! is used as a product parameter tree separator - it reflects product
## configuration in the GUI
$sess->configure_product($product, 
         array('CRM Complete - miesieczny!Abonament CRM Complete!Cena indywidualna' => amount('100'),
               'CRM Complete - miesieczny' => 1)
       );

## to assign a product we need a subscription object, let's create it
$subscription = $sess->create_subscription($customer);

## asignment of a configured product - before product assignment a subscription object is created
$sess->assign_product($product, $subscription);

## Billin API is transactional - a commit is mandatory
$sess->commit();

## print all subscription statusses to the screen
print_r($sess->all_subscriptions_status());

## get pending payment information for PayU IPSP
print_r($sess->get_payu_pending_payment($customer));

## authorize card
try {
	print_r($sess->authorize_card($customer, 'visa', '4111111111111111', '0000', 2020, 4, 'John Doe', 'jd@acme.com',
					'127.0.0.1', 'US', 'NYC', 'Elm Street', '12345', 'EUR'));
} catch (BillinPCPException $ex) {
	print_r(array(descr => $ex->{descr}));
}
?>
