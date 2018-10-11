<?php


class Promotion {

    /*
     * Creates a new promotion
     * @param name: The display name
     * @param code: the discount code
     * @param type: Either bogo_category or percent_category, where category is the item category
     * that the promotion works on.
     * @param percent: The discount percentage
     * @param enddate: The epoch time-based end date of the promotion. https://www.epochconverter.com
     */

    // HTTP Get Request
    static function Create($args): void {

        // Checks if the required variables are given
        if (!($args['name'] && $args['code'] && $args['type'] && $args['percent'] && $args['enddate'])) {
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

        // Checks if the enddate is valid (not past current time).
        $currenttime = time();
        if($currenttime >= $args['enddate']) {
            error("Invalid End Date");
            return;
        }

        // Checks if the promotion already exists & is running.
        $db = $GLOBALS['database'];
        $result = $db->query("SELECT enddate FROM promotions WHERE code = '" . $args['code'] . "';");

        // If there is a existing promotion with that code in the database
        if(mysqli_num_rows($result) > 0) {

            $row = mysqli_fetch_row($result);

            $currenttime = time();
            // If the promotion is not over
            if($currenttime < $row['enddate']) {

                error("Promotion already exists with that code");
                return;

            }

            // Otherwise, update that discount with the new information.
            $sql = "UPDATE promotions 
                      SET name='" . $args['name'] .
                    "', type='" . $args['type'] .
                    "', percent='" . $args['percent'] .
                    "', enddate='" . $args['enddate'] .
                    "' WHERE code='" . $args['code'] . "';";

            if(!$db->query($sql))
                error("There's an error in your SQL syntax: {$sql}");

        } else {

            $sql = "INSERT INTO `promotions`
                      (`name`, `code`, `type`, `percent`, `enddate`) 
                      VALUES 
                      ('". $args['name'] . "', 
                      '" . $args['code'] . "', 
                      '" . $args['type'] . "', 
                      '" . $args['percent'] . "',
                      '" . $args['enddate'] . "');";

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

        // Checks if the promotion already exists & is running.
        $db = $GLOBALS['database'];
        $result = $db->query("SELECT `name`, `type`, `percent`, `enddate` FROM promotions WHERE code = '" . $args['code'] . "';");

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

                    if($args[$val])
                        $new[$val] = $args[$val];
                    else
                        $new[$val] = $row[$val];

                }

                foreach($new as $key => $val) {

                    echo $val . '\n';

                }

            }

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

        $sql = "SELECT enddate FROM promotions WHERE code = '" . $args['code'] . "';";
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
?>
