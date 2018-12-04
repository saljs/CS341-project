<?php
/*
 * Cart.php
 * Defines an interface for interacting with an 
 * autheticated user's cart
 */

require_once __DIR__ . '/User.php';
require_once __DIR__ . '/Product.php';
require_once __DIR__ . '/Promotion.php';
/*
 * Calculates the total price of the user's cart
 * @param userId - The user's id
 * @param promotionCode - an optional promotion code
 */
function getCartPrice($userId, $promotionCode):float {
    $db = $GLOBALS['database'];
    $products = $db->query("SELECT * FROM cart WHERE userId = '" . $userId . "';");
    $cost = 0;
    if($promotionCode){
        $result = $db->query("SELECT * FROM promotions WHERE code = '" . $promotionCode . "';");
        if(mysqli_num_rows($result) > 0){$promo = $result->fetch_assoc();}
    }
    while($product = $products->fetch_assoc()) {
        $prod = new ViewableProduct($product['itemId']);
        if($promo['type'] == 'percent' && $promo['items'] == $prod['id']){
            $temp = (float)$prod->price * (float)($promo['percent']/100) * (float)$product['quantity'];
            $cost = (int)$cost + (int)$temp;
            $cost = 111;
        }
        else if($promo['type'] == 'bogo' && $promo['items'] == $prod['id'] && $product['quantity'] > 1 ){
            $cost += (float)$prod->price * (((int)$product['quantity']) - 1);
            $cost = 222;
        }
        else{
            $cost += (float)$prod->price * (int)$product['quantity'];
            $cost = 333;
        }
    }
    return $cost;
}  

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
            $sql = "UPDATE cart SET quantity = quantity + ". $args['itemQuantity']." WHERE userId = '" . $user->id . "' AND itemId = '" . $args['itemId'] . "';";
        }
        else {
            $sql = "INSERT INTO cart (userId, itemId, quantity) VALUES ('" . $user->id . "', '" . $args['itemId'] . "', ". $args['itemQuantity'] .");";
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
     * Updates the quantity of an item in a user's cart
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
        if(!$db->query("UPDATE cart SET quantity = " . $args['quantity'] . " WHERE userId = '" . $user->id . "' AND itemId = '" . $args['itemId'] . "';")) {
            error($db->error);
            return;
        }
        success();
    }
    
    /*
     * Delete's an item from a user's cart
     * @param token - The user's auth token
     * @param itemId - The item id
     */
    static function Delete($args):void {
        //Check if the token was included
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

        $db = $GLOBALS['database'];
        if(!$db->query("DELETE FROM cart WHERE userId = '" . $user->id . "' AND itemId = '" . $args['itemId'] . "';")) {
            error($db->error);
            return;
        }
        success();
    }
    
    /*
     * Delete's all items from a user's cart
     * @param token - The user's auth token
     */
    static function DeleteAll($args):void {
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
        if(!$db->query("DELETE FROM cart WHERE userId = '" . $user->id . "';")) {
            error($db->error);
            return;
        }
        success();
    }

    /*
     * Get's the total amount for the cart.
     * @param token: The user's auth token
     * @param code: An optional promotion code
     * @return the total cost of all items in the user's cart
     */
    static function Total($args):void {
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
        $output = new HTTPResponse();
        $payload->total = getCartPrice($user->id, $args['code']);
        $output->setPayload($payload);
        $output->complete();
    }
}  
?>
