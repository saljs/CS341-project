<?php

    class Category {
        /*
         * Creates a category entry in the database
         * @param name: The category name
         * @param token: A User authentication token
         */
        static function Create($args): void {
            // Checks if the required variables were given
            if (!($args['category'] && $args['token'])) {
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

            // Checks if the category exists
            $db = $GLOBALS['database'];
            $sql = "SELECT category FROM categories WHERE category='" . $args['category'] . "';";
            $result = $db->query($sql);
            if(!$result)
                error("There's an error in your SQL syntax: {$sql}");

            // If there is not an existing category with the name
            if(!mysqli_num_rows($result) > 0) {

                $sql = "INSERT INTO `categories`
                      (`category`) 
                      VALUES 
                      ('" . $args['category'] . "');";

                if(!$db->query($sql)) {
                    error("There's an error in your SQL syntax: {$sql}");
                    return;
                }

            }

            success();

        }

        /*
         * Returns a comma delimited list of all existing categories
         */
        static function GetAll(): void {

            $db = $GLOBALS['database'];
            $sql = "SELECT category FROM categories";

            $result = $db->query($sql);
            if(!$result) {
                error("There's an error in your SQL syntax: {$sql}");
                return;
            }

            $categories = array();

            while($row = mysqli_fetch_assoc($result)) {

                array_push($categories, $row['category']);

            }
            // make into json object?
            $output = new HTTPResponse();

            // Declare our return fields.
            $payload->categories = $categories;
            $output->setPayload($payload);
            $output->complete();

        }

        /*
         * Deletes a category entry in the database
         * @param name: The category name
         * @param token: A User authentication token
         */
        static function Delete($args): void {

            if(!($args['category'] && args['token'])) {
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

            // Checks if the category exists
            $db = $GLOBALS['database'];
            $sql = "SELECT category FROM categories WHERE category='" . $args['category'] . "';";

            $result = $db->query($sql);
            if(!$result) {
                error("There's an error in your SQL syntax: {$sql}");
                return;
            }

            // If there is a category with that name
            if(mysqli_num_rows($result) > 0) {

                $sql = "DELETE FROM `categories` WHERE category='" . $args['category'] . "';";
                if(!$db->query($sql)) {
                    error("There's an error in your SQL syntax: {$sql}");
                    return;
                }

            } else {

                success("There is no category with that name");
                return;

            }

            success();

        }

    }

?>
