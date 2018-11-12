<?php


class Promotion {

    /*
     * Creates a new promotion
     * @param name: The display name
     * @param code: the discount code
     * @param type: Either bogo or percent
     * @param percent: The discount percentage
     * @param startDate: The epoch time-based end date of the promotion. https://www.epochconverter.com
     * @param endDate: The epoch time-based end date of the promotion. https://www.epochconverter.com
     * @param items: Comma delimited list of item ID's that the promotion works for.
     * @param categories: Comma delimited list of category names that the promotion works for.
     */

    // HTTP Get Request
    static function Create($args): void {

        // Checks if the required variables are given
        if (!($args['name'] && $args['code'] && $args['typeRadio'] && $args['percent'] && $args['endDate'] && $args['startDate'] && $args['items'] && $args['categories'])) {
            error("Missing required fields");
            return;
        }

        $args['type'] = $args['typeRadio'];
        $args['startDate'] = strtotime($args['startDate']);
        $args['endDate'] = strtotime($args['endDate']);

//        if(!$args['token']) {
//            error("Operation requires admin privileges");
//            return;
//        }
//        try {
//            $admin = new SiteUser(null, $args['token']);
//            if(!$admin->isAuth()) {
//                error("Invalid token");
//                return;
//            }
//        }
//        catch(Exception $e) {
//            error($e->getMessage());
//            return;
//        }

        // Checks if the endDate is valid (not past current time).
        $currenttime = time();
        if($currenttime >= $args['endDate']) {
            error("Invalid End Date");
            return;
        }

        // Checks if the promotion already exists & is running.
        $db = $GLOBALS['database'];
        $sql = "SELECT enddate FROM promotions WHERE code = '" . $args['code'] . "';";
        $result = $db->query($sql);
        if(!$result)
            error("There's an error in your SQL syntax: {$sql}");

        // If there is a existing promotion with that code in the database
        if(mysqli_num_rows($result) > 0) {

            $row = mysqli_fetch_row($result);

            $currenttime = time();
            // If the promotion is not over
            if($currenttime < $row['endDate']) {

                error("Promotion already exists with that code");
                return;

            }

            // Otherwise, update that discount with the new information.
            $sql = "UPDATE promotions 
                      SET name='" . $args['name'] .
                    "', type='" . $args['type'] .
                    "', percent='" . $args['percent'] .
                    "', enddate='" . $args['endDate'] .
                    "', startdate='" . $args['startDate'] .
                    "', items='" . $args['items'] .
                    "', categories='" . $args['categories'] .
                    "' WHERE code='" . $args['code'] . "';";

            if(!$db->query($sql))
                error("There's an error in your SQL syntax: {$sql}");

        } else {

            $sql = "INSERT INTO `promotions`
                      (`name`, `code`, `type`, `percent`, `items`, `categories`, `startdate`, `enddate`) 
                      VALUES 
                      ('". $args['name'] . "', 
                      '" . $args['code'] . "', 
                      '" . $args['type'] . "', 
                      '" . $args['percent'] . "',
                      '" . $args['items'] . "',
                      '" . $args['categories'] . "',
                      '" . $args['startDate'] . "',
                      '" . $args['endDate'] . "');";

            // Create the new promotion
            if(!$db->query($sql))
                error("There's an error in your SQL syntax: {$sql}");

        }

        success();

    }

    static function Edit($args): void {

        // Make sure they passed all fields
        if(!($args['code'])) {
            error("Missing required fields");
            return;
        }

        $args['startDate'] = strtotime($args['startDate']);
        $args['endDate'] = strtotime($args['endDate']);

        // Checks if the promotion already exists & is running.
        $db = $GLOBALS['database'];
        $sql = "SELECT `name`, `type`, `percent`, `startdate`, `enddate`, `items`, `categories` FROM promotions WHERE code = '{$args["code"]}';";
        $result = $db->query($sql);
        if(!$result)
            error("There's an error in your SQL syntax: {$sql}");

        if(mysqli_num_rows($result) > 0) {

            // We want to check if there's a promotion with the given code
            $row = mysqli_fetch_assoc($result);
            $currenttime = time();

            // If the promotion is not over
            if ($currenttime < $row['enddate']) {

                // Go through each field in the row, if that field was given in the
                // edit arguements, then insert it into our new array, otherwise take
                // that data from the database and insert it into our new array.
                $new['code'] = $args['code'];
                foreach($row as $key => $val) {

                    if($args[$key])
                        $new[$key] = $args[$key];
                    else
                        $new[$key] = $row[$key];

                }

                // Use our existing create function to update the item
                self::Create($new);

            } else {

                error("This promotion is over");

            }

        }

    }

    static function GetAll(): void {

        $db = $GLOBALS['database'];
        $sql = "SELECT * FROM promotions";

        $result = $db->query($sql);
        if(!$result) {
            error("There's an error in your SQL syntax: {$sql}");
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

    static function End($args): void {

        // Make sure they entered a code
        if(!($args['code'])) {
            error("Missing required fields");
            return;
        }

//        if(!$args['token']) {
//            error("Operation requires admin privileges");
//            return;
//        }
//        try {
//            $admin = new SiteUser(null, $args['token']);
//            if(!$admin->isAuth()) {
//                error("Invalid token");
//                return;
//            }
//        }
//        catch(Exception $e) {
//            error($e->getMessage());
//            return;
//        }

        $db = $GLOBALS['database'];

        $sql = "SELECT endDate FROM promotions WHERE code = '" . $args['code'] . "';";
        $result = $db->query($sql);
        if(!$result)
            error("There's an error in your SQL syntax: {$sql}");

        if(mysqli_num_rows($result) > 0) {

            // We want to check if there's a promotion with the given code
            while($row = mysqli_fetch_assoc($result)) {

                $currenttime = time();
                // If the promotion is not over
                if($currenttime < $row['enddate']) {

                    // Change the end date to the current time.
                    $sql = "UPDATE promotions SET enddate= '" . $currenttime . "' WHERE code='" . $args[code] . "';";

                    if(!$db->query($sql))
                        error("There's an error in your SQL syntax: {$sql}");

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
        if(!$result)
            error("There's an error in your SQL syntax: {$sql}");

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
