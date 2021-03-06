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
        if($promo['type'] == 'percent' && $promo['itemId'] == $product['itemId']){
            $temp = (float)$prod->price * (float)($promo['percent']/100) * (float)$product['quantity'];
            $cost = (int)$cost + (int)$temp;
        }
        else if($promo['type'] == 'bogo' && $promo['items'] == $product['itemId'] && $product['quantity'] > 1 ){
            $cost += (float)$prod->price * (((int)$product['quantity']) - 1);
            $cost = 222;
        }
        else{
            $cost += (float)$prod->price * (int)$product['quantity'];
        }
    }
    return $cost;
}  

class Cart {
    /*
     * Adds an item to a user's cart
     * @param itemId - the item ID
     * @param itemQuantity - the number of items to add
     * @param token - The user's auth token
     */
    static function Add($args):void {
        //check params
        if(!($args['itemId'] && $args['token'] && $args['itemQuantity'])) {
            error("Missing required fields");
            return;
        }
        //check if user is logged in
        $user = new SiteUser(null, $args['token']);
        if(!$user->isAuth()) { 
            error("User is not authenticated");
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

        //check if product is in stock
        $prod = new ViewableProduct($args['itemId']);
        if($prod->quantity < (int)$args['quantity']) {
            error("Product is out of stock");
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

    /*
     * Returns a list of the user's order history
     * @param token The user's authentication token
     * @return A list of orders for this user, including item ids and
     * a total price.
     */
    static function History($args): void {
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
        $db = $GLOBALS['database'];
        $orders = $db->query("SELECT * from history WHERE userId = '" . $user->id . "';");
        $payload->orders = array();
        while($order = $orders->fetch_assoc()) {
            $res = new stdClass();
            $res->time = $order['time'];
            $res->items = array();
            foreach(str_getcsv($order['itemList']) as $itemId) {
                //add each product to the payload
                try {
                    $prod = new ViewableProduct((int)$itemId);
                }
                catch (Exception $e) {
                    continue;
                }
                $item = new stdClass();
                $item->id = $itemId;
                $item->name = $prod->name;
                $item->image = $prod->image;
                $res->items[] = $item;
            }
            $payload->orders[] = $res;
        }
        $output->setPayload($payload);
        $output->complete();
    }
}  
?>
