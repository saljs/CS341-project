<?php
/*
 * GuestCheckout.php
 * Controller for guest checkout flow
 */

require_once __DIR__ . '/Product.php';
require_once __DIR__ . '/GuestCart.php';
require_once __DIR__ . '/../PayPal-PHP-SDK/autoload.php';

class GuestCheckout {

    /*
     * Completes a payment, sending it off to paypal
     * for processing
     * @param guestId - The user's guest ID
     * @param code - Optional promotional code
     */
    static function Complete($args):void {
        if(!$args['guestId']) {
            error("Missing required fields");
            return;
        }
        
        $db = $GLOBALS['database'];
        $result = $db->query("SELECT * FROM siteadmin;");
        $vars = $result->fetch_assoc();
        
        $apiContext = new \PayPal\Rest\ApiContext(
            new \PayPal\Auth\OAuthTokenCredential($vars['paypal-clientId'], $vars['paypal-secret'])
        );

        $payer = new \PayPal\Api\Payer();
        $payer->setPaymentMethod('paypal');

        //calculate the total amount
        $price = getCartPrice($args['guestId'], $args['code']);
        
        $amount = new \PayPal\Api\Amount();
        $amount->setTotal($price);
        $amount->setCurrency('USD');
        
        $transaction = new \PayPal\Api\Transaction();
        $transaction->setAmount($amount);
        
        $redirectUrls = new \PayPal\Api\RedirectUrls();
        $redirectUrls->setReturnUrl($vars['paypal-success-url'])
            ->setCancelUrl($vars['paypal-cancel-url']);
        
        $payment = new \PayPal\Api\Payment();
        $payment->setIntent('sale')
            ->setPayer($payer)
            ->setTransactions(array($transaction))
            ->setRedirectUrls($redirectUrls);
        
        try {
            $payment->create($apiContext);
            $output = new HTTPResponse();
            $payload->payemntPage = $payment->getApprovalLink();
            $output->setPayload($payload);
            $output->complete();
        }
        catch (\PayPal\Exception\PayPalConnectionException $ex) {
            // This will print the detailed information on the exception.
            //REALLY HELPFUL FOR DEBUGGING
            error($ex->getData());
        }
    }

}

?>
