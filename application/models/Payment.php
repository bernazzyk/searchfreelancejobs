<?php

define("AUTHORIZENET_API_LOGIN_ID", "9GH8LBb4kud");
define("AUTHORIZENET_TRANSACTION_KEY", "9XYz3g6YHbs643C8");
define("AUTHORIZENET_SANDBOX", false);

//define("PAYPAL_EMAIL", 'sergey_1345211687_per@gmail.com');
//define("PAYPAL_API_USERNAME", 'sergey_1345211687_per_api1.gmail.com');
//define("PAYPAL_API_PASSWORD", '1366250536');
//define("PAYPAL_API_SIGNATURE", 'An5ns1Kso7MWUdW4ErQKJJJ4qi4-A4ZVfMfwwMwp0MhVvzAlqTPgKC2k');

define("PAYPAL_EMAIL", 'billing@yestoapps.com');
define("PAYPAL_API_USERNAME", 'admin_api1.yestoapps.com');
define("PAYPAL_API_PASSWORD", 'K9G89S7PLBTAAN6N');
define("PAYPAL_API_SIGNATURE", 'AFcWxV21C7fd0v3bYYYRCpSSRl31ADVmIOUaQlc3BHq9rRHS4Iey24RX');

define('SUBSCRIPTION_COST', 19.95);

define('UPGRADE_COST', 10.00); //for testing otherwise 10$

define('IS_SANDBOX', 0);

class Application_Model_Payment
{
    
    const PAYPAL_URL = 'https://www.paypal.com/webscr';
    
    
    public function ccQuery(Application_Model_DbTable_Transaction $transaction, Application_Model_DbTable_Account $account, array $cc)
    {
        include_once('AuthorizeNet/AuthorizeNet.php');
        
        $ccExpr = sprintf('%04d-%02d', $cc['ccexpyear'], $cc['ccexpmonth']);
        
        $countries = new Application_Model_DbTable_Countries();
        $country = $countries->find($account->country_id)->current();
        
        $sale = new AuthorizeNetAIM();
        $sale->amount = '1';
        $sale->card_num = $cc['cc'];
        $sale->exp_date = $ccExpr;
        $sale->card_code = $cc['ccv'];
        $sale->first_name = $account->fname;
        $sale->last_name = $account->lname;
        $sale->description = 'Freelancer.fm check card';
        $sale->zip = $account->post_code;
        $sale->city = $account->city;
        $sale->state = $account->state;
        $sale->address = $account->street;
        $sale->company = $account->company;
        $sale->country = $country->code;
        $response = $sale->authorizeOnly();
        if (!$response->approved) {
            return false;
        }
        $sale->void($response->transaction_id);
        
        $subscription = new AuthorizeNet_Subscription();
        $subscription->name = 'Freelancer.fm subscription';
        $subscription->intervalLength = "1";
        $subscription->intervalUnit = "months";
        $subscription->totalOccurrences = 9999;
        $subscription->amount = $transaction->amount;
        $subscription->creditCardCardNumber = $cc['cc'];
        $subscription->creditCardExpirationDate = $ccExpr;
        $subscription->creditCardCardCode = $cc['ccv'];
        $subscription->billToFirstName = $account->fname;
        $subscription->billToLastName = $account->lname;
        $subscription->startDate = date('Y-m-d', strtotime('+ 1 week'));
        $subscription->billToZip = $account->post_code;
        $subscription->billToCity = $account->city;
        $subscription->billToState = $account->state;
        $subscription->billToAddress = $account->street;
        $subscription->billToCompany = $account->company;
        $subscription->billToCountry = $country->code;
        
        $request = new AuthorizeNetARB();
        $response = $request->createSubscription($subscription);
        $subscriptionId = $response->getSubscriptionId();
        
        return $subscriptionId;
    }
    
    public function checkSubscription($subscriptionId)
    {
        include_once('AuthorizeNet/AuthorizeNet.php');
        
        $arb = new AuthorizeNetARB();
        $result = $arb->getSubscriptionStatus($subscriptionId);
        return $result->getSubscriptionStatus() == 'active';
    }
    
    public function cancelSubscription($subscriptionId)
    {
        include_once('AuthorizeNet/AuthorizeNet.php');
        
        $arb = new AuthorizeNetARB();
        $result = $arb->cancelSubscription($subscriptionId);
        return $result->isOk();
    }
    
    public function cancelPayPal($subscriptionId)
    {
        $apiRequest = 'USER=' . urlencode(PAYPAL_API_USERNAME)
            . '&PWD=' . urlencode(PAYPAL_API_PASSWORD)
            . '&SIGNATURE=' . urlencode(PAYPAL_API_SIGNATURE)
            . '&VERSION=76.0'
            . '&METHOD=ManageRecurringPaymentsProfileStatus'
            . '&PROFILEID=' . urlencode($subscriptionId)
            . '&ACTION=Cancel'
            . '&NOTE=' . urlencode('Profile cancelled at store');
        
        $url = IS_SANDBOX ? 'https://api-3t.sandbox.paypal.com/nvp' : 'https://api-3t.paypal.com/nvp';
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        
        curl_setopt($ch, CURLOPT_POSTFIELDS, $apiRequest);
        
        $response = curl_exec($ch);
        
        if (!$response) {
            return false;
        }
        
        curl_close($ch);
        
        parse_str($response, $parsedResponse);
        return isset($parsedResponse['ACK']) && 'Success' == $parsedResponse['ACK'];
    }
	  public function getPayPalFormForUpgrade()
    {
        $paypalEmail = PAYPAL_EMAIL;
		//$paypalEmail = "arvindjobs2014-facilitator@gmail.com";
        $payPalUrl = self::PAYPAL_URL;
		$UPGRADE_COST = UPGRADE_COST;
		//$payPalUrl = "https://www.sandbox.paypal.com/cgi-bin/webscr";
		//$UPGRADE_COST = 1;
        return <<<HTML
Please wait, you will be redirected to the paypal website.<br />
If you are not automatically redirected to paypal within 5 seconds...
<form method="post" name="dps_paypal_form" action="{$payPalUrl}">
    <input type="hidden" name="cmd" value="_xclick-subscriptions">
    <input type="hidden" name="business" value="{$paypalEmail}">
    <input type="hidden" name="return" value="http://{$_SERVER['SERVER_NAME']}/payment/successupgrade">
    <input type="hidden" name="cancel_return" value="http://{$_SERVER['SERVER_NAME']}/payment/cancel">
    <input type="hidden" name="notify_url" value="http://{$_SERVER['SERVER_NAME']}/payment/result-paypal">
    <input type="hidden" name="item_name" value="Freelancer.fm subscription">
	<input type="hidden" name="item_number" value="1">
    <input type="hidden" name="a3" value="{$UPGRADE_COST}">
    <input type="hidden" name="p3" value="1">
    <input type="hidden" name="t3" value="M">
    <input type="hidden" name="src" value="1">
    <input type="hidden" name="no_note" value="1">
    <input type="hidden" name="no_shipping" value="1">
    <input type="hidden" name="currency_code" value="USD">
    <input type="submit" style="display: none;">
</form>
<br>
<script>$(document).ready(function() { document.dps_paypal_form.submit(); }); </script>
HTML;
    }
    
    public function getPayPalForm(Application_Model_DbTable_Transaction $transaction)
    {
        $paypalEmail = PAYPAL_EMAIL;
        $payPalUrl = self::PAYPAL_URL;
        return <<<HTML
Please wait, you will be redirected to the paypal website.<br />
If you are not automatically redirected to paypal within 5 seconds...
<form method="post" name="dps_paypal_form" action="{$payPalUrl}">
    <input type="hidden" name="cmd" value="_xclick-subscriptions">
    <input type="hidden" name="business" value="{$paypalEmail}">
    <input type="hidden" name="return" value="http://{$_SERVER['SERVER_NAME']}/payment/success">
    <input type="hidden" name="cancel_return" value="http://{$_SERVER['SERVER_NAME']}/payment/cancel">
    <input type="hidden" name="notify_url" value="http://{$_SERVER['SERVER_NAME']}/payment/result-paypal">
    <input type="hidden" name="item_name" value="Freelancer.fm subscription">
    <input type="hidden" name="item_number" value="{$transaction->id}">
    <input type="hidden" name="a1" value="0">
    <input type="hidden" name="p1" value="1">
    <input type="hidden" name="t1" value="W">
    <input type="hidden" name="a3" value="{$transaction->amount}">
    <input type="hidden" name="p3" value="1">
    <input type="hidden" name="t3" value="M">
    <input type="hidden" name="src" value="1">
    <input type="hidden" name="no_note" value="1">
    <input type="hidden" name="no_shipping" value="1">
    <input type="hidden" name="currency_code" value="USD">
    <input type="submit" style="display: none;">
</form>
<br>
<script>$(document).ready(function() { document.dps_paypal_form.submit(); }); </script>
HTML;
    }
    
    public function validatePayPal()
    {
        $url_parsed=parse_url(self::PAYPAL_URL);
        
        if (empty($_POST['txn_type'])) {
            return false;
        }
        
        if ($_POST['txn_type'] == 'subscr_payment' && SUBSCRIPTION_COST != $_POST['payment_gross']) {
            return false;
        }
        
        $post_string = '';
        foreach ($_POST as $field=>$value) {
            $this->pp_data["$field"] = $value;
            $post_string .= $field.'='.urlencode(stripslashes($value)).'&';
        }
        $post_string.="cmd=_notify-validate";
        // open the connection to paypal
        $fp = fsockopen($url_parsed["host"],"80",$err_num,$err_str,30);
        if(!$fp) {
            return false;
        } else {
            // Post the data back to paypal
            fputs($fp, "POST ".$url_parsed["path"]." HTTP/1.1\r\n");
            fputs($fp, "Host: ".$url_parsed["host"]."\r\n");
            fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
            fputs($fp, "Content-length: ".strlen($post_string)."\r\n");
            fputs($fp, "Connection: close\r\n\r\n");
            fputs($fp, $post_string . "\r\n\r\n");
            // loop through the response from the server and append to variable
            while(!feof($fp)) {
                $this->response .= fgets($fp, 1024);
            }
            fclose($fp); // close connection
        }
        
        //add checking to sandbox.paypal.com
        //2012-12-16
        if (eregi("VERIFIED",$this->response) || $url_parsed["host"] == 'www.paypal.com') {
            // Valid IPN transaction.
            return true;
        } else {
            // Invalid IPN transaction.
            return false;
        }
    }
    
    
    
}