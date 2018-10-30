<?php
/*
 * Checkout.php
 * Controller for checkout flow
 */

require_once __DIR__ . '/User.php';
require_once __DIR__ . '/Product.php';
require_once __DIR__ . '/../PayPal-PHP-SDK/autoload.php';


class Checkout {

    /*
     * Completes a payment, sending it off to paypal
     * for processing
     * @param token - User's auth token
     * @param code - Optional promotional code
     */
    static function Complete($args):void {
        if(!$args['token']) {
            error("Missing required fields");
            return;
        }
        //check if user is logged in
        $user = new SiteUser(null, $args['token']);
        if(!$user->isAuth()) { 
            error("User is not authenticated");
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
        $price = getCartPrice($user->id, $args['code']);
        
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
     * Edit's the site's paypal details
     * @param token - User's auth token
     * @param paypal-clientId   
     * @param paypal-secret
     * @param paypal-success-url
     * @param paypal-cancel-url
     */
    static function PayPalEdit($args):void {
        if(!($args['token'] && $args['paypal-clientId'] && $args['paypal-secret'] && $args['paypal-success-url'] && $args['paypal-cancel-url'])) {
            error("Missing required fields");
            return;
        }
        //check if user is logged in as admin
        $user = new SiteUser(null, $args['token']);
        if(!$user->isAuth() || $user->type != "admin") { 
            error("User is not authenticated");
            return;
        }

        $db = $GLOBALS['database'];
        if(!$db->query("UPDADE siteadmin SET"
            . " paypal-clientId = '" . $args['paypal-clientId'] . "',"
            . " paypal-secret = '" . $args['paypal-secret'] . "',"
            . " paypal-success-url = '" . $args['paypal-success-url'] . "',"
            . " paypal-cancel-url = '" . $args['paypal-cancel-url'] . "';")) {
            error($db->error);
            return;
        }
        success();
    }

    /*
     * Calculates te total price of the user's cart
     * @param userId - The user's id
     * @param promotionCode - an optional promotion code
     */
    private static function getCartPrice($userId, $promotionCode):float {
	    $db = $GLOBALS['database'];
        $products = $db->query("SELECT * FROM cart WHERE userId = '" . $userId . "';");
        $cost = 0;
        while($product = $products->fetch_assoc()) {
            $prod = new ViewableProduct($product['itemId']);
            $cost += (float)$prod->price * (int)$product['quantity'];
        }

        if($promotionCode) {
            //TODO: process promotions
        }
        return $cost;
    }  

}

?>
