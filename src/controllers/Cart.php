<?php
/*
 * Cart.php
 * Defines an interface for interacting with an 
 * autheticated user's cart
 */

require_once __DIR__ . '/User.php';
require_once __DIR__ . '/Product.php';


class Cart {
    /*
     * Adds an item to a user's cart
     * @param itemId - the item ID
     * @param token - The user's auth token
     */
    static function Add($args):void {
        //check params
        if(!($args['itemId'] && $args['token'])) {
            error("Missing required fields");
            return;
        }
        //check if user is logged in
        $user = new SiteUser(null, $args['token']);
        if(!$user->isAuth()) { 
            error("User is not authenticated");
            return;
        }

        //check if item is already in user's cart
        $db = $GLOBALS['database'];
        $sql = "";
        $result = $db->query("SELECT * FROM cart WHERE userId = '" . $user->id . "' AND itemId = '" . $args['itemId'] . "';"); 
        if(mysqli_num_rows($result) > 0) { 
            $sql = "UPDATE cart SET quantity = quantity + 1 WHERE userId = '" . $user->id . "' AND itemId = '" . $args['itemId'] . "';";
        }
        else {
            $sql = "INSERT INTO cart (userId, itemId, quantity) VALUES ('" . $user->id . "', '" . $args['itemId'] . "', 1);";
        }
        //insert item into cart
        if(!$db->query($sql)) {
            error($db->error);
            return;
        }
        success();
    }

    /*
     * Get's a list of items in a user's cart
     * @param token - the user's auth token
     * @return A list of items in the user's cart
     */
    static function Get($args):void {
        //Check if the token was included
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
        $products = $db->query("SELECT * FROM cart WHERE userId = '" . $user->id . "';");
        $output = new HTTPResponse();
        $payload->products = array();
        while($product = $products->fetch_assoc()) {
            //add each product to the payload
            try {
                $prod = new ViewableProduct($product['itemId']);
            }
            catch (Exception $e) {
                error($e->getMessage());
            }
            $item = new stdClass();
            $item->id = $product['itemId'];
            $item->name = $prod->name;
            $item->price = $prod->price;
            $item->image = $prod->image;
            $item->quantity = $product['quantity'];
            $payload->products[] = $item;
        }
        $output->setPayload($payload);
        $output->complete();
    }

    /*
     * Updates the quantity of items in a user's cart
     * @param token - The user's auth token
     * @param itemId - The item id
     * @param quantity - The new quantity
     */
    static function Update($args):void {
        //Check if the token was included
        if(!($args['itemId'] && $args['quantity'] && $args['token'])) {
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
        if(!$db->query("UPDATE cart WHERE userId = '" . $user->id . "' AND itemId = '" . $args['itemId'] . "' SET quantity = " . $args['quantity'] . ";")) {
            error($db->error);
            return;
        }
        success();
    }

}  
?>
