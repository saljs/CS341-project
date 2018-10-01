<?php
/*
 * Endpoint that implements user flow
 */

class User {

    /* 
     * Creates a new user
     * @param email: The new user's email address
     * @param password: The user's desired password
     * @param type: Either 'user' or 'admin'
     * @param name: The user's name
     */
    static function Create($args): void {
        //checks if the required variables were given
        if(!($args['email'] && $args['password'] && $args['type'] && $args['name'])) {
            error("Missing required fields");
            return;
        }

        //checks if user already exists
        $db = $GLOBALS['database'];
        $result = $db->query("SELECT id from users WHERE email = '" . $args['email'] . "';");
        if(mysqli_num_rows($result) > 0) {
            error("User already exists");
            return;
        }

        //checks if admin privs are needed
        if($args['type'] == "admin") {
            if(!$args['auth']) {
                error("Operation requires admin privileges");
                return;
            }
            $admin = new SiteUser($args['email']);
            if(!$admin->checkAuth($args['auth'])) {
                error("Invalid token");
                return;
            }
        }
        
        //inserts the user into the database
        if(!$db->query("INSERT INTO users (email, password, type, name) VALUES('" 
             . $args['email'] . "', '"
             . password_hash($args['password'], PASSWORD_BCRYPT) . "', '"
             . $args['type'] . "', '"
             . $args['name'] . "');")) {
             error($db->error);
             return;
        }

        //imports the cart from a guest user
        if($args['guestID']) {
            //TODO: import guest cart
        }
        
        success();
    }

}

/*
 * Site user object
 */
class SiteUser {

    public $id;
    public $email;
    private $hash;
    public $type;
    public $name;
    
    /*
     * Constructor
     * @param email: The user's email address
     */
    function __construct($email) {
        $this->email = $email;
        $db = $GLOBALS['database'];
        $result = $db->query("SELECT * from users WHERE email = '" . $this->email . "';");
        $row = mysqli_fetch_assoc($result);
        $this->hash = $row['password'];
        $this->type = $row['type'];
        $this->name = $row['name'];
        $this->id =  $row['id'];
    }

    /*
     * Authenticates the user
     * @param password: The user's plaintext password
     * @return An authentication token that is valid for 6 hours
     */
    function auth($password): string {
        if(!password_verify($password, $this->hash)) {
            throw new Exception("Invalid credentials");
        }
        $db = $GLOBALS['database'];
        $result = $db->query("SELECT * from logins WHERE user = '" . $this->id . "';");
        if(mysqli_num_rows($result) > 0) {
            //Previous login for this user
            $row = mysqli_fetch_assoc($result);
            if(time() - strtotime($row['created']) < 21600) { //6 hours until token expires
                return $row['token'];
            }
            else {
                if(!$db->query("DELETE FROM logins WHERE user = '" . $this->id . "';")) {
                    throw new Exception($db->error);
                }
            }
        }
        //generate a new token
        $token = bin2hex(random_bytes(128));
        if(!$db->query("INSERT INTO logins (user, token) VALUES('" . $this->id . "', '" . $token . "');")) {
            throw new Exception($db->error);
        }
        return $token;
    }

    /*
     * Checks if the user is authenticated
     * @param token: An authentication token
     * @return True if authenticated, false otherwise
     */
    function checkAuth($token): bool {
        $result = $db->query("SELECT * from logins WHERE user = '" . $this->id . "';");
        if(mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            if(time() - strtotime($row['created']) < 21600 && $row['token'] == $token) { //6 hours until token expires
                return true;
            }
        }
        return false;
    }
}
        
?>
