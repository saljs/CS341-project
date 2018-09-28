<?php

/*
 * Endpoint that implements user flow
 */
class User {

    /* 
     * Creates a new user
     * @param email: The new user's email address
     * @param 
     */
    static function Create($args): void {
        //checks if the required variables were given
        if(!($args['email'] && $args['password'] && $args['type'])) {
            error("Missing required fields");
        }
        
        //checks if user already exists
        $db = $GLOBALS['database'];
        $result = $db->query("SELECT id from users WHERE email = '" . $args['email'] . "';");
        if(mysqli_num_rows($result) > 0) {
            error("User already exists");
        }

        //checks if admin privs are needed
        if($args['type'] == "admin") {
            if(!$args['auth']) {
                error("Operation requires admin privileges");
            }
            //TODO: verify the key!
        }
        
        //inserts the user into the database
        if(!$db->query("INSERT INTO users (email, password, type) VALUES('" 
             . $args['email'] . "', "
             . password_hash($args['password'], PASSWORD_BCRYPT) . "', "
             . $args['type'] . "');")) {
             error($db->error);
        }

        //imports the cart from a guest user
        if($args['guestID']) {
            //TODO: import guest cart
        }
        
        success();
    }

}

class SiteUser {

    public $email;
    public $password;
    public $type;
    public $name;
    
    function __construct($id) {
        $this->email = $id;
    }

    function load(): int {
        $db = $GLOBALS['database'];
    }
        
?>
