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
            if(!$args['token']) {
                error("Operation requires admin privileges");
                return;
            }
            $admin = new SiteUser($token=$args['token']);
            if(!$admin->isAuth()) {
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

    /*
     * Authenticates the user for 6 hours
     * @param email The user's email
     * @param The user's password
     * @return A 64-digit hexadecimal authentication token
     */
    static function Authenticate($args): void {
        try {
            $user = new SiteUser($email=$args['email']);
            $token = $user->auth($args['password']);
            $output = new HTTPResponse();
            $payload->token = $token;
            $output->setPayload($payload);
            $output->complete();
        }
        catch (Exception $e) {
            error($e->getMessage());
        }
    }

}

/*
 * Site user object
 */
class SiteUser {

    //user information
    public $id;
    public $email;
    private $hash;
    public $type;
    public $name;

    //login information
    private $token;
    private $tokenCreated;
    
    /*
     * Constructor
     * @param email: The user's email address
     * @param token: An authentication token
     */
    function __construct($email=null, $token=null) {
        $db = $GLOBALS['database'];
        $sql = "";
        if($token != null) {
            $result = $db->query("SELECT * logins WHERE token = '" . $token . "';");
            if(mysqli_num_rows($result) > 0) {
                $row = mysqli_fetch_assoc($result);
                $this->token = $token;
                $this->tokenCreated = strtotime($row['created']);
                $sql = "SELECT * FROM users WHERE id = '" . $row['user'] . "';";
            }
        }
        else if($email != null) {
            $this->email = $email;
            $sql = "SELECT * FROM users WHERE email = '" . $this->email . "';";
        }
        
        if(!empty($sql)) {
            $result = $db->query($sql);
            if(mysqli_num_rows($result) > 0) {
                $row = mysqli_fetch_assoc($result);
                $this->hash = $row['password'];
                $this->type = $row['type'];
                $this->name = $row['name'];
                $this->id =  $row['id'];
            }
            else {
                throw new Exception($sql);
            }
        }
        else {
            throw new Exception("Need either email or token");
        }
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
                $this->token = $row['token'];
                $this->tokenCreated = strtotime($row['created']);
                return $this->token;
            }
            else {
                if(!$db->query("DELETE FROM logins WHERE user = '" . $this->id . "';")) {
                    throw new Exception($db->error);
                }
            }
        }
        //generate a new token
        $this->token = bin2hex(random_bytes(128));
        if(!$db->query("INSERT INTO logins (user, token) VALUES('" . $this->id . "', '" . $this->token . "');")) {
            throw new Exception($db->error);
        }
        $this->tokenCreated = time();
        return $this->token;
    }

    /*
     * Checks if the user is authenticated
     * @return True if authenticated, false otherwise
     */
    function isAuth(): bool {
        if(time() - $this->tokenCreated < 21600) {
            return true;
        }
        return false;
    }
}
        
?>
