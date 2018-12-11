<?php
require_once __DIR__ . '/User.php';

class Promotion {

    /*
     * Creates a new promotion
     * @param name: The display name
     * @param code: the discount code
     * @param type: Either bogo or percent
     * @param percent: The discount percentage
     * @param startDate: The epoch time-based start date of the promotion. https://www.epochconverter.com
     * @param endDate: The epoch time-based end date of the promotion. https://www.epochconverter.com
     * @param items: Comma delimited list of item ID's that the promotion works for.
     * @param categories: Comma delimited list of category names that the promotion works for.
     * @param token: An Admin Auth token
     */
    static function Create($args): void {

        // Checks if the required variables are given
        if (!($args['name'] && $args['code'] && $args['type'] && $args['percent'] && $args['endDate'] && $args['startDate'] && $args['items'] && $args['categories'] && $args['token'])) {
            error("Missing required fields");
            return;
        }

        //check if user has access
        try {
            $user = new SiteUser(null, $args['token']);
            if(!$user->isAuth() || $user->type != "admin") {
                error("User doesn't have privileges to add items");
                return;
            }
        }
        catch(Exception $e) {
            error($e->getMessage());
            return;
        }

        // Checks if the endDate is valid (not past current time).
        $currenttime = time();
        if($currenttime >= strtotime($args['endDate'])) {
            error("Invalid End Date");
            return;
        }

        $db = $GLOBALS['database'];
        // Create the new promotion
        if(!$db->query("INSERT INTO promotions (name, code, type, percent, items, categories, startdate, enddate) VALUES ("
          . "'" . $args['name'] . "', "
          . "'" . $args['code'] . "', " 
          . "'" . $args['type'] . "', " 
          . "'" . $args['percent'] . "', "
          . "'" . $args['items'] . "', "
          . "'" . $args['categories'] . "', "
          . "'" . strtotime($args['startDate']) . "', "
          . "'" . strtotime($args['endDate']) . "');")) {
            error($db->error);
            return;
        }
        success();
    }

    /*
     * Edits an existing promotion, only changing fields given, all based on the promotion code.
     * @param name: The display name
     * @param code: the discount code
     * @param type: Either bogo or percent
     * @param percent: The discount percentage
     * @param startDate: The epoch time-based start date of the promotion. https://www.epochconverter.com
     * @param endDate: The epoch time-based end date of the promotion. https://www.epochconverter.com
     * @param items: Comma delimited list of item ID's that the promotion works for.
     * @param categories: Comma delimited list of category names that the promotion works for.
     * @param token: An Admin Auth token
     */
    static function Edit($args): void {

        // Make sure they passed all fields
        if (!($args['name'] && $args['code'] && $args['type'] && $args['percent'] && $args['endDate'] && $args['startDate'] && $args['items'] && $args['categories'] && $args['token'])) {
            error("Missing required fields");
            return;
        }

        //check if user has access
        try {
            $user = new SiteUser(null, $args['token']);
            if(!$user->isAuth() || $user->type != "admin") {
                error("User doesn't have privileges to add items");
                return;
            }
        }
        catch(Exception $e) {
            error($e->getMessage());
            return;
        }

        // Checks if the endDate is valid (not past current time).
        $currenttime = time();
        if($currenttime >= strtotime($args['endDate'])) {
            error("Invalid End Date");
            return;
        }

        $db = $GLOBALS['database'];
        // Update the promotion
        if(!$db->query("UPDATE promotions SET "
          . "name = '" . $args['name'] . "', "
          . "code = '" . $args['code'] . "', " 
          . "type = '" . $args['type'] . "', " 
          . "percent = '" . $args['percent'] . "', "
          . "items = '" . $args['items'] . "', "
          . "categories = '" . $args['categories'] . "', "
          . "startdate = '" . strtotime($args['startDate']) . "', "
          . "enddate = '" . strtotime($args['endDate']) . "';")) {
            error($db->error);
            return;
        }
        success();
    }

    /*
     * Get all existing promotions and their data.
     * @return: Returns a json string of all promotions in the database.
     */
    static function GetAll(): void {

        $db = $GLOBALS['database'];
        $sql = "SELECT * FROM promotions";

        $result = $db->query($sql);
        if(!$result) {
            error($db->error);
            return;
        }

        $promotions = array();
        while($row = mysqli_fetch_assoc($result)) {
            $promo = array();
            foreach($result as $key => $value) {
                $promo[$key] = $value;
            }
            array_push($promotions, $promo);
        }

        $output = new HTTPResponse();
        // Declare our return fields.
        $payload->promotions = $promotions[0];
        $output->setPayload($payload);
        $output->complete();
    }

    /*
     * Gets data for one promotion.
     * @param code: The promotion to look for.
     * @return: Returns a json string of all promotions in the database.
     */
    static function Get($args): void {

        if (!$args['code']) {
            error("Discount code required");
            return;
        } try {
            // Get our promotion based on the code given.
            $promo = new ViewableDiscount($args['code']);
            $output = new HTTPResponse();

            // Declare our return fields.
            $payload->code = $promo->code;
            $payload->name = $promo->name;
            $payload->type = $promo->type;
            $payload->percent = $promo->percent;
            $payload->startDate = $promo->startDate;
            $payload->endDate = $promo->endDate;
            $payload->items = $promo->items;
            $payload->categories = $promo->categories;

            // Send the output.
            $output->setPayload($payload);
            $output->complete();

        } catch (Exception $e) {
            error($e->getMessage());
        }
    }

    /*
     * Forcibly ends a promotion.
     * @param code: The promotion to look for.
     * @param token: An Admin Auth token
     */
    static function End($args): void {
        // Make sure they entered a code
        if(!($args['code']) && $args['token']) {
            error("Missing required fields");
            return;
        }
        //check if user has access
        try {
            $user = new SiteUser(null, $args['token']);
            if(!$user->isAuth() || $user->type != "admin") {
                error("User doesn't have privileges to add items");
                return;
            }
        }
        catch(Exception $e) {
            error($e->getMessage());
            return;
        }

        $db = $GLOBALS['database'];
        $sql = "SELECT enddate FROM promotions WHERE code = '" . $args['code'] . "';";
        $result = $db->query($sql);
        if(!$result) {
            error($db->error);
            return;
        }

        if(mysqli_num_rows($result) > 0) {
            // We want to check if there's a promotion with the given code
            while($row = mysqli_fetch_assoc($result)) {
                $currenttime = time();
                // If the promotion is not over
                if($currenttime < $row['enddate']) {
                    // Change the end date to the current time.
                    $sql = "UPDATE promotions SET enddate= '" . $currenttime . "' WHERE code='" . $args[code] . "';";
                    if(!$db->query($sql)) {
                        error($db->error);
                        return;
                    }
                }
            }
        } else {
            error("Invalid Code");
            return;
        }
        success();
    }
}

class ViewableDiscount {

    public $code;
    public $name;
    public $type;
    public $percent;
    public $startDate;
    public $endDate;
    public $items;
    public $categories;

    function __construct($code){

        $db = $GLOBALS['database'];
        $sql = "SELECT * FROM promotions WHERE code = '{$code}';";
        $result = $db->query($sql);
        if(!$result) {
            throw new Exception($db->error);
        }
        if(mysqli_num_rows($result) < 1) {
            throw new Exception("Promotion Code does not exist");
        } else {


            $row = mysqli_fetch_assoc($result);

            $this->code = $code;
            $this->name = $row['name'];
            $this->type = $row['type'];
            $this->percent = $row['percent'];
            $this->startDate = $row['startdate'];
            $this->endDate = $row['enddate'];
            $this->items = $row['items'];
            $this->categories = $row['categories'];

        }

    }

}
?>
