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
    static function Create($args): void {

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
        if(mysqli_num_rows($result) > 0) {

            // We want to check if there's a promotion with the given code, *that is still going on*.
            while($row = mysqli_fetch_row($result)) {

                $currenttime = time();
                // If the end date is *not* past
                if($currenttime < $row['enddate']) {

                    error("Promotion already exists with that code");
                    return;

                }

            }

        }

        success();

    }

    //TODO: ongoing field in database
    static function End($args): void {

        // Make sure they entered a code
        if(!($args['code'])) {
            error("Missing required fields");
            return;
        }

        $db = $GLOBALS['database'];
        $result = $db->query("SELECT enddate FROM promotions WHERE code = '" . $args['code'] . "';");
        if(mysqli_num_rows($result) > 0) {

            // We want to check if there's a promotion with the given code, *that is still going on*.
            while($row = mysqli_fetch_row($result)) {

                $currenttime = time();
                // If the end date is *not* past
                if($currenttime < $row['enddate']) {

                    $db->query("UPDATE promotions SET enddate= '" . $currenttime . "' WHERE code='" . $args[code] . "';");

                }

            }

        } else {

            error("Invalid Code");
            return;

        }

    }

}
