<?php

class Cart{
  
    /* 
     *
     * Creates a new cart for a user, cart is assoicated with user by the authentication token
     * @param token: A User authentication token
     */
      static function Create($args): void {
        //checks if the required variables were given
        if(!($args['token'])) {
            error("Missing required fields");
            return;
        }
        //insert into database
        $db = $GLOBALS['database'];
        if(!$db->query("INSERT INTO cart (token) VALUES('". $args['token'] . "');")){
            error($db->error);
            return;
        }
        success();
    }
}
class ViewableCart{


}
?>
