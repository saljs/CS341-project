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

    /*
     * Finalize a cart when the transaction is complete.
     * @param guestId: The user's guest ID
     * @param paymentId: The PayPal payment ID
     * @param PayerID: The PayPal payer ID
     * @return A success or error message
     */
    static function Finalize($args):void {
        //Check if the token was included
        if(!($args['guestId'] && $args['paymentId'] && $args['PayerID'])) {
            error("Missing required fields");
            return;
        }
        
        //execute payment
        $db = $GLOBALS['database'];
        $result = $db->query("SELECT * FROM siteadmin;");
        $vars = $result->fetch_assoc();
        
        $apiContext = new \PayPal\Rest\ApiContext(
            new \PayPal\Auth\OAuthTokenCredential($vars['paypal-clientId'], $vars['paypal-secret'])
        );
        $payment = \PayPal\Api\Payment::get($args['paymentId'], $apiContext);
        $execution = new PayPal\Api\PaymentExecution();
        $execution->setPayerId($args['PayerID']);
        try {
            $payment->execute($execution, $apiContext);
        }
        catch (\PayPal\Exception\PayPalConnectionException $ex) {
            // This will print the detailed information on the exception.
            error($ex->getData());
            return;
        }

        //remove the items from their cart
        if(!$db->query("DELETE FROM guestCart WHERE guestId = '" . $args['guestId'] . "';")) {
            error($db->error);
            return;
        }
        success();
    }

}

?>
