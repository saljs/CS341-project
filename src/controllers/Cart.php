<?php
/*
 * Cart.php
 * Defines an interface for interacting with an 
 * autheticated user's cart
 */

require_once "User.php";

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

}  
?>
