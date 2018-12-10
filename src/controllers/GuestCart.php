<?php
/*
 * GuestCart.php
 * Defines an interface for interacting with an 
 * guest user's cart
 */

require_once __DIR__ . '/Product.php';
require_once __DIR__ . '/Promotion.php';
/*
 * Calculates the total price of the user's cart
 * @param guestId - The user's id
 * @param promotionCode - an optional promotion code
 */
function getCartPrice($guestId, $promotionCode):float {
    $db = $GLOBALS['database'];
    $products = $db->query("SELECT * FROM guestCart WHERE guestId = '" . $guestId . "';");
    $cost = 0;
    while($product = $products->fetch_assoc()) {
        $prod = new ViewableProduct($product['itemId']);
        $cost += (float)$prod->price * (int)$product['quantity'];
    }

    if($promotionCode) {
        //TODO: process promotions with more validation
        $result = $db->query("SELECT * FROM promotions WHERE code = '" . $promotionCode . "';");
        if(mysqli_num_rows($result) > 0){$promo = $result->fetch_assoc();}
        if($promo['type'] == 'percent'){
            $cost = $cost * (float)($promo['percent']/100);
        }
        
        //$cost = $cost * $promotionCode;
    }
    return $cost;
}  

class GuestCart {
    /*
     * Adds an item to a user's cart
     * @param itemId - the item ID
     * @param itemQuantity - the number of items to add
     * @param guestId - the user's guest ID
     */
    static function Add($args):void {
        //check params
        if(!($args['itemId'] && $args['guestId'] && $args['itemQuantity'])) {
            error("Missing required fields");
            return;
        }

        //check if product is in stock
        $prod = new ViewableProduct($args['itemId']);
        if($prod->quantity < (int)$args['itemQuantity']) {
            error("Product is out of stock");
            return;
        }
        
        //check if item is already in user's cart
        $db = $GLOBALS['database'];
        $sql = "";
        $result = $db->query("SELECT * FROM guestCart WHERE guestId = '" . $args['guestId'] . "' AND itemId = '" . $args['itemId'] . "';"); 
        if(mysqli_num_rows($result) > 0) { 
            $sql = "UPDATE guestCart SET quantity = quantity + ". $args['itemQuantity']." WHERE userId = '" . $args['guestId'] . "' AND itemId = '" . $args['itemId'] . "';";
        }
        else {
            $sql = "INSERT INTO guestCart (guestId, itemId, quantity) VALUES ('" . $args['guestId'] . "', '" . $args['itemId'] . "', ". $args['itemQuantity'] .");";
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
     * @param guestId - the user's guest ID
     * @return A list of items in the user's cart
     */
    static function Get($args):void {
        //Check if the token was included
        if(!$args['guestId']) {
            error("Missing required fields");
            return;
        }

        $db = $GLOBALS['database'];
        $products = $db->query("SELECT * FROM guestCart WHERE guestId = '" . $args['guestId'] . "';");
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
     * Updates the quantity of an item in a user's cart
     * @param guestId - The user's guest ID
     * @param itemId - The item id
     * @param quantity - The new quantity
     */
    static function Update($args):void {
        //Check if the token was included
        if(!($args['itemId'] && $args['quantity'] && $args['guestId'])) {
            error("Missing required fields");
            return;
        }

        //check if product is in stock
        $prod = new ViewableProduct($args['itemId']);
        if($prod->quantity < (int)$args['quantity']) {
            error("Product is out of stock");
            return;
        }
        
        $db = $GLOBALS['database'];
        if(!$db->query("UPDATE guestCart SET quantity = " . $args['quantity'] . " WHERE guestId = '" . $args['guestId'] . "' AND itemId = '" . $args['itemId'] . "';")) {
            error($db->error);
            return;
        }
        success();
    }
    
    /*
     * Delete's an item from a user's cart
     * @param guestId - The user's guest ID
     * @param itemId - The item id
     */
    static function Delete($args):void {
        //Check if the token was included
        if(!($args['itemId'] && $args['guestId'])) {
            error("Missing required fields");
            return;
        }

        $db = $GLOBALS['database'];
        if(!$db->query("DELETE FROM guestCart WHERE guestId = '" . $args['guestId'] . "' AND itemId = '" . $args['itemId'] . "';")) {
            error($db->error);
            return;
        }
        success();
    }
    
    /*
     * Delete's all items from a user's cart
     * @param guestId - The user's guest ID
     */
    static function DeleteAll($args):void {
        //Check if the token was included
        if(!$args['guestId']) {
            error("Missing required fields");
            return;
        }

        $db = $GLOBALS['database'];
        if(!$db->query("DELETE FROM guestCart WHERE guestId = '" . $args['guestId'] . "';")) {
            error($db->error);
            return;
        }
        success();
    }

    /*
     * Get's the total amount for the cart.
     * @param guestId: The user's guest ID
     * @param code: An optional promotion code
     * @return the total cost of all items in the user's cart
     */
    static function Total($args):void {
        //Check if the token was included
        if(!$args['guestId']) {
            error("Missing required fields");
            return;
        }
        $output = new HTTPResponse();
        $payload->total = getCartPrice($args['guestId'], $args['code']);
        $output->setPayload($payload);
        $output->complete();
    }
}  
?>
