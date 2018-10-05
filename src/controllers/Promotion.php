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

        //TODO: Admin Check, User.php

        // Checks if the required variables are given
        if (!($args['name'] && $args['code'] && $args['type'] && $args['percent'] && $args['enddate'])) {
            error("Missing required fields");
            return;
        }

        // Checks if the enddate is valid (not past current time).
        $currenttime = time();
        if($currenttime >= $args['enddate']) {
            error("Invalid End Date");
            return;
        }

        // Checks if the promotion already exists & is running.
        $db = $GLOBALS['database'];
        $result = $db->query("SELECT enddate FROM promotions WHERE code = '" . $args['code'] . "';");
        echo "selecting\n";

        // If there is a existing promotion with that code in the database
        if(mysqli_num_rows($result) > 0) {

            $row = mysqli_fetch_row($result);

            $currenttime = time();
            // If the promotion is not over
            if($currenttime < $row['enddate']) {

                error("Promotion already exists with that code");
                return;

            }

            echo "Updating\n";

            // Otherwise, update that discount with the new information.
            $sql = "UPDATE promotions 
                      SET name='" . $args['name'] .
                    "', type='" . $args['type'] .
                    "', percent='" . $args['percent'] .
                    "', enddate='" . $args['enddate'] .
                    "' WHERE code='" . $args['code'] . "';";

            if(!$db->query($sql))
                echo($sql);

        } else {

            echo "Inserting\n";

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
                echo($sql);

        }

        success();

    }

    static function End($args): void {

        // Make sure they entered a code
        if(!($args['code'])) {
            error("Missing required fields");
            return;
        }

        $db = $GLOBALS['database'];

        $sql = "SELECT enddate FROM promotions WHERE code = '" . $args['code'] . "';";
        $result = $db->query($sql);
       // if(!$result)
            echo($sql);

        if(mysqli_num_rows($result) > 0) {

            // We want to check if there's a promotion with the given code
            while($row = mysqli_fetch_row($result)) {

                $currenttime = time();
                // If the promotion is not over
                if($currenttime < $row['enddate']) {

                    // Change the end date to the current time.
                    $sql = "UPDATE promotions SET enddate= '" . $currenttime . "' WHERE code='" . $args[code] . "';";

                    if(!$db->query($sql))
                        echo($sql);

                    echo $sql;

                    success();

                }

            }

        } else {

            error("Invalid Code");
            return;

        }

    }

}
?>
